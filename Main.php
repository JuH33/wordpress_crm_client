<?php

/*
  Base file example
*/

require_once(__DIR__ . "/AccountManager.php");

/*
*--------------------------------------------------------------------------------------------------------------------
*====================================================================================================================
*
*                                      DEBUGING AND TESTS SUITS /!\ /!\ /!\ /!\ /!\
*
*====================================================================================================================
*--------------------------------------------------------------------------------------------------------------------
*/

if (constant("DEBUG")) {

  // IF NOT IN WORDPRESS FAKE IT
  if (!function_exists('get_post_meta')) {
    function get_post_meta($id, $tag, $single) {
      return ['a:2:{s:11:"STATUS_CODE";i:204;s:16:"HUBSPOT_RESPONSE";s:0:"";}'];
    }
  }

  if (!function_exists('add_filter')) {
    function add_filter($str, $array, $n, $y) {
      print_r('DEBUG::add_filter' . '=>' . $str . PHP_EOL);
    }
  }

  $data = array(
    'localization' => 'fr',
    'NavigationGPS' => 'tomtom',
    'company' => '-MyCompan - 5 9yTestByJuH-',
    'email' => 'julien@example.com',
    'country' => 'France'
  );

  $postId = 42;

  try {
    $manage = new AccountManager($data, array('Mapotempo'), $postId);
    $manage->initFactory();
    $manage->startConnections();
  } catch (Exception $e) {
    print($e);
  }

  var_dump(['test', 'deux']);
}

/*
*--------------------------------------------------------------------------------------------------------------------
*====================================================================================================================
*
                                       WORDPRESS IMPLEMENTATION
*
*====================================================================================================================
*--------------------------------------------------------------------------------------------------------------------
*/
if (function_exists("add_action")) {
  add_action( 'wpcf7_before_send_mail', 'wpcf7_inject_api_teamleader' );
  add_action('wp_enqueue_scripts', 'wpcf7_prevent_multi_requests_script');
}

function wpcf7_inject_api_teamleader($cf7) {
    $wpcf = WPCF7_ContactForm::get_current();
    $wpcf_submission = WPCF7_Submission::get_instance();
    $form_to_proceed = [4]; // Forms allowed

    if (in_array($cf7->id(), $form_to_proceed)) {
      $cf7->skip_mail = SKIP_MAIL;
      $manager = null;

      try {
        $manager = new AccountManager($wpcf_submission->get_posted_data(), array('Mapotempo', 'Ines', 'Hubspot'));
        $manager->initFactory();
        $manager->startConnections();
      } catch (Exception $e) {
        print($e);
      }
    }

    return $wpcf;
}

function wpcf7_prevent_multi_requests_script() {
  if ( function_exists( 'wpcf7_enqueue_scripts') ) {
          wp_enqueue_script('special_trick', get_stylesheet_directory_uri() . '/cross-api-dispatcher/js/wpf7_events.js',
                            array('jquery', 'contact-form-7'), '1.0', true);
  }
}
