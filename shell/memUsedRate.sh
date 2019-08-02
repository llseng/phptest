#!/bin/bash
#内存信息文件
meminfo="/proc/meminfo"
#总内存
totalMem=`grep 'MemTotal' $meminfo | awk '{print $2}'`
#闲置内存
freeMem=`grep 'MemFree' $meminfo | awk '{print $2}'`
#可用内存
availableMem=`grep 'MemAvailable' $meminfo | awk '{print $2}'`
#使用内存
usedMem=`expr $totalMem - $availableMem`
#内存使用率
memUsedRate=`awk 'BEGIN{printf "%.0f",'$usedMem'/'$totalMem'*100}'`
#是否超过80%
if [ $memUsedRate -gt 80 ]; then
    echo "memUsedRate : ${memUsedRate}%"
fi