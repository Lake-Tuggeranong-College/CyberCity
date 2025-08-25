FROM ubuntu:latest

RUN apt-get update && apt-get install -y openssh-server sudo

RUN mkdir /var/run/sshd

COPY program /home/connor/program

RUN chmod 000 /home/connor/program

RUN useradd -m -s /bin/bash connor && echo 'connor:Password01' | chpasswd

RUN usermod -aG sudo connor

# Configure SSH
RUN sed -i 's/#PasswordAuthentication yes/PasswordAuthentication yes/' /etc/ssh/sshd_config
RUN sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config

EXPOSE 22

CMD ["/usr/sbin/sshd", "-D"]
