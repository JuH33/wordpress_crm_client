<?php

interface IModelCRM {
  public function setFormData(&$data);
  public function initConnection();

  public static function newInstanceWithSharedData(&$array);
  public function setSharedData(&$array);
  public function addDefaultData();
}
