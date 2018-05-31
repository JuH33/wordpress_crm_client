<?php

abstract class BaseClient {

  protected $descriptorCompleted;
  protected $sharedArray;
  protected $postId;
  protected $error = false;

  protected $dataFormatted;

  // Static for now, need to code fast [CF7 DATA] Tagged:UPDATE Task:Regex
  private $arrayToDismatch = array('_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag');

  /**
  * Check cf7 polued data only
  */
  protected function sanitizeData() {
    foreach ($this->dataFormatted as $key => $value) {
      if (in_array($key, $this->arrayToDismatch)) {
        unset($this->dataFormatted[$key]);
      }
    }
  }

  protected function __construct($postId) {
    $this->postId = $postId;
  }

  protected function log($filepath, $content, $append = true) {
    $date = date(DATE_RFC2822);
    $logs = "[{$date}] :: ";
    $logs .= serialize($content);
    $logs .= PHP_EOL;
    $editMethods = ($append) ? FILE_APPEND | LOCK_EX : null;

    file_put_contents($filepath, $logs, $editMethods);
  }
}
