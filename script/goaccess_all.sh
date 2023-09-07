sudo zcat /var/log/nginx/access.log*gz | /usr/bin/goaccess - -o /var/www/log/report_all.html
