<?php


  function expr($data) {
    global $id;
    global $include;
    global $v;
    return 
      str_replace("{licence}", $v['licence'], str_replace("{device}", $include, str_replace("{id}", $id, $data))
    );
  }

  function jnet($str) { 
    list ($net, $bits) = explode('/', $str);
    return $net;
  }

  function jbits($str)
  { 
    if (strpos($str, '/') === false) {
      throw new Exception("${str} must contain /");
    }
    list ($net, $bits) = explode('/', $str);
    $bits = (int)$bits;
    if ($bits === null || $bits < 0 || $bits > 32) {
      throw new Exception("Invalid Network: ${str}");
    }
    return (int)$bits;
  }

  function jmask($str)
  { 
    $mask_int = jbits($str);
    $mask_nr = (pow(2, $mask_int) - 1) << (32 - $mask_int); 
    return long2ip($mask_nr);
  }

