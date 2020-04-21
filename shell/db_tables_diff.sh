#!/bin/bash
# 检查数据库表差异
# @Author: llseng
# @Date:   2020-04-14 17:31:25
# @Last Modified by:   llseng
# @Last Modified time: 2020-04-14 18:51:52
# $1 结构文件
# $2 对比结构文件

#需要两个参数
if [ $# -lt 2 ]; then
    echo "缺少参数"
    exit 1
fi
#非文件或不可读
if [ ! -f $1 -o ! -r $1 ]; then
    echo "$1 不存在或不可读"
    exit 2
fi
#非文件或不可读
if [ ! -f $2 -o ! -r $2 ]; then
    echo "$2 不存在或不可读"
    exit 3
fi

TABLES_1=$(grep -oP 'CREATE TABLE \`\w+\`' $1 | awk '{print $3}' | grep -oP '\w+' )
TABLES_2=$(grep -oP 'CREATE TABLE \`\w+\`' $2 | awk '{print $3}' | grep -oP '\w+' )
#差异集
DIFF_TABLES=()

for i in $TABLES_1; do

    table_exist=1

    for j in $TABLES_2; do

        if [ $i = $j ]; then
            table_exist=0
            break
        fi

    done

    if [ $table_exist -gt 0 ]; then
        DIFF_TABLES[${#DIFF_TABLES[@]}]=$i
    fi
done

echo '差异数: '${#DIFF_TABLES[@]}
echo '差异集: '${DIFF_TABLES[@]}