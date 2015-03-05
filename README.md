# TeacherLine Branch: TL28
# Branched off of MOODLE_28_STABLE


https://docs.moodle.org/dev/Git_for_developers

To keep stable branches up to date:

#!/bin/sh
git fetch upstream
for BRANCH in MOODLE_{19..28}_STABLE master; do
    git push origin refs/remotes/upstream/$BRANCH:$BRANCH
done



# Spinning Up Ubuntu in AWS

## Mounting Extra EBS

http://docs.aws.amazon.com/AWSEC2/latest/UserGuide/ebs-using-volumes.html

1. Determine the EBS volume.

    $ lsblk


2. Check the Filesystem, create one if necessary.

    $ sudo file -s /dev/xvdb
    /dev/xvdb: data

    $ sudo mkfs -t ext4 /dev/xvdb


3. Mount the device.

    $ sudo mkdir /mnt/sdb
    $ sudo mount /dev/xvdb /mnt/sdb


4. Add the devide to `fstab`.

    $ vim /etc/fstab
    #device_name  mount_point  file_system_type  fs_mntops  fs_freq  fs_passno
    /dev/xvdb  /mnt/sdb  ext4  defaults,nofail,nobootwait  0  2



## Installing Software

List of all packages installed:

git
apache2
php5
php5-fpm
php5-pgsql
php5-curl
php5-gd
php5-xmlrpc
php5-intl
postgresql-client



List of all command lines run:


Configuring FPM, Apache2:

https://serversforhackers.com/apache-proxy-fcgi/

Modified default apache site to serve FPM Moodle 2.8.


AWS VPC: Modified the RDS Security Group to allow inbound traffic
(172.30.0.0/16).
