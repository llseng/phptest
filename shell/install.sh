#!/bin/bash

echo "---------start---------"
#需要安装的包
packages=(nginx httpd ftp vsftpd)
#包版本
pversions=()
#未安装列表
notInstalled=()
#显示版本号
getVersion()
{
    if [ -n $1 ];then
        echo $1 | awk -F '-' '{print $1":"$2}'
    fi
}

#遍历
for p in ${packages[@]}; do
    #检查安装
    installed=`rpm -qa ${p}`
    #未安装
    if [ -z $installed ]; then
        notInstalled[${#notInstalled[@]}]=$p
        continue
    fi

    #安装版本
    getVersion $installed
done
#有未安装的包
if [ $notInstalled ]; then

    #未安装包
    echo "not installed : "${notInstalled[@]}
    #安装
    installCmd="yum -y install ${notInstalled[@]}"
    echo $installCmd
    
    $installCmd
fi

for x in ${notInstalled[@]};do

    #检查安装
    installed=`rpm -qa ${p}`
    #安装版本
    getVersion $installed
done

echo "----------end----------"