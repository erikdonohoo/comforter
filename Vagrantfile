# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
    # The most common configuration options are documented and commented below.
    # For a complete reference, please see the online documentation at
    # https://docs.vagrantup.com.

    # Every Vagrant development environment requires a box. You can search for
    # boxes at https://atlas.hashicorp.com/search.
    config.vm.define "api" do |api|
        api.ssh.forward_agent = true
        api.vm.box = "ubuntu/trusty64"
        api.vm.network "private_network", ip: "192.168.33.50"
        api.vm.provision :shell, path: "vagrant/web.sh"
    end

    config.vm.define "db" do |db|
        db.ssh.forward_agent = true
        db.vm.box = "ubuntu/trusty64"
        db.vm.network "private_network", ip: "192.168.33.51"
        db.vm.provision :shell, path: "vagrant/db.sh"
    end

end
