<?php
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
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'nepalinseattle' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '0lQL]~.iH~|ZoY9fm<B68se4WXTa*/_rK&f9zqM+1SWawp|Ca]OEp&*0I>70T<#y' );
define( 'SECURE_AUTH_KEY',  'kfL:[~e+x{[,bKOno;pqOkN&w?paT~k=oF1fcYCWu@^$ei+8`1|Thm9!04%m44_!' );
define( 'LOGGED_IN_KEY',    's?h9-j_4b&j#bAh@GljAv>J3Ew2 Q>U;w=KrA)dnA2-<Y[@{f,/~{=r)X!9[:s.h' );
define( 'NONCE_KEY',        'y&M:LNGhItPsXS6OdLigNP,k4xZk}U@NS[E&)LH3MT+]cL^Zx%8ctECC?AR7&OlI' );
define( 'AUTH_SALT',        'l#Is?*hNu5dc ?rVU@V-Ce}V_e@8V]|;yv|BIr]o:UUqj8xLi^I9}Z4ewUKK:q>/' );
define( 'SECURE_AUTH_SALT', '$2QK>?[e-45<fp#:w|B}A`.rp%g/k1}PIs`d.N`[)S.Z<UB.ATDOuuFWryN#dxy#' );
define( 'LOGGED_IN_SALT',   '8|7_j@_wz=s)3ta*AtfXyU6)A$txO?A0XK=bi&C;Z^8`3Z2]fTdnH;2T#.[6*2|k' );
define( 'NONCE_SALT',       'WWAtiReP^]w0]#59C5=}|R2x2>}2iMp#Slet;7:iA/K[zHI]R&(}e*.at^}7eeUD' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
