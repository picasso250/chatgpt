sudo zcat /var/log/nginx/access.log*gz | /usr/bin/goaccess - -o /var/www/html/report_all.html
