- setup server
- setup apache
to install mcrypt:
	# Install prerequisites
	sudo apt-get install php-dev libmcrypt-dev gcc make autoconf libc-dev pkg-config

	# Compile mcrypt extension
	sudo pecl install mcrypt-1.0.1
	# Just press enter when it asks about libmcrypt prefix

	# Enable extension for apache
	echo "extension=mcrypt.so" | sudo tee -a /etc/php/7.2/apache2/conf.d/mcrypt.ini

	# Restart apache
	sudo service apache2 restart


sudo apt-get update
sudo apt-get install tasksel
sudo tasksel install lamp-server
- setup mysql
- migrate files
- setup wordpress

- fix file/directory permissions, if you broke them
sudo find . -type d -exec chmod 775 {} \;
sudo find . -type f -exec chmod 664 {} \;
sudo chown -R :www-data /var/www/html/wp-content/themes
sudo chown -R :www-data /var/www/html/wp-content/plugins








~~[jk we're not doing this; too much trouble]
setup js stuff
	sudo curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.33.11/install.sh | bash
	sudo nvm install node
		sudo nvm use node
	sudo apt install npm
	sudo npm install -g npm@latest
	sudo npm install --global gulp-cli
	sudo npm install -g browser-sync
	cd wp-content/themes/understrap
	sudo npm install~~