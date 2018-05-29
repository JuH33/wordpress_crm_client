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
  $data = array(
    'localization' => 'fr',
    'NavigationGPS' => 'tomtom',
    'company' => 'MyCompanyTest',
    'email' => 'julien-test@email.com',
    'country' => 'France'
  );

  $manage = new AccountManager($data, array('Mapotempo'));
  $manage->initFactory();
  $manage->startConnections();
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
        $manager = new AccountManager($wpcf_submission->get_posted_data(), array('Mapotempo', 'Ines'));
      } catch (InstanceException $e) {
        print($e);
      } finally {
        $manager->initFactory();
        $manager->startConnections();
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
