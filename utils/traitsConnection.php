<?php

trait traitsConnection {
  function setPutRequest($ch, &$args, $hasReturn = true, $url, $header = array()) {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $hasReturn);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  }
  
  function setPostRequest($ch, &$args, $hasReturn = true, $url, $header = array()) {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $hasReturn);
    curl_setopt($ch, CURLOPT_POST, count($args));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  }
  
  function setGetRequest($ch, $hasReturn = true, $url, $header = array()) {
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $hasReturn);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  }
}
