#!/usr/bin/env bash
apt-get update

# install nvm
curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.29.0/install.sh | bash
echo "source /home/vagrant/.nvm/nvm.sh" >> /home/vagrant/.profile
source /home/vagrant/.profile
source /home/vagrant/.bashrc
nvm install stable
nvm use stable
nvm alias default stable

# setup and start
npm install -g forever
cd /vagrant
npm install
forever start bin/www
