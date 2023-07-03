#!/bin/bash

# 定义源文件目录和目标机器信息
source_dir="."
target_host="xct7gm@104.225.145.121"
target_dir="/var/www/html/"

# 复制 PHP 文件和 HTML 文件
scp -P 27417 -r "$source_dir"/*.php "$target_host":"$target_dir"
scp -P 27417 -r "$source_dir"/*.html "$target_host":"$target_dir"

# 复制 CSS 和 JS 文件夹中的文件
scp -P 27417 -r "$source_dir"/css "$target_host":"$target_dir"
scp -P 27417 -r "$source_dir"/js "$target_host":"$target_dir"