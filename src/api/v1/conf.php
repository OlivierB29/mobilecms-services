<?php

// Only for development
define ( 'ALLOW_CROSS_DOMAIN', 'true' );


//eg : /var/www/html
define ( 'HOME', $_SERVER ['DOCUMENT_ROOT'] );

//If possible, use a directory only accessible with filesystem queries.
//Unless, use a .htaccess file
//eg : /var/www/private
define ( 'PRIVATEDIR', realpath($_SERVER ['DOCUMENT_ROOT'] . '/../private') );

?>
