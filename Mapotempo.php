<?php

class MapotempoServerError extends Exception { };

class Mapotempo extends BaseClient implements IModelCRM {

  use traitsConnection {
    setPutRequest as private;
    setPostRequest as private;
    setGetRequest as private;
  }

  // Make them public for add_filter usage
  use wpcf7Traits {
    wpcf7SuccessMessage as public;
    wpcf7MailErrorMessage as public;
    wpcf7CountryNotHandled as public;
    wpcf7VehicleLimitIsOverpassed as public;
  }

  public static $JSON_IDENTITY = "mapotempo";

  private $_apiAdmin = MAPOTEMPO_API_KEY;
  private $_templatesIds = array('FR' => MAPOTEMPO_TEMPLATE_ID_FR, 'EN' => MAPOTEMPO_TEMPLATE_ID_EN, 'MA' => MAPOTEMPO_TEMPLATE_ID_MA,
                                  'HE' => MAPOTEMPO_TEMPLATE_ID_HE, 'PT' => MAPOTEMPO_TEMPLATE_ID_PT);
  private $_templateName = "EN";
  private $_trads = array();
  private $_base_user = null;

  public function __construct() {
    // Silence is golden
  }

  // Main initializer
  public static function newInstanceWithSharedData(&$array) {
    $instance = new self();
    $instance->setSharedData($array);
    return $instance;
  }

  public function setSharedData(&$array) {
    $this->sharedArray = &$array;
  }

  public function setFormData(&$data) {
    $this->dataFormatted = $data;
    $this->addDefaultData();
    $this->sanitizeData();
  }

  public function initConnection() {
    $this->_trads = getFormResponsesBy($this->dataFormatted['localization']);
    $this->checkUser();
    $this->fetchUserFromBaseCustomer();
    
    if ($this->error) {
      return;
    }

    $this->sharedArray['Ines']['Mapotempo-web'] = '1';
    $url = MAPOTEMPO_URL_API_CUSTOMER . $this->_templatesIds[$this->_templateName] . "/duplicate?api_key=" . $this->_apiAdmin;
    $url .= "&exclude_users=true";

    $ch = curl_init();
    $this->setPutRequest($ch, $this->dataFormatted, true, $url);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
      $this->controlResponse($response = json_decode($response));
    } else {
      throw new MapotempoServerError("Mapotempo | Error Processing Request", 1);
    }

