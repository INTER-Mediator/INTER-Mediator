#!/bin/bash
#
# setup shell script for Alpine Linux 3.13 or 3.10
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

apk add --no-cache curl
curl -L https://github.com/itamae-kitchen/mitamae/releases/latest/download/mitamae-x86_64-linux.tar.gz | tar xvz
ls -al
./mitamae-x86_64-linux local ./recipe.rb*