<?php
    if( file_exists( __DIR__ . '/local-config.php' ) ){
        require_once( __DIR__ . '/local-config.php' );
    }
    if( ! defined( 'LOG_PATH')) {
        define( 'LOG_PATH',  __DIR__ . '/php-errors.log' );
    }
    if( ! defined( 'SITE_URL')) {
        define( 'SITE_URL',  'http://mydomainname.com' );
    }
    if( ! defined( 'DB_HOST')) {
        define( 'DB_HOST', 'localhost' );
    }
    if( ! defined( 'DB_NAME')) {
        define( 'DB_NAME', 'db_name' );
    }
    if( ! defined( 'DB_USER')) {
        define( 'DB_USER', 'db_user' );
    }
    if( ! defined( 'DB_PASSWORD')) {
        define( 'DB_PASSWORD', 'db_password' );
    }

    
    if( ! defined( 'SMTP_HOST')) {
        define( 'SMTP_HOST', 'yoursmtphost.com' );
    }
    if( ! defined( 'SMTP_USERNAME')) {
        define( 'SMTP_USERNAME', 'your-smtp-user' );
    }
    if( ! defined( 'SMTP_PASSWORD')) {
        define( 'SMTP_PASSWORD', 'your-smtp-password' );
    }
    if( ! defined( 'SMTP_SECURE')) {
        define( 'SMTP_SECURE', 'tls' );
    }
    if( ! defined( 'SMTP_PORT')) {
        define( 'SMTP_PORT', 587 );
    }
    
    date_default_timezone_set( 'America/New_York' ); 

    require_once( __DIR__ . '/functions.php' );