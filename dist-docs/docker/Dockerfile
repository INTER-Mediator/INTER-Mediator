FROM ubuntu

RUN apt-get update
RUN apt-get -y install ruby
RUN gem install itamae --no-ri --no-rdoc
RUN apt-get -y install git
RUN git clone https://github.com/INTER-Mediator/INTER-Mediator
#RUN itamae local INTER-Mediator/dist-docs/vm-for-trial/recipe.rb