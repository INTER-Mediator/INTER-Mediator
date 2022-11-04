FROM ubuntu:18.04

RUN apt-get update \
 && apt-get install -y \
    init \
    ruby \
    git \
 && rm -rf /var/lib/apt/lists/*

CMD [ "/sbin/init" ]