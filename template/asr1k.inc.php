<?php

  if (!array_key_exists('licence', $v)) {
    $v['licence'] = 'ipbase';
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

  $v['interfaces'] = array_merge(
    array(
      'GigabitEthernet0/0/0' => null,
      'GigabitEthernet0/0/1' => null,
      'GigabitEthernet0/0/2' => null,
    ),
    $v['interfaces']
  );

  $vpn = (array_key_exists('vpn', $v) && $v['vpn'] == 'true');

  $mgmt = 'GigabitEthernet0';
  $mgmtVRF = 'Mgmt-intf';

?>
!
! Last configuration change at 02:48:39 UTC Tue Feb 11 2014 by theo
!
version 15.4
no service pad
service tcp-keepalives-in
service tcp-keepalives-out
service timestamps debug datetime msec
service timestamps log datetime msec show-timezone
service password-encryption
service sequence-numbers
no service dhcp
service unsupported-transceiver
no platform punt-keepalive disable-kernel-core
!
hostname asr1k
!
boot-start-marker
boot system bootflash:<?php echo $image, "\n" ?>
boot-end-marker
!
<?php if ($currentLicence != 'ipbase'): ?>
aqm-register-fnf
<?php endif; ?>
!
vrf definition Mgmt-intf
 description Management network
 !
 address-family ipv4
 exit-address-family
 !
 address-family ipv6
 exit-address-family
!
vrf definition internal
 description V4 Internal Network (10.X.X.X)
 !
 address-family ipv4
 exit-address-family
 !
 address-family ipv6
 exit-address-family
!
logging count
logging buffered 16384 informational
!
aaa new-model
!
aaa session-id common
no ip source-route
ip options drop
prompt %h/<?php echo $id ?>%p
!
!
no ip bootp server
ip domain lookup source-interface Loopback0
ip domain name <?php echo $id ?>.dc.ftw.jiveip.net
ip name-server 8.8.8.8
ip dhcp bootp ignore
!
login on-failure log
login on-success log
ipv6 multicast rpf use-bgp
!
subscriber templating
!
flow record FLOW-RECORD-1
 collect routing source as
 collect routing forwarding-status reason
 collect routing next-hop address ipv4
 collect ipv4 source prefix
 collect ipv4 source mask
 collect ipv4 destination prefix
 collect ipv4 destination mask
 collect interface input
 collect interface output
 collect flow direction
 collect counter bytes
 collect counter packets
 collect timestamp sys-uptime first
 collect timestamp sys-uptime last
 collect flow end-reason
 collect connection initiator
 collect connection new-connections
 collect connection client ipv4 address
 collect connection client transport port
 collect connection server ipv4 address
 collect connection server transport port
 collect connection id
 collect connection server counter bytes network long
 collect connection client counter bytes network long
!
!
<?php foreach ($general['flow-exporter'] as $cid => $i): ?>
flow exporter COLLECTOR-<?php echo $cid, "\n" ?>
 description <?php echo $i['description'], "\n" ?>
 destination <?php echo $i['host'], "\n" ?>
 source Loopback0
 transport udp <?php echo $i['port'], "\n" ?>
!
<?php endforeach; ?>
!
flow monitor PUBLIC
 description Monitor Public IP Traffic
<?php foreach ($general['flow-exporter'] as $cid => $i): ?>
 exporter COLLECTOR-<?php echo $cid, "\n" ?>
<?php endforeach ?>
 statistics packet protocol
 statistics packet size
 record netflow ipv4 original-input
!
!
multilink bundle-name authenticated
!
<?php if (isset($v['serial'])): ?>
license udi pid ASR1001 sn <?php echo $v['serial'], "\n" ?>
<?php endif; ?>
license accept end user agreement
license boot level <?php echo $v['licence'], "\n" ?>
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
memory statistics history table 72
memory reserve critical 65536
memory free low-watermark processor 1048576
!
<?php if ($currentLicence != 'ipbase'): ?>
spanning-tree extend system-id
<?php endif; ?>
!
<?php include("users.inc.php") ?>
!
redundancy
 mode none
!
ip tftp source-interface GigabitEthernet0
ip ssh time-out 60
ip ssh authentication-retries 1
ip ssh version 2
<?php include("ssh.inc.php") ?>
!
lldp run
!
!
<?php if ($vpn): ?>
crypto keyring internal-keyring  
<?php foreach ($data as $rid => $rdata): ?>
<?php   if ($rid != $id): ?>
  ! <?php echo $rid ?> ASR1K Endpoint
  pre-shared-key address <?php echo long2ip(1+ ip2long(jnet($rdata['public']))) ?> key <?php echo $general['key'], "\n" ?>
<?php   endif; ?>
<?php endforeach; ?>
  ! CHI V4 SSG Endpoint
  pre-shared-key address <?php echo $general['v4compat']['ipsec']['host'] ?> key <?php echo $general['key'], "\n" ?>
!
!
!
crypto isakmp policy 10
 encr aes
 authentication pre-share
 group 2
!
crypto isakmp keepalive 10
!
<?php foreach ($data as $rid => $rdata): ?>
<?php   if ($rid != $id): ?>
crypto isakmp profile internal-<?php echo $rid ?>-profile
   vrf internal
   keyring internal-keyring
   match identity address <?php echo  long2ip(1 + ip2long(jnet($rdata['public']))) ?> 255.255.255.255
   isakmp authorization list default
   local-address Loopback0
<?php   endif; ?>
<?php endforeach; ?>
!
crypto ipsec security-association replay window-size 128
!
crypto ipsec transform-set esp-3des-sha esp-aes esp-sha-hmac 
 mode tunnel
!
<?php foreach ($data as $rid => $rdata): ?>
<?php   if ($rid != $id): ?>
crypto ipsec profile internal-<?php echo $rid ?>-profile
 set transform-set esp-3des-sha 
 set isakmp-profile internal-<?php echo $rid ?>-profile
<?php   endif; ?>
<?php endforeach; ?>
!
!
<?php endif; ?>
!
!
!
!
interface Loopback0
 description Public Loopback (Jive PI address space)
 ip address <?php echo $public['asr1k'] ?> 255.255.255.255
!
interface Loopback1
 description Public VPN Termination Loopback (Jive PI address space)
 ip address <?php echo $public['vpn'] ?> 255.255.255.255
!
<?php if ($vpn): ?>
!
<?php $count = 0 ?>
<?php foreach ($data as $rid => $rdata): ?>
<?php   if ($rid != $id): ?>
<?php     $count++; ?>
interface Tunnel<?php echo (200 + $rdata['id']), "\n" ?>
 vrf forwarding internal
 ip unnumbered GigabitEthernet0/0/3.4
 ip tcp adjust-mss 1387
 ip ospf 1 area 0
 tunnel source Loopback0
 tunnel mode ipsec ipv4
 tunnel destination <?php echo  long2ip(1 + ip2long(jnet($rdata['public']))), "\n" ?>
 tunnel protection ipsec profile internal-<?php echo $rid ?>-profile
 ip virtual-reassembly
!
<?php   endif; ?>
<?php endforeach; ?>
<?php endif; ?>
!
!
!
<?php foreach ($v['interfaces'] as $iface => $key): ?>
interface <?php echo $iface, "\n" ?>
<?php   if ($key === null): ?>
 no ip address
 shutdown
 no negotiation auto
<?php   else: ?>
 description <?php echo $key['description'], "\n" ?>
 ip address <?php echo $key['address'], " ", $key['netmask'], "\n" ?>
 no ip redirects
 no ip unreachables
 no lldp transmit
 no lldp receive
 ip verify unicast source reachable-via rx
 no negotiation auto
<?php   endif;?>
!
<?php endforeach; ?>
!
! Primary Trunk to SW-1 (cisco cat)
!
interface GigabitEthernet0/0/3
 no ip address
 negotiation auto
!
! V4 Compat VPN (10.x)
!
interface GigabitEthernet0/0/3.4
 vrf forwarding internal
 encapsulation dot1Q 4
 ip address 10.<?php echo (100 + $v['id']) ?>.255.1 255.255.255.0
!
! Public IP Spacing
!
interface GigabitEthernet0/0/3.10
 encapsulation dot1Q 10
 ip unnumbered Loopback0
!
! Management Interface
!
interface GigabitEthernet0
 vrf forwarding Mgmt-intf
 ip address <?php echo $private['asr1k'] ?> 255.255.255.240
 negotiation auto
!
!
router ospf 1 vrf internal
 ispf
 prefix-suppression
 timers throttle spf 10 100 1000
 timers throttle lsa 10 100 1000
 timers lsa arrival 50
 timers pacing flood 5
 timers pacing retransmission 60
 passive-interface default
<?php if ($vpn): ?>
<?php foreach ($data as $rid => $rdata): ?>
<?php   if ($rid != $id): ?>
 no passive-interface Tunnel<?php echo (200 + $rdata['id']), "\n" ?>
<?php   endif; ?>
<?php   endforeach; ?>
<?php endif; ?>
 network 10.0.0.0 0.255.255.255 area 0
!
!
router bgp <?php echo $autnum, "\n" ?>
 bgp log-neighbor-changes
 bgp graceful-restart restart-time 120
 bgp graceful-restart stalepath-time 360
 bgp graceful-restart
 bgp maxas-limit 24
 neighbor transit-peer peer-group
 neighbor transit-peer description Full Table Transit Peer
<?php foreach ($v['peers'] as $key): ?>
 neighbor <?php echo $key['address'] ?> remote-as <?php echo $key['remote-as'], "\n" ?>
 neighbor <?php echo $key['address'] ?> peer-group transit-peer
<?php endforeach; ?>
 !
 address-family ipv4
  network <?php echo $pubnet ?> mask <?php echo $pubmask ?> route-map PUBLIC_ANNOUNCE
  neighbor transit-peer next-hop-self
  neighbor transit-peer allowas-in
  neighbor transit-peer route-map TRANSIT_IN in
  neighbor transit-peer route-map TRANSIT_OUT out
  neighbor transit-peer prefix-list bogons in
<?php foreach ($v['peers'] as $key): ?>
  neighbor <?php echo $key['address'] ?> activate
<?php endforeach; ?>
 exit-address-family
!
ip forward-protocol nd
!
no ip http server
no ip http secure-server
ip http client source-interface GigabitEthernet0
ip route <?php echo $pubnet ?> <?php echo $pubmask ?> GigabitEthernet0/0/3.10
ip route vrf internal 10.<?php echo (100 + $v['id']) ?>.0.0 255.255.0.0 Null0
ip route vrf Mgmt-intf 0.0.0.0 0.0.0.0 <?php echo $private['c1921'], "\n" ?>
!
!
!
ip prefix-list BGP_TO_TRANSIT description Prefixes to announce to transits over BGP
ip prefix-list BGP_TO_TRANSIT seq 5 permit <?php echo $v['public'], "\n" ?>
!
!
ip prefix-list bogons-in description Denies internet bogon routes
ip prefix-list bogons-in seq 100 deny 0.0.0.0/8 le 32
ip prefix-list bogons-in seq 101 deny 10.0.0.0/8 le 32
ip prefix-list bogons-in seq 102 deny 127.0.0.0/8 le 32
ip prefix-list bogons-in seq 103 deny 169.254.0.0/16 le 32
ip prefix-list bogons-in seq 104 deny 172.16.0.0/12 le 32
ip prefix-list bogons-in seq 105 deny 192.0.2.0/24 le 32
ip prefix-list bogons-in seq 106 deny 192.168.0.0/16 le 32
ip prefix-list bogons-in seq 107 deny 224.0.0.0/3 le 32
ip prefix-list bogons-in seq 1000 permit 0.0.0.0/0 le 32
!
ip sla 1
 icmp-echo 172.16.0.136 source-interface GigabitEthernet0
 vrf Mgmt-intf
 tag overlord
 threshold 100
 timeout 500
 frequency 1
ip sla schedule 1 life forever start-time now
!
!
logging alarm minor
logging origin-id string asr1k.<?php echo $id, "\n" ?>
logging source-interface Loopback0
<?php foreach ($general['syslog'] as $sid => $vals): ?>
<?php if ($vals['port'] != 601): ?>
logging host <?php echo $vals['host'] ?> transport tcp port <?php echo $vals['port'] ?> sequence-num-session
<?php else: ?>
logging host <?php echo $vals['host'] ?> transport tcp sequence-num-session
<?php endif; ?>
<?php endforeach; ?>
!
route-map TRANSIT_IN permit 10
!
route-map PUBLIC_ANNOUNCE permit 10
 description Applied to public announcement (/24)
!
route-map TRANSIT_OUT permit 10
 match ip address prefix-list BGP_TO_TRANSIT
!
route-map TRANSIT_OUT deny 1000
!
!
control-plane
!
! banner login ~-------------------------------------------------------------------------------
!                       Jive - <?php echo $id ?> - ASR 1001 - 1
! -------------------------------------------------------------------------------
! ~
! banner motd ~
!  -> Type 'overlord-diff' to show differing configuration from overlord server.
!  -> Type 'overlord-apply' to automatically fetch autogenerated config and apply.
!  ->   ... then type 'overlord-confirm' to save it (otherwise it will be rolled back).
!  ->   ... then type 'overlord-rollback' to rollback immediatly.
!
!~
!
<?php include("alias.inc.php") ?>
configuration mode exclusive
!
!
line con 0
 privilege level 15
 transport preferred none
 transport output none
 stopbits 1
 exec-timeout 0 0
line aux 0
 exec-timeout 0 1
 no exec
 transport output none
 stopbits 1
line vty 0 4
 privilege level 15
 transport preferred none
 transport input ssh
 transport output telnet ssh
!
ntp source Loopback0
ntp server <?php echo $general['ntp'], "\n" ?>
!
event manager applet CLIaccounting
 event cli pattern ".*" sync no skip no
 action 1.0 syslog priority informational msg "$_cli_msg"
 action 2.0 set _exit_status "1"
!
end
