FROM ubuntu:latest

RUN apt-get update && apt-get install -y openssh-server

RUN mkdir /var/run/sshd

COPY flag.txt /home/cather/flag.txt

RUN useradd -m -s /bin/bash cather && echo 'cather:geneva' | chpasswd

RUN sed -i 's/#PasswordAuthentication yes/PasswordAuthentication yes/' /etc/ssh/sshd_config

RUN sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config

EXPOSE 22

CMD ["/usr/sbin/sshd", "-D"]
