FROM "rastasheep/ubuntu-sshd:16.04"
RUN apt -y update && apt -y install sudo
# Start services automatically after installation, just like on a normal PC
RUN echo "exit 0" > /usr/sbin/policy-rc.d
# Create vagrant user
# https://www.vagrantup.com/docs/boxes/base.html#quot-vagrant-quot-user
RUN useradd vagrant --create-home --password $(openssl passwd -1 vagrant) --shell /bin/bash
# https://www.vagrantup.com/docs/boxes/base.html#password-less-sudo
RUN echo "vagrant ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers
# https://www.vagrantup.com/docs/boxes/base.html#ssh-tweaks
RUN echo "UseDNS no" >> /etc/ssh/sshd_config