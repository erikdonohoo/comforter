#!/bin/bash

# Fail on any command failure
set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

# Get the php version, but ignore patch/release. https://unix.stackexchange.com/a/471594/314067
# Save output of a command to a variable https://stackoverflow.com/a/20688600/1248889
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;")
if [[ ! $PHP_VERSION == '7.4' ]]; then
	>&2 echo "ERROR: You are running a non-7.4 version of php ($PHP_VERSION), so I don't know how to install pear/pecl for you"
	exit 1
fi

# What type of machine are we on? https://stackoverflow.com/a/3466183/1248889
case "$(uname -s)" in
	Darwin*)    machine='Mac';;
	*)          echo "Only run this on the host machine"; exit 1;;
esac

if [ ! -d /usr/local/lib/php/pecl ]; then
	echo 'Making pecl directory...'
	sudo mkdir /usr/local/lib/php/pecl
fi

# Dude, where's my php.ini? https://stackoverflow.com/a/24342463/1248889
#PHP_LOCATION=$(php -r 'print php_ini_loaded_file();')
# What go-pear does: how to find php ini configuration https://stackoverflow.com/a/2750615/1248889
PHP_LOCATION=$(php -r "echo get_cfg_var('cfg_file_path');")

# Is my variable an existing file? https://stackoverflow.com/a/21164441/1248889
if [[ ! -f $PHP_LOCATION ]]; then
	echo 'Copying php.ini.default to php.ini...'

	# Where is the php ini configuration folder? https://stackoverflow.com/a/8684638/1248889
	# How to parse this input and get the unmatched part of a searched line https://stackoverflow.com/a/13733258/1248889
	# How to escape parenthesis with sed? https://unix.stackexchange.com/a/64197/314067
	# Space character in sed? https://stackoverflow.com/q/15509536/1248889
	PHP_DIR=$(php --ini 2>/dev/null | sed -n 's/Configuration File [(]php.ini[)] Path:[[:space:]]*//gp')

	# What if I dodn't have a php.ini https://stackoverflow.com/a/40516071/1248889
	sudo cp "$PHP_DIR/php.ini.default" "$PHP_DIR/php.ini"
fi

# See if we have php-config
if ! hash php-config &>/dev/null; then
	>&2 echo "ERROR: You don't have php-config, and I don't know how to install it on your machine."
	exit 1
fi

# See if we have xdebug.so
XDEBUG_PATH=$(php-config --extension-dir)/xdebug.so
if [[ ! -f $XDEBUG_PATH ]]; then
	# We need to go install it via pecl (php package manager)
	# See if we have pecl, and if not, install it
	if ! hash pecl &>/dev/null && ! hash pecl7 &>/dev/null; then
		echo "Installing pear/pecl..."

		# Use curl (instead of unavailable wget) to save pear's install script to a directory https://stackoverflow.com/a/16363115/1248889
		curl -o /tmp/go-pear.phar http://pear.php.net/go-pear.phar

		# Install pear on Mac OS X https://pear.php.net/manual/en/installation.getting.php#installation.getting.osx
		# Install pear/pecl without prompts https://stackoverflow.com/a/7245893/1248889
		# 1: Installation base ($prefix)
		# 4: Binaries directory
		# Y: alter php.ini to include the pear php directory
		sudo expect -c '
		spawn php -d detect_unicode=0 /tmp/go-pear.phar
		send "1\r"
		send "/usr/local/pear\r"
		send "4\r"
		send "/usr/local/bin\r"
		send "\r"
		send "Y\r"
		send "\r"
		expect eof
		'
	fi

	# Get pecl(7) command
	PECL_COMMAND=pecl
	if ! hash pecl &>/dev/null; then
		if ! hash pecl7 &>/dev/null; then
			>&2 echo 'ERROR: pecl(7) should be installed by now'
			exit 1
		fi

		# If it's pecl7, alias it to pecl for this script https://stackoverflow.com/a/24054245/1248889
		PECL_COMMAND=pecl7
	fi

	# Do we have Xdebug? https://superuser.com/a/1150477/385940
	if $PECL_COMMAND list | grep xdebug &>/dev/null; then
		>&2 echo "ERROR: Can't find the xdebug.so extension, but xdebug is already installed?"
		exit 1
	fi

	echo 'Installing Xdebug...'

	sudo $PECL_COMMAND install xdebug
fi

# Bash booleans https://stackoverflow.com/q/2953646/1248889
NEEDS_XDEBUG_CONF=false

# Get the location for additional ini files
PHP_INI=$(php -r "echo get_cfg_var('cfg_file_path');")
PHP_CONF_D=$(php --ini | sed -n 's/Scan for additional .ini files in:[[:space:]]*//gp')
if [[ $PHP_CONF_D != "(none)" ]]; then
	# If the directory exists, Add xdebug.ini there if there isn't already one there
	XDEBUG_INI=$PHP_CONF_D/xdebug.ini
	if [[ ! -f $XDEBUG_INI ]]; then
		NEEDS_XDEBUG_CONF=true
	fi
else
	echo "Can't find PHP module configuration directory"
	exit 1
fi

if cat $PHP_INI | grep 'zend\.assertions = -1' &>/dev/null; then
	echo 'Enabling php assertions...'

	# Need to enable assertions
	sudo sed -i '' 's/zend.assertions = -1/zend.assertions = 1/g' $PHP_INI
fi


if [[ $NEEDS_XDEBUG_CONF == true ]]; then
	echo "Configuring PHP to use Xdebug at $XDEBUG_INI..."

	# sudo cat redirect doesn't work, use tee instead https://stackoverflow.com/a/82278/1248889
	# Get php extension-dir https://unix.stackexchange.com/a/278983/314067
	# Here-doc with indentation https://stackoverflow.com/a/2954835/1248889
	sudo tee $XDEBUG_INI <<- EOF
		[xdebug]
		zend_extension="$(php-config --extension-dir)/xdebug.so"
		xdebug.remote_enable = 1
		xdebug.remote_autostart = 1
		xdebug.profiler_enable_trigger = 1
		xdebug.remote_connect_back = 1
	EOF

	sudo apachectl restart
fi

if ! ifconfig lo0 | fgrep 'inet 10.254.254.254' >/dev/null; then
	echo 'Establishing loopback ip...'
	sudo ifconfig lo0 alias 10.254.254.254
fi
