FROM ubuntu:latest

RUN apt-get update && apt-get install -y openssh-server

RUN mkdir /var/run/sshd

RUN useradd -m -s /bin/bash overseer && echo 'overseer:Password01' | chpasswd

RUN sed -i 's/#PasswordAuthentication yes/PasswordAuthentication yes/' /etc/ssh/sshd_config

RUN sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config

COPY executor.sh /root/executor.sh
COPY suidExecutor.cpp /root/suidExecutor.cpp

EXPOSE 22

CMD ["/usr/sbin/sshd", "-D"]