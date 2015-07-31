SCE Web is a client interface for Smart Cloud Engine scheduling platform.

Prerequisites (on Debian/Ubuntu):

- building environment (gcc, g++, make)

- Apache 2.2 (apache2)

- PHP >= 5.4

- MySQL php extension php5-mysql

- uuid php pecl extension

  apt-get install php5-dev uuid-dev

  pecl install uuid

  add uuid.so to php.ini

Configuration:

- adjust settings.php in /sce folder with Tomcat host and MySQL credentials

$config['tomcat'] = "localhost"; // ip of tomcat service
$config['host'] = "localhost"; //ip of database
$config['user'] = "username"; //db username
$config['pass'] = "password"; //db password
