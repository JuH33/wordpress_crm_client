<?php

class InesException extends Exception { /* */ }

class Ines extends BaseClient implements IModelCRM {

  public static $JSON_IDENTITY = "ines";
  private $log_file = __DIR__ . '/../logs/ines.log.txt';

  private $_userCreatedName;

  use traitsConnection {
    setPostRequest as private;
  }

  public function __construct() { /* Silence is golden */ }

  public static function newInstanceWithSharedData(&$array, $postId) {
    $instance = new self();
    $instance->setSharedData($array);
    return $instance;
  }

  public function setSharedData(&$array) {
    $this->sharedArray = &$array;
  }

  public function setFormData(&$data) {
    $this->dataFormatted = $data;
    $this->sanitizeData();
    $this->addDefaultData();
  }

  public function addDefaultData() {
    $matcher = $this->getMatcherArrayFromLocalization();

    foreach ($this->dataFormatted as $key => $value) {
      if (array_key_exists($key, $matcher)) {
        unset($this->dataFormatted[$key]);

        $this->dataFormatted[$matcher[$key]] = $value;
      }
    }
  }

  public function initConnection() {
    $url = INES_URL;

    if (array_key_exists('Mapotempo-web', $this->sharedArray[get_class($this)]) && !empty($this->_userCreatedName)) {
      $this->dataFormatted[$this->_userCreatedName] = $this->sharedArray[get_class($this)]['Mapotempo-web'];
    }

    $ch = curl_init();
    $this->setPostRequest($ch, $this->dataFormatted, true, $url);
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($statusCode != 200) {
      $serial = serialize($response);
      $infos = "Ines error, status {$statusCode} [{$serial}]";
      parent::log($this->log_file, $infos, true);
    }
  }

  private function getMatcherArrayFromLocalization() {
    $loc = strtolower($this->dataFormatted['localization']);
    $matcher;

    switch ($loc) {
      case "fr":
        $fr = file_get_contents(__DIR__ . "/../translations/fr.json");
        $matcher = json_decode($fr, true)[self::$JSON_IDENTITY]['formKeys'];
        $this->_userCreatedName = 'Checkbox_77800';
        break;
      case "he":
        $he = file_get_contents(__DIR__ . "/../translations/he.json");
        $matcher = json_decode($he, true)[self::$JSON_IDENTITY]['formKeys'];
        $this->_userCreatedName = 'Checkbox_85561';
        break;
      case "pt":
        $pt = file_get_contents(__DIR__ . "/../translations/pt.json");
        $matcher = json_decode($pt, true)[self::$JSON_IDENTITY]['formKeys'];
        $this->_userCreatedName = 'Checkbox_85580';
        break;
      case "en":
      default:
        $en = file_get_contents(__DIR__ . "/../translations/en.json");
        $matcher = json_decode($en, true)[self::$JSON_IDENTITY]['formKeys'];
        $this->_userCreatedName = 'Checkbox_77801';
        break;
    }

    return $matcher;
  }
}
