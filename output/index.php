<?php

  header("Content-Type: text/plain");

  include '../lib/Spyc.php';
  include '../lib/utils.php';

  $device = str_replace("/", "", $_SERVER['PATH_INFO']);
  $device = str_replace("-confg", "", $device);

  list($device, $site) = explode(".", $device);

  $currentLicence = isset($_GET['licence']) ? $_GET['licence'] : 'ipbase';

  $general = spyc_load_file('../config/general.yml');
  $asr1k = spyc_load_file('../config/asr1k.yml');
  $keys = spyc_load_file('../config/keys.yml');


  $autnum = $general['autnum'];
  $image = $asr1k['firmware'];

  $data = array();

  foreach (array_diff(scandir('../sites'), array('.', '..')) as $sitel)
  {
    if (substr($sitel, -4) == '.yml')
    {
      $id = substr($sitel, 0, -4);
      $data[$id] = spyc_load_file(sprintf('../sites/%s', $sitel));
    }
  }

  $id = $site;
  $v = $data[$id];
  $include = $device;

  include("../template/${device}.inc.php");




