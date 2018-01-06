FROM ubuntu:16.04

RUN apt-get update && apt-get -y install ruby git && git clone https://github.com/matsuo/INTER-Mediator && cd /INTER-Mediator && git checkout ubuntu1604 && gem install itamae --no-doc && itamae local dist-docs/vm-for-trial/recipe.rb
