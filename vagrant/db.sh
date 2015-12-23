#!/usr/bin/env bash
sudo apt-get update

# setup mongodb
sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10
echo "deb http://repo.mongodb.org/apt/ubuntu trusty/mongodb-org/3.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.0.list
sudo apt-get update
sudo apt-get install -y mongodb-org

# create db and user
mongo_db=`python /vagrant/vagrant/json_reader.py /vagrant/settings.json mongo.database`
mongo_user=`python /vagrant/vagrant/json_reader.py /vagrant/settings.json mongo.username`
mongo_pass=`python /vagrant/vagrant/json_reader.py /vagrant/settings.json mongo.password`
echo "db.createCollection('apps');db.createCollection('coverage');db.createUser({user:'$mongo_user', pwd:'$mongo_pass', roles:[]});" | mongo $mongo_db
echo "Created database $mongo_db with user $mongo_user:$mongo_pass"
sudo cp /vagrant/vagrant/mongod.conf /etc/mongod.conf
sudo service mongod restart
