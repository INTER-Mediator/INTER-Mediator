FROM ubuntu:20.04

ENV TZ=Asia/Tokyo
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
 && apt-get install -y \
    init \
    ruby \
    git \
 && rm -rf /var/lib/apt/lists/*

CMD [ "/sbin/init" ]