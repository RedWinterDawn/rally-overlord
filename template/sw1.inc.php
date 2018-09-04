
!
! auto generated by auto-overlord
!

version 15.2
no service pad
service timestamps debug datetime msec
service timestamps log datetime msec
service password-encryption
service sequence-numbers
service unsupported-transceiver
no service dhcp
!
hostname sw1.nyc-1a
!
boot-start-marker
boot-end-marker
!
!
!
aaa new-model
!
!
!
!
!
!
!
!
aaa session-id common
system mtu routing 1500
!
ip vrf Mgmt-intf
!
!
!
no ip domain-lookup
ip domain-name nyc-1a.dc.ftw.jiveip.net
ip device tracking
!
!
!
license boot level ipservices
!
!
!
spanning-tree mode pvst
spanning-tree extend system-id
!
!
!
!
!
!
!
!
!
vlan internal allocation policy ascending
!
ip ssh time-out 60
ip ssh authentication-retries 0
ip ssh version 2
ip ssh pubkey-chain
  username theo
   key-hash ssh-rsa 9FFEADE2D2409F9E8DA08A82FBAD80B5 theo@theo-mba.local
  username pholmes
   key-hash ssh-rsa C792175A2613CF42997CF16C6EACE6BC GitHub@holmes41@gmail.com
  username mmartinez
   key-hash ssh-rsa F759896802FF575A431BF27AAB5F544E mmartinez@getjive.com
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
!
interface FastEthernet0
 description management
 ip vrf forwarding Mgmt-intf
 ip address 172.20.0.3 255.255.255.0
 no ip route-cache
!
interface GigabitEthernet0/1
!
interface GigabitEthernet0/2
!
interface GigabitEthernet0/3
!
interface GigabitEthernet0/4
!
interface GigabitEthernet0/5
!
interface GigabitEthernet0/6
!
interface GigabitEthernet0/7
!
interface GigabitEthernet0/8
!
interface GigabitEthernet0/9
!
interface GigabitEthernet0/10
!
interface GigabitEthernet0/11
!
interface GigabitEthernet0/12
!
interface GigabitEthernet0/13
!
interface GigabitEthernet0/14
!
interface GigabitEthernet0/15
!
interface GigabitEthernet0/16
!
interface GigabitEthernet0/17
!
interface GigabitEthernet0/18
!
interface GigabitEthernet0/19
!
interface GigabitEthernet0/20
!
interface GigabitEthernet0/21
!
interface GigabitEthernet0/22
!
interface GigabitEthernet0/23
!
interface GigabitEthernet0/24
!
interface GigabitEthernet0/25
!
interface GigabitEthernet0/26
!
interface GigabitEthernet0/27
!
interface GigabitEthernet0/28
!
interface GigabitEthernet0/29
!
interface GigabitEthernet0/30
!
interface GigabitEthernet0/31
!
interface GigabitEthernet0/32
!
interface GigabitEthernet0/33
!
interface GigabitEthernet0/34
!
interface GigabitEthernet0/35
!
interface GigabitEthernet0/36
!
interface GigabitEthernet0/37
!
interface GigabitEthernet0/38
!
interface GigabitEthernet0/39
!
interface GigabitEthernet0/40
!
interface GigabitEthernet0/41
!
interface GigabitEthernet0/42
!
interface GigabitEthernet0/43
!
interface GigabitEthernet0/44
!
interface GigabitEthernet0/45
!
interface GigabitEthernet0/46
 description C1921/Gi0/1
 switchport trunk encapsulation dot1q
 switchport mode trunk
 spanning-tree portfast trunk
!
interface GigabitEthernet0/47
!
interface GigabitEthernet0/48
 description To ASR1001/Gi0/0/3
 switchport trunk encapsulation dot1q
 switchport mode trunk
 spanning-tree portfast trunk
!
interface GigabitEthernet1/1
!
interface GigabitEthernet1/2
!
interface GigabitEthernet1/3
!
interface GigabitEthernet1/4
!
interface TenGigabitEthernet1/1
!
interface TenGigabitEthernet1/2
!
interface Vlan1
 no ip address
!
ip forward-protocol nd
!
no ip http server
no ip http secure-server
!
ip route vrf Mgmt-intf 0.0.0.0 0.0.0.0 172.20.0.1
!
!
!
!
snmp-server community public RO
!
!
!
!
line con 0
 privilege level 15
 transport preferred none
 transport output none
line vty 0 4
 privilege level 15
 transport input ssh
line vty 5 15
 privilege level 15
 transport input ssh
!
end
