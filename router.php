server {
  listen 8080;
  listen [::]:8080;
  server_name yourdomain.com www.yourdomain.com;
  {{root}}
  include /etc/nginx/global_settings;
  
  # Custom routing for clean URLs - ADD THIS SECTION
  location /your-app-folder/ {
    try_files $uri $uri/ /your-app-folder/router.php?$query_string;
  }
  
  try_files $uri $uri/ /index.php?$args;
  index index.php index.html;
  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_intercept_errors on;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    try_files $uri =404;
    fastcgi_read_timeout 3600;
    fastcgi_send_timeout 3600;
    fastcgi_param HTTPS "on";
    fastcgi_param SERVER_PORT 443;
    fastcgi_pass 127.0.0.1:{{php_fpm_port}};
    fastcgi_param PHP_VALUE "{{php_settings}}";
  }
  if (-f $request_filename) {
    break;
  }
}
