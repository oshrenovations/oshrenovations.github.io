<?php
define('WP_AUTO_UPDATE_CORE', 'minor');// This setting is required to make sure that WordPress updates can be properly managed in WordPress Toolkit. Remove this line if this WordPress website is not managed by WordPress Toolkit anymore.
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_wx8ty' );

/** MySQL database username */
define( 'DB_USER', 'wp_dtqwo' );

/** MySQL database password */
define( 'DB_PASSWORD', 'f39io$28aywDa*_I' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'F#y_3oAtT]ztc2H+[(r7IH4WVB7]-j6Em8S87[/f~X-*99tg/6L[N/|;c3G:jt~6');
define('SECURE_AUTH_KEY', '46_r19;Zi3B1844~3;323mpnn@X815r4%xxfE2#Kn8)f4V/h2#3B9NOPW5zy2+Mk');
define('LOGGED_IN_KEY', ')FSnh628pN*:!k9Tvz8G|jOw(w+U:6kaJr~Z8mdL9c6g:+&RZg/0FK1LR5oe3K1Q');
define('NONCE_KEY', '7QE80W2Z+ed5_h4y3M@3T+9+0_0_8c_N2WB!A3rlq~5*s18_4OT[]i4gtLC-H9IJ');
define('AUTH_SALT', 's75qBN4tm6L%4id(_@DJ[n]+9XG#qf6N22|EjdoX;2rB(B#)S4Ys;k!]6Yl28%t-');
define('SECURE_AUTH_SALT', 'I1s(6|xG~75g8y3Jj~zkp5:gTS08+U5Us!0qnLb&ynpq0M6I&V64#C0VQR3:_F7v');
define('LOGGED_IN_SALT', '87e427qV;_l9PMZ4xMBSN5-[D4;5:O~VO#*&8r7m1iO]G11bgfXjJc@3))~OITo!');
define('NONCE_SALT', 'BmLB!m%ydoeN-JP%3p-*15lZIl6hZ5(62UMIW@E&3B@H2u58#7(_()2I1e@cc|n0');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'OTd2JNM_';


define('WP_ALLOW_MULTISITE', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
