
add following to nginx/sites-enabled/default server block


        listen 443 default_server ssl; ssl on;
        ssl_certificate /etc/nginx/ssl/wp.in/self-ssl.crt; set $root $host;
        ssl_certificate_key  /etc/nginx/ssl/wp.in/self-ssl.key;


sudo mkdir -p /etc/nginx/ssl/wp.in
cd /etc/nginx/ssl/wp.in
if asked for passphrase, just use 1111 or alternative

sudo openssl genrsa -des3 -out self-ssl.key 2048
 sudo openssl req -new -key self-ssl.key -out self-ssl.csr
sudo openssl x509 -req -days 365 -in self-ssl.csr -signkey self-ssl.key -out self-ssl.crt
sudo service nginx restart

when asked for COMMON NAME enter *.vagrantshare.com

when restarting nginx you will need to put in passphrase you used above (1111)
