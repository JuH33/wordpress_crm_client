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

function getFormResponsesBy($lang) {
  $lang = strtolower($lang);
  $path = __DIR__ . "/../translations/{$lang}.json";

  if (!file_exists($path)) {
    throw MapotempoServerError("translations has not been found for [{$lang}] at path: {$path}");
  }

  $fileContent = file_get_contents($path);

  return json_decode($fileContent, true)['mapotempo']['formResponses'];
}
