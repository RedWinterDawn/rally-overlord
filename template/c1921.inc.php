<?php

  $mgmt = 'GigabitEthernet0/0';
  $mgmtVRF = null;

  $vpns = $v['c1900']['overlord'];

  if (!isset($vpns) || !is_array($vpns)) {
    $vpns = array();
  }


  list ($pubnet, $pubbits) = explode('/', $v['public']);

  if (!is_int((int)$pubbits)) {
    die("Invalid public mask '${pubbits}'");
  }

  $pubmask = jmask($v['public']);

  $net = ip2long($pubnet); // $v['public']);
  $smask = $pubmask; //ip2long("255.255.255.0");

  $public = array(
    'asr1k' => long2ip(ip2long($pubnet) + 1),
    'c1900' => long2ip(ip2long($pubnet) + 2),
    'vpn' => long2ip(ip2long($pubnet) + 3),
  );

  $net = ip2long($v['internal']);
  $smask = ip2long("255.255.255.240");

  $private = array(
    'c1921' => long2ip(($net & $smask) + 1),
    'asr1k' => long2ip(($net & $smask) + 2)
  );

  $v4compat = array(
    'asr1k' => '10.999.255.1'
  );

?>
!
! Last configuration change at 22:19:57 UTC Sat Feb 22 2014 by theo
!
version 15.3
service timestamps debug datetime msec
service timestamps log datetime msec
no service password-encryption
service internal
!
hostname c1921.<?php echo $id, "\n" ?>
!
boot-start-marker
boot system flash:c1900-universalk9-mz.SPA.153-2.T.bin
boot-end-marker
!
!
vrf definition LTE
 !
 address-family ipv4
 exit-address-family
!
logging buffered 51200 warnings
!
aaa new-model
!
!
aaa authentication login sslvpn local
!
!
!
!
!
aaa session-id common
!
ip cef
!
!
!
!
!
!
!
!         
!
!
ip domain retry 0
ip domain timeout 1
ip domain name oob.<?php echo $id ?>.dc.ftw.jiveip.net
ip inspect WAAS flush-timeout 10
no ipv6 cef
!
multilink bundle-name authenticated
!
chat-script lte "" "AT!CALL1" TIMEOUT 60 "OK"
!
crypto pki trustpoint TP-self-signed-219500257
 enrollment selfsigned
 subject-name cn=IOS-Self-Signed-Certificate-219500257
 revocation-check none
 rsakeypair TP-self-signed-219500257
!
!
crypto pki certificate chain TP-self-signed-219500257
 certificate self-signed 01
  30820229 30820192 A0030201 02020101 300D0609 2A864886 F70D0101 05050030 
  30312E30 2C060355 04031325 494F532D 53656C66 2D536967 6E65642D 43657274 
  69666963 6174652D 32313935 30303235 37301E17 0D313331 31323731 37303032 
  375A170D 32303031 30313030 30303030 5A303031 2E302C06 03550403 1325494F 
  532D5365 6C662D53 69676E65 642D4365 72746966 69636174 652D3231 39353030 
  32353730 819F300D 06092A86 4886F70D 01010105 0003818D 00308189 02818100 
  AAE963ED E6D8C330 2C5D5B87 64767672 DCD17A3B D7A8953E DCCA80A3 0D0D5331 
  E4C32407 E8481C22 941342A9 4EF8FB98 94E3175D FB9CFB9E 4B49AB0D 12A22AD5 
  9AFC8C28 6F6CE1F3 9CDF76E1 8F4551B8 E8D167ED 9D1E2D73 64B45AF1 3DF1C466 
  2CA997BD 0C321966 9AE831AA FF72B6C1 729E3CCB F8887B1A BDB71272 51ED2F45 
  02030100 01A35330 51300F06 03551D13 0101FF04 05300301 01FF301F 0603551D 
  23041830 16801436 ECEB27C5 B708F6A6 6D7F6DF7 72FE597D D9233230 1D060355 
  1D0E0416 041436EC EB27C5B7 08F6A66D 7F6DF772 FE597DD9 2332300D 06092A86 
  4886F70D 01010505 00038181 00116CCA 20FF9306 1A3A5111 08C15387 C0420286 
  57977895 A24FED78 E21B437E F71141A6 CF2F6E95 238D5395 0508AABD 3A8E2BA1 
  38901648 79522B1A 0052985D 3B6CF91D B59AF2C1 DE7BC454 397CD591 1481E4A3 
  0E7B43E5 1B833FB8 7009256A FCCCA73E 5D30A411 1CAA3A48 4560F35D CC3A0FA8 
  47B4916E 0AF3A561 9F808CA3 92
  	quit
license udi pid CISCO1921/K9 sn <?php echo $v['c1900']['serial'], "\n" ?>
!
!
<?php include("users.inc.php") ?>
!
!
!
archive
 log config
  logging enable
  logging size 200
  notify syslog contenttype plaintext
  hidekeys
 path flash:archived-config
 maximum 14
 write-memory
 time-period 1440
!
!
redundancy
 notification-timer 60000
!
!
!
!
!
controller Cellular 0/0
!
ip ssh authentication-retries 1
ip ssh version 2
<?php include("ssh.inc.php") ?>
!
! 
<?php include("vpns.inc.php") ?>
!
!
!
!
interface Loopback0
 no ip address
!         
!
interface Embedded-Service-Engine0/0
 no ip address
 shutdown
!
interface GigabitEthernet0/0
 description Mgmt-Int
 ip address 172.<?php echo $v['id'] ?>.0.1 255.255.255.0
 ip helper-address 172.16.0.136
 duplex auto
 speed auto
!
interface GigabitEthernet0/1
 ip address 192.168.1.1 255.255.255.0
 duplex auto
 speed auto
