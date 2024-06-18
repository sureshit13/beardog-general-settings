<?php 
if (!defined('WPINC')) {
    die;
}
// Cut copy paste Enable function
function custom_wpcf7_script() {?>
  <script>
      jQuery(document).ready(function($) {
          $('.wpcf7-form-control').on("cut copy paste", function(e) {
              e.preventDefault();
          });
      });
  </script>
  <?php
}
add_action('wp_footer', 'custom_wpcf7_script');