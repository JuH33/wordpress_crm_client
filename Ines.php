<?php

class Ines extends BaseClient implements IModelCRM {

  private $_userCreatedName;

  use traitsConnection {
    setPostRequest as private;
  }

  public function __construct() { /* Silence is golden */ }

  public function setFormData(&$data) {
    $this->dataFormatted = $data;
    $this->sanitizeData();
    $this->addDefaultData();
  }

  public function initConnection() {
    $url = INES_URL;

    if (array_key_exists('Mapotempo-web', $this->sharedArray[get_class($this)]) && !empty($this->_userCreatedName)) {
      $this->dataFormatted[$this->_userCreatedName] = $this->sharedArray[get_class($this)]['Mapotempo-web'];
    }

    $ch = curl_init();
    $this->setPostRequest($ch, $this->dataFormatted, true, $url);
    $response = curl_exec($ch);

    curl_close($ch);
  }

  public static function newInstanceWithSharedData(&$array) {
    $instance = new self();
    $instance->setSharedData($array);
    return $instance;
  }

  public function setSharedData(&$array) {
    $this->sharedArray = &$array;
  }

  private function addDefaultData() {
    $matcher = $this->getMatcherArrayFromLocalization();

    foreach ($this->dataFormatted as $key => $value) {
      if (array_key_exists($key, $matcher)) {
        unset($this->dataFormatted[$key]);

        $this->dataFormatted[$matcher[$key]] = $value;
      }
    }
  }

  // TODO: Remove External Files
  private function getMatcherArrayFromLocalization() {
    $loc = strtolower($this->dataFormatted['localization']);
    $matcher;

    switch ($loc) {
      case "fr":
        $matcher = array(
          'firstname' => 'TextBox_71847',
          'lastname' => 'TextBox_71848',
          'position' => 'TextBox_71849',
          'company' => 'TextBox_71850',
          'country' => 'Select_71851',
          'zipcode' => 'TextBox_71852',
          'industry' => 'Select_71855',
          'email' => 'TextBox_71853',
          'tel' => 'TextBox_73385',
          'fleetsize' => 'TextBox_71856',
          'subject-list' => 'Select_71857',
          'message' => 'TextBox_71858',
          'NavigationGPS' => 'Select_78291'
        );
          $this->_userCreatedName = 'Checkbox_77800';
        break;
      case "he":
        $matcher = array(
          'firstname' => 'TextBox_85550',
          'lastname' => 'TextBox_85549',
          'position' => 'TextBox_85551',
          'company' => 'TextBox_85548',
          'country' => 'Select_85552',
          'zipcode' => 'TextBox_85553',
          'industry' => 'Select_85556',
          'email' => 'TextBox_85554',
          'tel' => 'TextBox_85555',
          'fleetsize' => 'TextBox_85557',
          'subject-list' => 'Select_85558',
          'message' => 'TextBox_85559',
          'NavigationGPS' => 'Select_85562'
        );
        $this->_userCreatedName = 'Checkbox_85561';
        break;
      case "pt":
        $matcher = array(
          'firstname' => 'TextBox_85569',
          'lastname' => 'TextBox_85568',
          'position' => 'TextBox_85570',
          'company' => 'TextBox_85567',
          'country' => 'Select_85571',
          'zipcode' => 'TextBox_85572',
          'industry' => 'Select_85575',
          'email' => 'TextBox_85573',
          'tel' => 'TextBox_85574',
          'fleetsize' => 'TextBox_85576',
          'subject-list' => 'Select_85577',
          'message' => 'TextBox_85582',
          'NavigationGPS' => 'Select_85581'
        );
        $this->_userCreatedName = 'Checkbox_85580';
        break;
      case "en":
      default:
        $matcher = array(
          'firstname' => 'TextBox_74426',
          'lastname' => 'TextBox_74427',
          'position' => 'TextBox_74428',
          'company' => 'TextBox_74429',
          'country' => 'Select_74430',
          'zipcode' => 'TextBox_74431',
          'industry' => 'Select_74434',
          'email' => 'TextBox_74432',
          'tel' => 'TextBox_74433',
          'fleetsize' => 'TextBox_74435',
          'subject-list' => 'Select_74436',
          'message' => 'TextBox_74437',
          'NavigationGPS' => 'Select_78291'
        );
        $this->_userCreatedName = 'Checkbox_77801';
        break;
    }

    return $matcher;
  }
}
