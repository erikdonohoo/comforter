#!/usr/bin/env bash
apt-get update

# install nvm
curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.29.0/install.sh | bash
. /root/.bashrc
nvm install stable
nvm use stable
nvm alias default stable

# setup express api
npm install -g forever
cd /vagrant/api/
npm install
forever start app.js
