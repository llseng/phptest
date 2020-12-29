#!/bin/bash
# 文件压缩备份

# 当前文件夹
CURR_PATH=$( cd $(dirname $0); pwd )
# 待备份文件
FILES=()
# 是否清理文件
CLEAN_FILES=0

if [[ 0 -eq ${#FILES[@]} ]]; then
    echo 'FILES is empty'
    exit 0
fi

EXIST_FILES=();

for file in ${FILES[@]}; do
    filepath="${CURR_PATH}/${file}"
    if [[ -f ${filepath} ]]; then
        EXIST_FILES[ ${#EXIST_FILES[@]} ]=${file}
    else
        echo ${filepath}' is not file'
    fi
done

if [[ 0 -eq ${#EXIST_FILES[@]} ]]; then
    echo 'EXIST_FILES is empty'
    exit 0
fi

DATE_TIME=$(date '+%Y%m%d_%H%M%S')

BACKUP_FILE_NAME="${CURR_PATH}/${DATE_TIME}.tgz"
while [[ -f ${BACKUP_FILE_NAME} ]]; do
    BACKUP_FILE_NAME='_'${BACKUP_FILE_NAME}
done

tar -czvf ${BACKUP_FILE_NAME} -C ${CURR_PATH} ${EXIST_FILES[@]} 2>&1
if [[ $? -gt 1 ]]; then
    echo 'tar encounter error'
    exit 1
fi

if [[ 0 -ne ${CLEAN_FILES} ]]; then
    echo 'clean files'
    for file in ${EXIST_FILES[@]}; do
        filepath="${CURR_PATH}/${file}"
        >${filepath}
    done
fi