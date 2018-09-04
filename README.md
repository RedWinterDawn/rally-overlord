# Cisco Auto-Generation

This tool creates a set of site configurations for multiple devices by using a template and YAML input data.

The input configuration files are in sites/ and config/

A set of output files are generated which can then be loaded onto a cisco device using:

    configure replace http://opscontroller.ftw.jiveip.net/cisco/asr1k.pvu-1b-confg list force ignorecase revert trigger error timer 1

To generate the output, run:

    sh generate.sh

You can view the changes that are pending using:

    show archive config differences http://opscontroller.ftw.jiveip.net/cisco/asr1k.pvu-1b-confg

Once a device is provisoned, you can use overlord's commands:

    overlord-diff (list changes)
    overlord-apply (apply changes)
    overlord-confirm (confirm applied changes)
    overlord-revert (revert applied changes)

# Jive Configs (V1)


## C1900

Gi0/0: Trunk to the SG (management) switch.

  Vlan 2: Network Infrastructure
  Vlan 3: IPMI management


Gi0/0: Trunk to C3550

  Vlan 10: Public Internet

## ASR1K

Stge 1 is applying a "seed config" which sets it up to be part of the overlord network, but not apply any V5 specific network config (all external interfaces are down, no BGP sessiosn configured, etc).  this can be seen as a "recovery" mode.  It also sets the correct licencing image.

Once seeded, the config should be saved and router reloaded.  Once back, it should be checked to ensure that it can reach overlord using "overlord-ping".


## Setup

  - Copy seed config, or dhcp
  - create crypto keys for SSH
  - apply overlord config

TODO:

  - inbound traffic ACLs
  - management ACLs
  - SNMP traps
  - call home


