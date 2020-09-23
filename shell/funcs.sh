#!/bin/bash

# @Author: llseng
# @Date:   2020-09-23 17:44:12
# @Last Modified by:   llseng
# @Last Modified time: 2020-09-23 19:16:52

# [ set -u ]遇到不存在的变量终止脚本的执行
set -o nounset

# [ set -e ] 遇到执行出错终止脚本的执行( 函数返回非0时会终止脚本 )
# set -o errexit

# 当前脚本地址
CurPath=$(cd $(dirname $0); pwd)

# 开启使用未定义变量终止
OnUnset() {
    set -u
}

# 关闭使用未定义变量终止
OffUnset() {
    set +u
}

# 开启出错时终止
OnErrexit() {
    set -e
}

# 关闭出错时终止
OffErrexit() {
    set +e
}

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