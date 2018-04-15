FROM ubuntu:14.04
 
RUN apt-get update && apt-get -y install ruby2.0 git && git clone https://github.com/INTER-Mediator/INTER-Mediator && cd /INTER-Mediator && git checkout 5.x && gem2.0 install itamae --no-doc && itamae local dist-docs/vm-for-trial/recipe.rb