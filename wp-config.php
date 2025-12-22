<?php
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

/**
 * Database connection information is automatically provided.
 * There is no need to set or change the following database configuration
 * values:
 *   DB_HOST
 *   DB_NAME
 *   DB_USER
 *   DB_PASSWORD
 *   DB_CHARSET
 *   DB_COLLATE
 */

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */

define('AUTH_KEY',         '6Q@wWnzE<X9h2l,iIm<G=:xy=G0gdcKQOr8EW1t.:Qa%,=es0H5]V*N%D3ytwiY{');
define('SECURE_AUTH_KEY',  'HTHC45LcpCAHN|r~2Ah29}tjF<}RX+3vkutV7b0Pz!X6~$fo$T#^e|axZL_n=JTg');
define('LOGGED_IN_KEY',    '8u38,TAj749yIxqQG!cZGWW=0KV#nMW!k4NJNaTkRXG3X]oo:r<PR)a;dO,U-v}b');
define('NONCE_KEY',        '#|P~lyb>q8j|Geg]6EG6gk+__uRV@C>fbYpYQyz|Me,.vw:YJk?m6-p%hhI*y9t(');
define('AUTH_SALT',        '|aV=1(I9^Og1D%w$ae4%m@b$7$?$;B,~A.V}$LQ]Bz}T5oy>|fz0f4Dx|3hY_L7m');
define('SECURE_AUTH_SALT', 'uePco!ST3Sy0W^X:><(iavMAXfq:TwS]+n6GL~ak$l2H%ksMx.8abaJr4fs2x#f?');
define('LOGGED_IN_SALT',   'q=V]FL[78Xh3s$#1kwo*}qK*VT]>jhw@;8BuSz;GFuJl0*,[F>R:CHoAIO%PSH9q');
define('NONCE_SALT',       '<g.~BTU$7Yvrbr)H!9}zDCsrjqBS8x[d]pFGt#~eROZD*uAa~C[sc(zYFP-IKAJG');

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
  define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
