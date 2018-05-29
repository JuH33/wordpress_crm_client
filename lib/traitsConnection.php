<?php

trait traitsConnection {
  function setPutRequest($ch, &$args, $hasReturn = true, $url) {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $hasReturn);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $args);

    if (property_exists(get_class($this), 'dataFormatted'))
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Language: ' . $this->dataFormatted['localization']));
  }
  
  function setPostRequest($ch, &$args, $hasReturn = true, $url) {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $hasReturn);
    curl_setopt($ch, CURLOPT_POST, count($args));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    
    if (property_exists(get_class($this), 'dataFormatted'))
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Language: ' . $this->dataFormatted['localization']));
  }
  
  function setGetRequest($ch, $hasReturn = true, $url) {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $hasReturn);
    
    if (property_exists(get_class($this), 'dataFormatted'))
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Language: ' . $this->dataFormatted['localization']));
  }
}
