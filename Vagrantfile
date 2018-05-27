require 'json'
require 'yaml'

VAGRANTFILE_API_VERSION ||= "2"
confDir = $confDir ||= File.expand_path("vagrant/homestead", File.dirname(__FILE__))

homesteadYamlPath = "vagrant/bootstrap.yaml"
afterScriptPath = "vagrant/after.sh"
aliasesPath = "vagrant/aliases"

require File.expand_path(confDir + '/scripts/homestead.rb')

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    if File.exists? aliasesPath then
        config.vm.provision "file", source: aliasesPath, destination: "~/.bash_aliases"
    end

    if File.exists? homesteadYamlPath then
        settings = YAML::load(File.read(homesteadYamlPath))
        Homestead.configure(config, settings)
    end

    if File.exists? afterScriptPath then
        config.vm.provision "shell", path: afterScriptPath
    end

    config.vm.provider :virtualbox do |vb|
        vb_name = settings["name"] ||= "homestead-7"
        vb_name = vb_name + "_" + File.basename(Dir.pwd)
        vb.name = vb_name
    end
end