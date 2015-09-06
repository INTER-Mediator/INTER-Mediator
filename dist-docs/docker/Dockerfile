FROM ubuntu:14.04

RUN apt-get update
RUN apt-get -y install ruby
RUN gem install itamae --no-ri --no-rdoc
RUN apt-get -y install git
RUN git clone https://github.com/matsuo/INTER-Mediator
RUN cd INTER-Mediator && git checkout update-dockerfile && cd
RUN itamae local INTER-Mediator/dist-docs/vm-for-trial/recipe.rb
