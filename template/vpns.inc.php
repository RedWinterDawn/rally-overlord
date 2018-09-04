<?php

?>
! 
<?php foreach ($vpns as $vid => $vd): ?>
!
! --- VPN <?php echo $vid, "\n" ?>
!
!
crypto keyring keyring-<?php echo $vid ?> <?php echo (isset($vd['vrf'])) ? sprintf("vrf %s", $vd['vrf']) : "" ?> 
  local-address <?php echo $vd['local'] ?> <?php echo (isset($vd['vrf'])) ? sprintf("%s\n", $vd['vrf']) : "\n" ?> 
  pre-shared-key address <?php echo $vd['address'] ?> key <?php echo $vd['key'], "\n" ?>
!
crypto isakmp profile isakmp-<?php echo $vid, "\n" ?>
   keyring keyring-<?php echo $vid, "\n" ?>
   match identity address <?php echo $vd['address'] ?> 255.255.255.255 
   local-address <?php echo $vd['local'] ?> <?php echo (isset($vd['vrf'])) ? sprintf("%s\n", $vd['vrf']) : "\n" ?>
!
crypto ipsec transform-set ipsec-prop-<?php echo $vid ?> esp-aes esp-sha-hmac
 mode tunnel
!
crypto ipsec profile ipsec-<?php echo $vid, "\n" ?>
 set transform-set ipsec-prop-<?php echo $vid, "\n" ?>
 set pfs group2
!
!
interface Tunnel<?php echo $vd['tunnel'], "\n" ?>
 ip address <?php echo $vd['internal'], "\n" ?>
 ip virtual-reassembly in
 ip tcp adjust-mss 1387
 tunnel source <?php echo $vd['local'], "\n" ?>
 tunnel mode ipsec ipv4
 tunnel destination <?php echo $vd['address'], "\n" ?>
<?php if (isset($vd['vrf'])): ?>
 tunnel vrf <?php echo $vd['vrf'], "\n" ?>
<?php endif; ?>
 tunnel protection ipsec profile ipsec-<?php echo $vid, "\n" ?>
!
<?php endforeach; ?>
!
crypto vpn anyconnect usbflash0:/webvpn/anyconnect-macosx-i386-3.1.04072-k9.pkg sequence 1
!
crypto isakmp policy 200
 encr aes
 authentication pre-share
 group 2
 lifetime 28800
!
crypto isakmp policy 201
 encr aes
 authentication pre-share
 group 2
 lifetime 28800
!
crypto isakmp keepalive 10 10
!
crypto ipsec security-association replay window-size 128
!
crypto ipsec df-bit clear
!
