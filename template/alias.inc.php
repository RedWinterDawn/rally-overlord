alias exec overlord-diff show archive config differences <?php echo expr($general['config']), "\n" ?>  
alias exec overlord-apply configure replace <?php echo expr($general['config']) ?> list force ignorecase revert trigger error timer 1 
alias exec overlord-confirm configure confirm 
alias exec overlord-revert configure revert 
alias exec overlord-ping ping <?php

  if ($mgmtVRF !== null) {
    echo "vrf Mgmt-intf "; 
  }

?>172.16.0.136 timeout 1 repeat 1 source <?php echo $mgmt, "\n" ?> 
