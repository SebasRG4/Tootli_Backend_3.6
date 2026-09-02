#!/bin/bash
sed -i '' '/listen 80;/,/}/d' ./docker/nginx/default.conf
sed -i '' '/listen 443 ssl;/i\
    listen 80;\
    listen [::]:80;
' ./docker/nginx/default.conf
sed -i '' '/fastcgi_param HTTPS on;/d' ./docker/nginx/default.conf
