<?php

trait wpcf7Traits {
  function wpcf7SuccessMessage($message, $status) {
    $message = $this->_trads['success_sent'] . '. ';
    $message .= $this->_trads['confirm_sent'] . $this->dataFormatted['email'] . '. ';

    return $message;
  }

  function wpcf7CountryNotHandled($message, $status) {
    $message = $this->wpcf7SuccessMessage('', $status);
    $message .= $this->_trads['map_data'];

    return $message;
  }

  function wpcf7MailErrorMessage($message, $status) {
    $b = '<br>';
    $message = $this->_trads['success_sent'] . ', ';
    $message .= str_replace('{email}', $this->dataFormatted['email'], $this->_trads['email_ataken']);

    return $message;
  }

  function wpcf7VehicleLimitIsOverpassed($message, $status) {
    if (empty($message))
      $message = $this->wpcf7SuccessMessage('', $status);

    $message .= str_replace('{number}', $this->dataFormatted['fleetsize'], $this->_trads['vehicle_limit_overpassed']);

    return $message;
  }
}

// Helpers

function getArrayForLang($lang) {
  $traductions = array(
    'success_sent' => '',
    'confirm_sent' => '',
    'fail_no_sent' => '',
    'email_ataken' => '',
    'map_data' => '',
    'vehicle_limit_overpassed' => ''
  );

  switch($lang) {
    case('fr'):
      $traductions['success_sent'] = 'Merci, votre demande a bien été envoyée ';
      $traductions['confirm_sent'] = 'Un compte de test a été créé, pour l’activer veuillez définir votre mot de passe depuis l’email envoyé à ';
      $traductions['fail_no_sent'] = 'Merci, votre demande a bien été envoyée. ';
      $traductions['email_ataken'] = 'néanmoins votre compte de test Mapotempo n\'a pas été créé car l\'adresse email {email} est déjà utilisée. ';
      $traductions['vehicle_limit_overpassed'] = 'Vous avez déclaré {number} véhicules, néanmoins votre compte gratuit en comportera 2. Merci pour votre compréhension. ';
      $traductions['map_data'] = "Nous vous informons que pour l’instant nous ne disposons pas des données cartographiques pour l’ensemble de votre pays. Il est possible que nous gérions prochainement votre zone géographique. Pour en savoir plus contactez-nous : +33 5 64 27 04 59. ";
      break;
    default:
      $traductions['success_sent'] = 'Thank you, your request has been sent ';
      $traductions['confirm_sent'] = 'A test account has been created, to activate it please set your password from the email sent to ';
      $traductions['fail_no_sent'] = 'Your Mapotempo account has not been created. ';
      $traductions['email_ataken'] = 'however your Mapotempo test account has not been created because the email address {email} is already used. ';
      $traductions['vehicle_limit_overpassed'] = 'You have declared {number} vehicles, nevertheless your free account will include 2. Thank you for your understanding. ';
      $traductions['map_data'] = "We inform you that we do not yet have map data for your entire country. However, your geographic zone could be handled soon. For more information contact us: +33 5 64 27 04 59.";
      break;
  }

  return $traductions;
}
