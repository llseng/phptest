#!/bin/bash
# @Author: llseng
# @Date:   2020-04-23 16:02:00
# @Last Modified by:   llseng
# @Last Modified time: 2020-11-18 11:33:24
# 
rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm \
&& rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm \
&& yum -y install php72w \
&& yum -y install php72w-cli \
php72w-common \
php72w-devel \
php72w-bcmath \
php72w-fpm \
php72w-gd \
php72w-imap \
php72w-ldap \
php72w-mbstring \
php72w-mysqlnd \
php72w-odbc \
php72w-opcache \
php72w-pdo \
php72w-pear \
php72w-process \
php72w-xmlrpc \
&& php -v \
&& sed -i 's/;date.timezone =/date.timezone = PRC/' /etc/php.ini \
&& curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /usr/local/bin/composer