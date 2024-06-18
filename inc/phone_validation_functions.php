<?php
if (!defined('WPINC')) {
    die;
}
// Phone Validation
function custom_phone_validation($result, $tag) {
	$type = $tag->type;
	$name = $tag->name;

	if ($type == 'tel' || $type == 'tel*') {
			$phoneNumber = isset($_POST[$name]) ? trim($_POST[$name]) : '';
			// Remove common phone number characters
			$phoneNumber = preg_replace('/[() .+-]/', '', $phoneNumber);

			// Check if the phone number is not exactly 10 digits or contains any non-digit characters
			if (!ctype_digit($phoneNumber) || strlen($phoneNumber) != 10) {
					$result->invalidate($tag, 'Please enter a valid phone number.');
			}
	}

	return $result;
}

add_filter('wpcf7_validate_tel', 'custom_phone_validation', 10, 2);
add_filter('wpcf7_validate_tel*', 'custom_phone_validation', 10, 2);

// Enqueue the custom JavaScript
function custom_phone_input_restriction() {
?>
	<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
					var phoneFields = document.querySelectorAll('input[type="tel"]');
					
					phoneFields.forEach(function(field) {
							field.addEventListener('input', function(e) {
									// Remove any non-digit character
									this.value = this.value.replace(/\D/g, '');
									
									// Limit the input to 10 digits
									if (this.value.length > 10) {
											this.value = this.value.slice(0, 10);
									}
							});
					});
			});
	</script>
<?php
}
add_action('wp_footer', 'custom_phone_input_restriction');