!
interface GigabitEthernet0/1.3
 encapsulation dot1Q 3
 ip dhcp relay information option subscriber-id servers
 ip dhcp relay information option-insert 
 ip address 172.<?php echo $v['id'] ?>.1.1 255.255.255.0
 ip helper-address 172.16.0.136
!
interface GigabitEthernet0/1.10
 description Public Internet
 encapsulation dot1Q 10
 ip address <?php echo $public['c1900'] ?> 255.255.255.0
 no cdp enable
!
interface Cellular0/0/0
 no ip address
 encapsulation slip
 dialer in-band
 dialer pool-member 1
 async mode interactive
!
interface Virtual-Template1
 description Virtual-Template attached to internal VRF (WebVPN)
 ip unnumbered GigabitEthernet0/0
!
interface Dialer1
 vrf forwarding LTE
 ip address negotiated
 ip virtual-reassembly in
 ip virtual-reassembly out
 encapsulation slip
 dialer pool 1
 dialer idle-timeout 0
 dialer string lte
 dialer persistent
!
router bgp 65000
 bgp log-neighbor-changes
 !
 neighbor aws-vpn peer-group
 neighbor aws-vpn description AWS-VPN
 neighbor aws-vpn timers 10 30 30
 !
 neighbor aws-vpn-lte peer-group
 neighbor aws-vpn-lte description AWS-VPN
 neighbor aws-vpn-lte timers 10 30 30
 !
<?php foreach ($vpns as $vid => $vd): ?>
 neighbor <?php echo $vd['otherside'] ?> remote-as 7224
<?php if (isset($vd['vrf'])): ?>
 neighbor <?php echo $vd['otherside'] ?> peer-group aws-vpn-lte
<?php else: ?>
 neighbor <?php echo $vd['otherside'] ?> peer-group aws-vpn
<?php endif; ?>
<?php endforeach; ?>
 !
 address-family ipv4
  network 172.<?php echo $v['id'] ?>.0.0
  !
  neighbor aws-vpn next-hop-self
  neighbor aws-vpn soft-reconfiguration inbound
  neighbor aws-vpn prefix-list BGP_FROM_AWS in
  neighbor aws-vpn route-map AWS_IN in
  neighbor aws-vpn route-map AWS_OUT out
  !
  neighbor aws-vpn-lte next-hop-self
  neighbor aws-vpn-lte soft-reconfiguration inbound
  neighbor aws-vpn-lte prefix-list BGP_FROM_AWS in
  neighbor aws-vpn-lte route-map AWS_IN_LTE in
  neighbor aws-vpn-lte route-map AWS_OUT_LTE out
<?php foreach ($vpns as $vid => $vd): ?>
  neighbor <?php echo $vd['otherside'] ?> activate
<?php endforeach; ?>
 exit-address-family
!
ip local pool webvpn-pool 172.<?php echo $v['id'] ?>.0.8 172.<?php echo $v['id'] ?>.0.15
ip forward-protocol nd
!
no ip http server
no ip http secure-server
ip http client source-interface GigabitEthernet0/0
!
ip dns view default
 domain timeout 1
 domain retry 0
!
ip route 0.0.0.0 0.0.0.0 <?php echo $public['asr1k'], "\n" ?>
ip route 172.<?php echo $v['id'] ?>.0.0 255.255.0.0 Null0
ip route vrf LTE 0.0.0.0 0.0.0.0 Dialer1
!
!
ip prefix-list BGP_FROM_AWS seq 5 permit 172.16.0.0/16
!
ip prefix-list BGP_TO_AWS seq 5 permit 172.<?php echo $v['id'] ?>.0.0/16
!
route-map AWS_IN permit 10
route-map AWS_IN_LTE permit 10
!
route-map AWS_OUT permit 10
 match ip address prefix-list BGP_TO_AWS
 set metric 100
route-map AWS_OUT deny 20
!
route-map AWS_OUT_LTE permit 10
 match ip address prefix-list BGP_TO_AWS
 set metric 1000
route-map AWS_OUT_LTE deny 20
!
!
snmp-server community public RO
snmp-server community jive RO
snmp-server location OREM-PROD
snmp-server contact noc@jive.com
snmp-server chassis-id c1901.orem
snmp-server enable traps vstack operation
access-list 23 permit 10.10.10.0 0.0.0.7
!
!
!
control-plane
!
!
!
line con 0
line aux 0
 no exec
 transport input ssh
 stopbits 1
line 2
 no activation-character
 no exec
 transport preferred none
 transport input all
 transport output pad telnet rlogin lapb-ta mop udptn v120 ssh
 stopbits 1
line 0/0/0
 script dialer lte
 no exec
 rxspeed 100000000
 txspeed 50000000
line vty 0 4
 privilege level 15
 transport input telnet ssh
line vty 5 15
 privilege level 15
 transport input telnet ssh
!
scheduler allocate 20000 1000
!
!
webvpn gateway Cisco-WebVPN-Gateway
 ip interface Dialer1 port 443
 ssl trustpoint TP-self-signed-219500257
 inservice
 dtls port 4433
 !
webvpn context Cisco-WebVPN
 virtual-template 1
 aaa authentication list sslvpn
 gateway Cisco-WebVPN-Gateway
 max-users 10
 !
 ssl authenticate verify all
 inservice
 !
 policy group webvpnpolicy
   functions svc-enabled
   filter tunnel ssl-acl
   svc address-pool "webvpn-pool" netmask 255.255.255.0
   svc rekey method new-tunnel
   svc split include <?php echo $v['internal'] ?> 255.255.255.0
 default-group-policy webvpnpolicy
!
<?php include("alias.inc.php") ?>
end


