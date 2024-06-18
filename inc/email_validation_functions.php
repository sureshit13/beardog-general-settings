<?php 
if (!defined('WPINC')) {
    die;
}
function webslaw_validate_email( $result, $tag ) {
  $type = $tag['type'];
  $email = $tag['name'];
  $value = $_POST[$email] ;

  if ( strpos( $email , 'email' ) !== false ){
      $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,7})$/';
      $Valid = preg_match($regex,  $value, $matches );
      if ( $Valid > 0 ) {
      } else {
          $result->invalidate( $tag, wpcf7_get_message( 'invalid_email' ) );
      }
  }
  return $result;
}
add_filter( 'wpcf7_validate_email*','webslaw_validate_email', 20, 2 );
add_filter( 'wpcf7_validate_email','webslaw_validate_email', 20, 2 );