PerfunctoryPHP
==============

An MVC platform that allows you to quickly without thinking about too much create pages in PHP without a lot of server tags/programming to design a site.

the root is were all the views go.  Models and templates go in the respected folders.

Reference:  http://prayersphere.com/CodeIgniter/new-development/the-rundown

To get things working you must start by editing your .htaccess file like this:

RewriteEngine on
RewriteCond %{REQUEST_URI} !(redirector.php)
RewriteRule /(.*)$         xxxxxxx/Core/redirector.php?page=$1

where xxxxxxx is the folder of your app.