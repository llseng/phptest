#!/bin/sh

# 当前目录
CURPATH=$( cd $( dirname $0 ); pwd )
# 引入函数文件
. $CURPATH'/./funcs.sh'
# 日志文件
logFile=$CURPATH'/logs/'$(date +%Y-%m-%d)'.log'
touch $logFile

list=(1 2 3 4 5 6 7)
msg=()
key=0
for val in ${list[@]}; do
    echo $val
    if [[ $key -gt 0 ]]; then
        msg[ ${#msg[@]} ]=$val
    fi  
    let key++
done

echo ${msg[@]}
