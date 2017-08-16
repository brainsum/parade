#!/bin/sh
set -e

DOCKER_COMPOSE_VERSION=1.15.0

# Make sure we are in the right folder.
cd "${TRAVIS_BUILD_DIR}"
# Install docker-compose.
sudo rm /usr/local/bin/docker-compose
curl -L https://github.com/docker/compose/releases/download/${DOCKER_COMPOSE_VERSION}/docker-compose-`uname -s`-`uname -m` > docker-compose
chmod +x docker-compose
sudo mv docker-compose /usr/local/bin
# Try speeding up composer build a bit.
composer global require hirak/prestissimo
# Install a drupal-composer project.
composer create-project drupal-composer/drupal-project:8.x-dev docker --stability dev --no-interaction
cd docker
# Copy the scripts and docker-compose.yml
cp ../ci-scripts/* .
# Fix permissions.
sudo chown 82:82 . -R
#sudo chmod +x docker--install.sh docker--phpcs-practice.sh
# Start docker.
docker-compose up -d
# Execute install script.
docker-compose exec --user 82 php sh docker--install.sh
