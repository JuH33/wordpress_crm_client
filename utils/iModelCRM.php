<?php

interface IModelCRM {
  public function setFormData(&$data);
  public function initConnection();

  public static function newInstanceWithSharedData(&$array, $postId);
  public function setSharedData(&$array);
  public function addDefaultData();
}
