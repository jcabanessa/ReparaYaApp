<?php
/**
 * The base configuration for WordPress
 * Secure configuration reading from environment variables
 */

if (!function_exists('getenv_docker')) {
    function getenv_docker($env, $default) {
        if ($fileEnv = getenv($env . '_FILE')) {
            return rtrim(file_get_contents($fileEnv), "\r\n");
        }
        else if (($val = getenv($env)) !== false) {
            return $val;
        }
        else {
            return $default;
        }
    }
}

// ** Database settings - Leyendo de variables de entorno (Sin secretos expuestos) ** //
define( 'DB_NAME', getenv_docker('MYSQL_DATABASE', '') );
define( 'DB_USER', getenv_docker('MYSQL_USER', '') );
define( 'DB_PASSWORD', getenv_docker('MYSQL_PASSWORD', '') );
define( 'DB_HOST', getenv_docker('MYSQL_HOST', 'localhost') );

/** Database charset */
define( 'DB_CHARSET', getenv_docker('MYSQL_CHARSET', 'utf8mb4') );
define( 'DB_COLLATE', getenv_docker('MYSQL_COLLATE', '') );

/** Authentication unique keys and salts */
define( 'AUTH_KEY',         getenv_docker('WORDPRESS_AUTH_KEY',         '5af5ac24735829318a6e53ba9516fc9d38cee0bb') );
define( 'SECURE_AUTH_KEY',  getenv_docker('WORDPRESS_SECURE_AUTH_KEY',  '66dd920a879e38b024923a8efa9ef421067a5891') );
define( 'LOGGED_IN_KEY',    getenv_docker('WORDPRESS_LOGGED_IN_KEY',    '7ab55a14f0964e4e032a73db20cab22ebae1d7ea') );
define( 'NONCE_KEY',        getenv_docker('WORDPRESS_NONCE_KEY',        '877e637b2b95dad574b759d4bfe74020f9006061') );
define( 'AUTH_SALT',        getenv_docker('WORDPRESS_AUTH_SALT',        '207f7412a5c7d61846bf250a4e1d6babccaf7bcc') );
define( 'SECURE_AUTH_SALT', getenv_docker('WORDPRESS_SECURE_AUTH_SALT', '3906b895635d2ed91eebdcc8f03028abf716d7ef') );
define( 'LOGGED_IN_SALT',   getenv_docker('WORDPRESS_LOGGED_IN_SALT',   '64a89c593ff2d09d25cba800c964a339d58b17be') );
define( 'NONCE_SALT',       getenv_docker('WORDPRESS_NONCE_SALT',       '99d21fb86f92df5691a00bf52e50414d30089688') );

$table_prefix = getenv_docker('WORDPRESS_TABLE_PREFIX', 'wp_');

define( 'WP_DEBUG', !!getenv_docker('WORDPRESS_DEBUG', '') );

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false) {
    $_SERVER['HTTPS'] = 'on';
}

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';