#!/bin/bash

# @Author: llseng
# @Date:   2020-09-23 17:44:12
# @Last Modified by:   llseng
# @Last Modified time: 2020-09-25 11:15:27

# [ set -u ]遇到不存在的变量终止脚本的执行
set -o nounset

# [ set -e ] 遇到执行出错终止脚本的执行( 函数返回非0时会终止脚本 )
# set -o errexit

# 当前脚本地址
CurPath=$(cd $(dirname $0); pwd)

# 开启调试
OnDebug() { set -x; }

# 关闭调试
OffDebug() { set +x; }

# 开启使用未定义变量终止
OnUnset() { set -u; }

# 关闭使用未定义变量终止
OffUnset() { set +u; }

# 开启出错时终止
OnErrexit() { set -e; }

# 关闭出错时终止
OffErrexit() { set +e; }

# 打印日志
# $@ 信息
Log() {
    local prefix="[$(date +%Y-%m-%d\ %H:%M:%S)]: "
    echo "${prefix} $@" >&2
}

# 数组中搜索指定值
# $1 指定值
# $2 数组
# $return 0 不存在 || 首个相应(键名+1)
ArraySearch() {
    local key=0
    local curKey=1

    for val in $2; do
        if [[ $1 = $val ]]; then
            key=${curKey}
            break
        fi

        let curKey++
    done

    return $key
}

# 数组中是否存在指定值
# $1 指定值
# $2 数组
# $return 0 不存在 || 数量
InArray() {
    local num=0
    for val in $2; do
        if [[ $1 = $val ]]; then
            let num++
        fi
    done

    return $num
}

# $1 日志文件
# $@
WriteLog() {
    if [[ $# -lt 2 ]]; then
        Log 'parameter error'
        return 100 
    fi  

    log_file=$1

    if [[ ! -f $log_file ]]; then
        Log $log_file' is not file'
        return 1
    fi  

    if [[ ! -w $log_file ]]; then
        Log $log_file' is not writable'
        return 2
    fi  

    msg=()
    key=0
    for val in $@; do
        if [[ $key -gt 0 ]]; then
            msg[ ${#msg[@]} ]=$val
        fi  
        let key++
    done

    Log ${msg[@]} >> $log_file 2>&1

    return 0
}