<?php


  include "../lib/utils.php";


  include '../lib/Spyc.php';


  $general = spyc_load_file('../config/general.yml');
  $asr1k = spyc_load_file('../config/asr1k.yml');

  $autnum = $general['autnum'];
  $image = $asr1k['firmware'];

  $data = array();

  foreach (array_diff(scandir('../sites'), array('.', '..')) as $site)
  {
    if (substr($site, -4) == '.yml')
    {
      $id = substr($site, 0, -4);
      $data[$id] = spyc_load_file(sprintf('../sites/%s', $site));
    }
  }

  $keys = spyc_load_file('../config/keys.yml');

  $include = null;

  foreach ($data as $id => $v)
  {
    echo "Generating ${id} ...";
    foreach (array('asr1k', 'c1921', 'sw1') as $include)
    {
      echo " ", $include;
      ob_start();
      include("../template/${include}.inc.php");
      $page = ob_get_contents();
      ob_end_clean();
      file_put_contents("../output/${include}.${id}-confg", $page);
    }
    echo "\n";

  }

?>
