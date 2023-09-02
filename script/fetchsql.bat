
@echo off
setlocal enabledelayedexpansion

rem 获取当前日期
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do (
    set "datetime=%%a"
)

rem 格式化日期为YYYY-MM-DD
set "datestamp=!datetime:~0,4!-!datetime:~4,2!-!datetime:~6,2!"

rem 构建文件名
set "filename=D:\data\chatgptdata\!datestamp!_chatgpt.sql"

rem 使用SCP命令
scp -P 27417 xct7gm@104.225.145.121:/var/www/backup/!datestamp!_chatgpt.sql "!filename!"

endlocal
