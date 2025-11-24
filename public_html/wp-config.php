<?php
define( 'WP_CACHE', true );

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u888022333_gv0FJ' );
/** Database username */
define( 'DB_USER', 'u888022333_Wf5on' );
/** Database password */
define( 'DB_PASSWORD', 'SkgqZ8zX1f' );
/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );
/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );
/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '~X_V}2jI0,xEgccG+G=1E7TAeBIe_<Ibi}Y8N]%>`-V~Z&Bf$tE9}_M9/3F<P?{U' );
define( 'SECURE_AUTH_KEY',   'KR+r~W-<j~|p+g`gZf<UQI[RPxu;X,a&s8!{}jA7$wQogIVCAz.#J}_{gnNi05T=' );
define( 'LOGGED_IN_KEY',     'a* K i{2b&!C}`D%ip=:x$Gp&DyY1ebBK_,kw?du_bU^W ?tjPnVsGyl0<Gu:{%+' );
define( 'NONCE_KEY',         '#=HV]{O^Z27a/(/sODk7K^I>jR9M8J|X$$!4[G5}:Sk`T9q1,($>YUsd_vcld,~u' );
define( 'AUTH_SALT',         'l+:yU%S:N@ -W9PpTjh9!q%KBmwea*oSR])T@/mElY`G[q9.1wOoe5{5ao3_QaQ5' );
define( 'SECURE_AUTH_SALT',  '1T3f1>Ljs91XU1gQg7{#e*cJG87f|wSkoH&VYH_U?%+oQ~72#+IE?}RA8U#D9%iU' );
define( 'LOGGED_IN_SALT',    'ygBk& tx/QW:}-owR>p%KI_&CM?bdhBX]Ta110>S+e8$0_EuA!aG+`k`L)rHd~cU' );
define( 'NONCE_SALT',        'Yi.w1avW.UWt]5PqSB@gzZISC ?;,N(=i8NM}7H0sfpU;){/&eT`mc5YcId}}T4[' );
define( 'WP_CACHE_KEY_SALT', ';6uG+ LRt6 `eU`U1JgjUv;j* 5$$#HQ`< ^b2CAn8YgF]kRa4k-u{R%ynD$HkaZ' );
/**#@-*/
/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
/* Add any custom values between this line and the "stop editing" line. */
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
 
//if ( ! defined( 'WP_DEBUG' ) ) {
//	define( 'WP_DEBUG', false );
//}
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);

/////

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '2fa9e6e16a84c2b49e12a0c3c1b68beb' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';