    if (!$this->error) {
      $this->updateDuplicatedCustomer($response->id);
      $this->fetchUserFromBaseCustomer();
      $this->createUserFor($response->id);
    }
  }

  private function fetchUserFromBaseCustomer() {
    $url = MAPOTEMPO_URL_USERS_CUSTOMER;
    $url .= $this->_templatesIds[$this->_templateName];
    $url .= '/users?api_key=' . $this->_apiAdmin;

    $ch = curl_init();
    $this->setGetRequest($ch, true, $url);
    $response = json_decode( curl_exec($ch) );
    curl_close($ch);

    if (!is_array($response) || array_key_exists('error', $response)) {
      $this->error = true;
      throw new MapotempoServerError("can't get user informations");
    }
    
    $this->_base_user = $response[0];
  }

  private function updateDuplicatedCustomer($id) {
    $date = new DateTime('NOW');
    $date->modify("+15 day");

    $data = array(
      'devices' => '{ "'. $this->dataFormatted['NavigationGPS'] .'": { "enable": "true" } }',
      'end_subscription' => date_format($date, 'Y-m-d'),
      'name' => $this->dataFormatted['company'],
      'api_key' => $this->_apiAdmin,
      'description' => '[' . $this->_templateName . '][WebLead]'
    );

    $url = MAPOTEMPO_URL_API_CUSTOMER . $id;

    $ch = curl_init();
    $this->setPutRequest($ch, $data, true, $url);
    $response = curl_exec($ch);

    curl_close($ch);
  }

  public function addDefaultData() {
    $accept_language = 'FR-fr';
    
    if (isset($_SERVER) &&
        array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
      $accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }

    $dataRequired = array(
      'max_vehicles' => 4,
      'default_country' => substr($accept_language, 0, 2),
      'router_id' => 1,
      'profile_id' => 1
    );

    if (is_array($this->dataFormatted)) {
      $this->dataFormatted += $dataRequired;
    } else {
      throw new InstanceException('Add data only to the base dataFormatted array. (Type used: ' . gettype($this->dataFormatted) . ')');
    }
  }

  private function controlResponse($response) {
    $error = false;

    if (is_object($response)) {
      if (property_exists($response, 'backtrace'))
        $error = (is_array($response->backtrace)) ? implode(" ", $response->backtrace) : $response->backtrace;
      elseif (property_exists($response, 'message'))
        $error = (is_array($response->message)) ? implode(" ", $response->message) : $response->message;
      elseif (property_exists($response, 'error'))
        $error = (is_array($response->error)) ? implode(" ", $response->error) : $response->error;

      if ($error) {
        $this->error = true;
        throw new MapotempoServerError($error);
      }
    }
  }

  private function createUserFor($customer_id) {
    $lang = ($this->_templateName == 'MA') ? 'fr' : strtolower($this->_templateName);
    $data = array(
      "api_key" => $this->_apiAdmin,
      "email" => $this->dataFormatted['email'],
      "password" => bin2hex(openssl_random_pseudo_bytes(4)),
      "customer_id" => $customer_id,
      "layer_id" => 1,
      "prefered_unit" => 'km',
      'time_zone' => 'Paris',
      "locale" => $lang
    );

    if (isset($this->_base_user)) {
      $user = $this->_base_user;

      $data['time_zone'] = $user->time_zone;
      $data['prefered_unit'] = $user->prefered_unit;
      $data['layer_id'] = $user->layer_id;
    }

    $ch = curl_init();
    $this->setPostRequest($ch, $data, true, MAPOTEMPO_URL_API_USER);
    $response = curl_exec($ch);
    curl_close($ch);

    $this->controlResponse($response = json_decode($response));

    if (function_exists('has_filter')) {
      if (!$filterExist = has_filter('wpcf7_display_message', array($this, 'wpcf7CountryNotHandled')))
        add_filter('wpcf7_display_message', array($this, 'wpcf7SuccessMessage'), $filterExist, 2);

      if ($this->dataFormatted['fleetsize'] > 2)
        add_filter('wpcf7_display_message', array($this, 'wpcf7VehicleLimitIsOverpassed'), 10, 2);
    }
  }

  private function checkUser() {
    $parameters = "?api_key=" . $this->_apiAdmin . "&email=" . $this->dataFormatted['email'];
    
    $ch = curl_init();
    $this->setGetRequest($ch, true, MAPOTEMPO_URL_API_USER . $parameters);
    $response = json_decode( curl_exec($ch) );
    curl_close($ch);
    
    $this->controlResponse($response);
    $this->error = $this->emailAlreadyTaken($response);
    $this->sharedArray['Ines']['Mapotempo-web'] = '0';

    if (!$this->error) {
      $this->countriesManager();
    }
  }

  private function emailAlreadyTaken($rsp) {
    $error = false;

    if ((is_array($rsp) && sizeof($rsp) > 0)) {
      $error = $this->emailAlreadyTaken($rsp[0]);
    } elseif (is_object($rsp) && $rsp->id) {
      add_filter('wpcf7_display_message', array($this, 'wpcf7MailErrorMessage'), 10, 2);
      $error = true;
    }

    return $error;
  }

  private function countriesManager() {
    $countriesArray = array();
    $notSupported = true;

    $fr = file_get_contents(__DIR__ . "/translations/fr.json");
    $ma = file_get_contents(__DIR__ . "/translations/ma.json");
    $en = file_get_contents(__DIR__ . "/translations/en.json");
    $pt = file_get_contents(__DIR__ . "/translations/pt.json");
    $he = file_get_contents(__DIR__ . "/translations/he.json");

    $frCountries = array(
      "template" => "FR",
      'countries' => json_decode($fr, true)[self::$JSON_IDENTITY]['countries']
    );
    
    $maCountries = array(
      "template" => "MA",
      'countries' => json_decode($ma, true)[self::$JSON_IDENTITY]['countries']
    );

    $enCountries = array(
      'template' => 'EN',
      'countries' => json_decode($en, true)[self::$JSON_IDENTITY]['countries']
    );

    $ptCountries = array(
      'template' => 'PT',
      'countries' => json_decode($pt, true)[self::$JSON_IDENTITY]['countries']
    );

    $heCountries = array(
      'template' => 'HE',
      'countries' => json_decode($he, true)[self::$JSON_IDENTITY]['countries']
    );

    array_push($countriesArray, $frCountries);
    array_push($countriesArray, $enCountries);
    array_push($countriesArray, $maCountries);
    array_push($countriesArray, $ptCountries);
    array_push($countriesArray, $heCountries);

    for ($i = 0; $i < sizeof($countriesArray); $i++) {
      if (in_array($this->dataFormatted['country'], $countriesArray[$i]['countries'])) {
        $this->_templateName = $countriesArray[$i]['template'];
        $notSupported = false;
        break;
      }
    }

    if ($notSupported)
      add_filter('wpcf7_display_message', array($this, 'wpcf7CountryNotHandled'), 10, 2);
  }
}
