FROM ubuntu:14.04

RUN apt-get update && apt-get -y install ruby2.0 git
RUN git clone https://github.com/INTER-Mediator/INTER-Mediator && gem2.0 install itamae --no-ri --no-rdoc && itamae local INTER-Mediator/dist-docs/vm-for-trial/recipe.rb
