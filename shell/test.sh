#!/bin/sh

curPath=$(cd $(dirname $0); pwd)
. ${curPath}/./funcs.sh

Log "test" "logfucntion" "exec"
Log ${curPath}
Log ${CurPath}

ArraySearch '1' '1 0 1 1 1 2 3 4 5 6'
echo 'ArraySearch: '$?

