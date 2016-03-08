#! /bin/bash

# 创建日志的必需目录结构

cur_pwd=`pwd`

app_path=`dirname $cur_pwd`
app_name=`basename $app_path`
app_root_path=`dirname $app_path`
app_logs=$app_root_path'/logs/'$app_name

mkdir -p $app_logs

if [ ! $? -eq 0 ]; then
    echo "mkdir logs fail"
    exit 1
fi

mkdir -p $app_logs'/bqlog' $app_logs'/oplog' $app_logs'/log'
exit 0
