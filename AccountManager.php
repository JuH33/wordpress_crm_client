<?php

require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/utils/BaseClient.php");
require_once(__DIR__ . "/utils/iModelCRM.php");
require_once(__DIR__ . "/utils/traitsConnection.php");
require_once(__DIR__ . "/utils/traitsWPCF7.php");

class InstanceException extends Exception { };

class AccountManager {

  private $type = '.php';

  private $formData;            // Data from cf7 form
  private $classNames;          // Class names as string
  private $factoryClasses;      // Array of clients instance
  private $facotrySharedArray;  // Array shared by all clients
  private $postId;              // Current WPCF7 Post id

  public function __construct($data, $classLoaderNames, $postId = null) {
    $this->formData = (array) $data;
    $this->classNames = (array) $classLoaderNames;
    $this->factoryClasses = array();
    $this->postId = $postId;
  }

  public function initFactory() {
    $base_path = __DIR__ . "/clients/";
    $this->facotrySharedArray = $this->initSharedArray();

    foreach ($this->classNames as &$klass) {
      if ($file = file_exists($base_path . $klass . $this->type)) {
        require $base_path . $klass . $this->type;

        $instance = $klass::newInstanceWithSharedData($this->facotrySharedArray, $this->postId);
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

      try {
        $this->factoryClasses[$i]->setFormData($this->formData);
        $this->factoryClasses[$i]->initConnection();
      } catch (Exception $e) {
        // print($e->getMessage());
        // {JS gone crazy if print is active} FIXME
      }
      
    }
  }

  function __destruct() {
    $this->cf7 = null;
    $this->formData = null;
    $this->facotrySharedArray = null;
  }
};
