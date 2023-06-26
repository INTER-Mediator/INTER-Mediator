#!/bin/bash
#
# setup shell script for CentOS Linux 7.8, Ubuntu Server 18.04 and Alpine Linux 3.10
#
# This file can get from the URL below.
# https://raw.githubusercontent.com/INTER-Mediator/INTER-Mediator/master/dist-docs/vm-for-trial/deploy.sh
#
# How to test using Serverspec 2 after running this file on the guest of VM:
#
# - Install Ruby on the host of VM (You don't need installing Ruby on macOS usually)
# - Install Serverspec 2 on the host of VM (ex. "sudo gem install serverspec" on macOS)
#   See detail: https://serverspec.org/
# - Change directory to "vm-for-trial" directory on the host of VM
# - Run "rake spec" on the host of VM
#

if [ -e "/usr/bin/yum" ]; then
    yum install -y tar
fi
if [ `uname -m` = "arm64" ]; then
    curl -L https://github.com/itamae-kitchen/mitamae/releases/download/v1.12.0/mitamae-aarch64-linux.tar.gz | tar xvz
    ./mitamae-aarch64-linux local ./recipe.rb --log-level=debug
else
    curl -L https://github.com/itamae-kitchen/mitamae/releases/download/v1.12.0/mitamae-x86_64-linux.tar.gz | tar xvz
    ./mitamae-x86_64-linux local ./recipe.rb --log-level=debug
fi