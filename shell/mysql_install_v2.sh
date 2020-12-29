#!/bin/bash
# @Author: llseng
# @Date:   2020-12-15 15:18:27
# @Last Modified by:   llseng
# @Last Modified time: 2020-12-15 16:49:54

COMMON_RPM='mysql-community-common.rpm'
LIBS_RPM='mysql-community-libs.rpm'
CLIENT_RPM='mysql-community-client.rpm'
SERVER_RPM='mysql-community-server.rpm'
# 腾讯云服务器,使用内网流量不占用公网流量 
# DOMAIN_NAME='mirrors.tencentyun.com'
# 外网域名
DOMAIN_NAME='mirrors.cloud.tencent.com'


# 下载镜像文件
wget -O "${COMMON_RPM}" "https://${DOMAIN_NAME}/mysql/yum/mysql-5.7-community-el7-x86_64/mysql-community-common-5.7.32-1.el7.x86_64.rpm" \
&& wget -O "${LIBS_RPM}" "https://${DOMAIN_NAME}/mysql/yum/mysql-5.7-community-el7-x86_64/mysql-community-libs-5.7.32-1.el7.x86_64.rpm" \
&& wget -O "${CLIENT_RPM}" "https://${DOMAIN_NAME}/mysql/yum/mysql-5.7-community-el7-x86_64/mysql-community-client-5.7.32-1.el7.x86_64.rpm" \
&& wget -O "${SERVER_RPM}" "https://${DOMAIN_NAME}/mysql/yum/mysql-5.7-community-el7-x86_64/mysql-community-server-5.7.32-1.el7.x86_64.rpm" \
&& yum -y remove mariadb* \
&& yum -y remove mysql* \
&& yum -y install libaio numactl \
&& rpm -ivh "${COMMON_RPM}" "${LIBS_RPM}" "${CLIENT_RPM}" "${SERVER_RPM}" 
