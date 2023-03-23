FROM amazonlinux:2

RUN yum -y upgrade

RUN yum -y install wget httpd24 mod24_ssl git zip gcc glibc glibc-common gd gd-devel make

# PHP 8.1
RUN amazon-linux-extras enable php8.1
RUN yum clean metadata
RUN yum -y install php php-{pear,pecel-xdebug,process,cgi,common,curl,mbstring,gd,mysqlnd,gettext,bcmath,json,xml,fpm,intl,zip,imap,xdebug,devel,pdo}
RUN pecl install xdebug

RUN cd /root && wget https://getcomposer.org/installer && php installer --install-dir=/usr/local/bin --filename=composer && rm -f installer
RUN mkdir -p /var/run/php/cache && chown apache:apache /var/run/php/cache

RUN sed -i 's/zend.assertions = -1/zend.assertions = 1/g' /etc/php.ini
COPY xdebug.ini /etc/php.d/40-xdebug.ini

RUN mkdir -p /www/web && rmdir /var/www/html && ln -s /www/web/public /var/www/html
RUN chown apache:apache -R /www

COPY httpd.conf /etc/httpd/conf/

COPY docker-entrypoint.sh /usr/local/bin/

ENTRYPOINT [ "bash", "docker-entrypoint.sh" ]
