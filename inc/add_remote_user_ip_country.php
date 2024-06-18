<?php
if (!defined('WPINC')) {
    die;
}
// Hook into the 'wpcf7_before_send_mail' action
add_action('wpcf7_before_send_mail', 'cf7_add_ip_country_to_email');

function cf7_add_ip_country_to_email($contact_form) {
    // Get the submission instance
    $submission = WPCF7_Submission::get_instance();

    if ($submission) {
        // Get the remote IP address
        $ip_address = $submission->get_meta('remote_ip');

        // Use an external service to get the country by IP address
        $response = wp_remote_get('http://ip-api.com/json/' . $ip_address);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if ($data && $data->status === 'success') {
            $country = $data->country;
        } else {
            $country = 'Unknown';
        }

        // Get the current mail properties
        $mail = $contact_form->prop('mail');

        // Define the new content to be added
        $additional_info = "
            <tr><th valign='top'>IP Address:</th><td valign='top'>{$ip_address}</td></tr>
            <tr><th valign='top'>Country:</th><td valign='top'>{$country}</td></tr>";

        // Find the placeholder for user detail and add the IP and country information
        $mail['body'] = str_replace(
            '<tr><th valign="top">User detail :</th><td valign="top"></td></tr>',
            '<tr><th valign="top">User detail :</th><td valign="top"></td></tr>' . $additional_info,
            $mail['body']
        );

        // Update the mail properties
        $contact_form->set_properties(array('mail' => $mail));
    }
}
