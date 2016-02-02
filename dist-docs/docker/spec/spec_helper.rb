require "docker"
require "serverspec"

set :backend, :docker
set :os, family: 'ubuntu', arch: 'x86_64'
set :docker_url, ENV["DOCKER_HOST"]
set :docker_image, "ubuntu"
