#!/bin/bash
#
#当前日期
nowdate=`date +%Y%m%d`
#当前时间
nowtime=`date +%H%M%S`

#打印时间
getDatatime()
{
	datatime=`date +%Y-%m-%d.%H:%M:%S`
	echo "DATATIME $datatime"
}

getDatatime
echo "========== START =========="

#数据库用户
dbuser="root"
#数据库列表
dbnames=("test")
#密码
dbpwd="123456"

#备份目录
bcakupPath="sqlBackup"

#备份目录 
if [ ! -d $bcakupPath ]; then
	`mkdir $bcakupPath`
	`chmod 755 $bcakupPath`
fi

#所有数据库
for dbname in ${dbnames[@]}; do

	getDatatime
	echo "----------- ${dbname} start"

	#备份文件
	backupFile="${bcakupPath}/${nowdate}-${nowtime}_${dbname}.sql"
	#压缩文件
	tarFile="${bcakupPath}/${nowdate}-${nowtime}_${dbname}_sql.tgz"
	#备份命令 --skip-tz-utc禁止timestmp时区装换
	`mysqldump -u${dbuser} -p${dbpwd} --skip-tz-utc ${dbname} > ${backupFile}`

	getDatatime
	echo $backupFile

	#备份成功
	if [ -f $backupFile ]; then
		echo "Bcakup SUCCESS"
		#备份文件压缩
		`tar -czvf $tarFile -C $bcakupPath ${nowdate}-${nowtime}_${dbname}.sql`

		#压缩成功
		if [ -f $tarFile ]; then
			echo "tar SUCCESS"

			#删除备份
			`rm -f $backupFile`
			echo "rm -f $backupFile"

			#获取以往压缩
			beforeTarFiles=`ls $bcakupPath | grep "_${dbname}_sql.tgz" | grep -v "${nowdate}-${nowtime}"`
			echo "ls $bcakupPath | grep ""_${dbname}_sql.tgz"" | grep -v ""${nowdate}-${nowtime}"
			echo $beforeTarFiles

			#删除以往备份
			for x in ${beforeTarFiles[@]}; do
				`rm -f $bcakupPath'/'$x`
				echo "rm -f ${bcakupPath}/${x}"
			done

			echo "delete before tarFiles"
		else
			echo "tar FAIL"
		fi

		#获取以往备份
		beforeBackupFiles=`ls $bcakupPath | grep "_${dbname}.sql" | grep -v "${nowdate}-${nowtime}"`
		echo "ls $bcakupPath | grep ""_${dbname}.sql"" | grep -v ""${nowdate}-${nowtime}"
		echo $beforeBackupFiles

		#删除以往备份
		for x in ${beforeBackupFiles[@]}; do
			`rm -f $bcakupPath'/'$x`
			echo "rm -f ${bcakupPath}/${x}"
		done
		
		echo "delete before BackupFiles"
	else
		echo "Bcakup FAIL"
	fi

	getDatatime
	echo "----------- ${dbname} end"

done

getDatatime
echo "=========== END ==========="