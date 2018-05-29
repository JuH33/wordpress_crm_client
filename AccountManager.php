<?php

require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/lib/BaseClient.php");
require_once(__DIR__ . "/lib/iModelCRM.php");
require_once(__DIR__ . "/lib/traitsConnection.php");
require_once(__DIR__ . "/lib/traitsWPCF7.php");

class InstanceException extends Exception { };

class AccountManager {

  private $type = '.php';

  private $formData;
  private $classNames;
  private $factoryClasses;
  private $facotrySharedArray;

  public function __construct($data, $classLoaderNames) {
    $this->formData = (array) $data;
    $this->classNames = (array) $classLoaderNames;
    $this->factoryClasses = array();
  }

  public function initFactory() {
    $base_path = __DIR__ . "/";
    $this->facotrySharedArray = $this->initSharedArray();

    foreach ($this->classNames as &$klass) {
      if ($file = file_exists($base_path . $klass . $this->type)) {
        require $base_path . $klass . $this->type;

        $instance = $klass::newInstanceWithSharedData($this->facotrySharedArray);
        if ($instance instanceof IModelCRM) {
          $this->factoryClasses[] = $instance;
        } else {
          throw new InstanceException('Make sure the name of your class coresspond to
           IModelCRM class interface declared in : templates-crm folder');
        }
      }
    }
  }

  private function initSharedArray() {
    $array;
    foreach ($this->classNames as $k) {
      $array[$k] = array();
    }
    return $array;
  }

  public function startConnections() {
    for ($i = 0; $i < sizeof($this->factoryClasses); $i++) {
      $this->factoryClasses[$i]->setFormData($this->formData);

      try {
        $this->factoryClasses[$i]->initConnection();
      } catch (MapotempoServerError $e) {
        print($e->getMessage());
      }

    }
  }

  function __destruct() {
    $this->cf7 = null;
    $this->formData = null;
    $this->facotrySharedArray = null;
  }
};
