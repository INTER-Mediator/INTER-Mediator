FROM ubuntu:14.04

RUN apt-get update && apt-get -y install ruby git
RUN git clone https://github.com/matsuo/INTER-Mediator && cd INTER-Mediator && git checkout update-dockerfile && cd
RUN gem install itamae --no-ri --no-rdoc && itamae local INTER-Mediator/dist-docs/vm-for-trial/recipe.rb
