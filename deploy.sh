#!/bin/bash

set -e

# 定义源文件目录和目标机器信息
source_dir="html"
target_host="xct7gm@104.225.145.121"
target_dir="/var/www/html/"
tar_file="deploy.tar.gz"
set -x

# before deploy
/C/xampp/php/php.exe script/html.php

# 创建临时目录并将所有文件压缩成tar文件
tar -czf "$tar_file" "$source_dir"

# 将tar文件传输到目标机器的/tmp目录
scp -P 27417 "$tar_file" "$target_host:/tmp"

# 在目标机器上解压tar文件并移动到目标目录
ssh -p 27417 "$target_host" "cd $target_dir.. && tar -xzf /tmp/$tar_file && chgrp www-data $target_dir && rm /tmp/$tar_file"

# 清理临时文件和目录
rm -rf "$tar_file"