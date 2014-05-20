Notes
====

To enable the GeoIP, use the [MaxMind] details that are linked to hello@hmn.md email address.

To use Foursquare API
1. Create a [Foursqaure app]
2. Take all details and insert them into the plugin options page ( under Settings ).
3. Create a Google Timezone API and add that into the plugin options page.
4. Once you have connected your Foursquare account to your WordPress user, then you can use the Foursquare push console to  push your Foursquare user to the site.

*Google Timezone API*
 Foursquare User Push API no longer returns the location's timezone ID. It only sends back the time offset. To get around this, the plugin sends the pushed venue latitude and longitude to Google's Timezone API and that returns the necessary timezone ID.

[MaxMind] https://www.maxmind.com/en/my_license_key
[Foursqaure app] https://foursquare.com/developers/app/


Server Configs
==============

Vagrant Share
-------------
When running vagrant share on the default salty stack, the designated external URL points to the server and not to the WordPress instance.

I modified the salty stack to point to wordpres-trunk.dev as default as shown on [GitHub].

[GitHub] https://github.com/missjwo/Salty-WordPress/commit/d6bbba5bb993175be6bdbda1011eb401b2f50f48


SSL Certificates
----------------
Foursquare API insist on a https connection. These are notes on how to make the salty stack have a SSL certificate.

---------------------------

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

Known Issues
============
At time of writing:

* The plugin settings input validation is non exsitant
* The API endpoint that are currently available are:
   - Foursquare for connections and User Push API {site_url}/wp-json/hm-time
   - Users {site_url}/wp-json/hm-time/users
* When setting up the Foursquare connection, making the redirect uri to be the same as the push url for it to connect Foursquare user to your WordPress user successfully. The problem with that is when connecting your account from the profile page is that you are then redirected to said page. This is something I have not figured out how to fix.
* Accessing the admin area of wordpres-trunk.dev via vagrant share's external URL returns me back to the internal url as I set up the site locally first.
	- Because of this, when testing the GeoIP, the plugin tries to find the server ip 192.168.50.10. For testing purposes, I have written my external IP into lines 16 and 32 of the includes/geoip.php file.
	- For the same reason includes/api-foursquare.php line 37 has the user_id hardcoded to 1 for the default site admin. This is so that when Foursquare interacts with the site to get the user access code it knows whom to save the access token against.
* The redirect_uri for foursquare should actually be auto built by the plugin but as the testing was done using vagrant share, this has been made into an option which can be updated. There were ideas of making a dev mode in which this would only show. This effects includes/api-foursquare.php line 49.


Future
======

Future plans would include

* Fixing the above issues
* Object oretinating the whole project
* Writing better error messages.
* Create a single bootstrap class
* Refactoring the Settings inputs so that they don't repeat so much.
* Add the rest of the [API design]
* Create a sidebar widget that uses the Users endpoint
* Create a Meeting Time finder ( inspired by the OSX app Clock )
* Get Sir Hubbot to answer the age old question of * Where is @joe * by returning Joe's last saved location.

[API design] api_design.md



