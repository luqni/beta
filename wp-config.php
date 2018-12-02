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

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'h27i:pn@UkHPJ(~c:-H_L/f !^-SiKfSH}TM4X<O~0.a@L-kT3XS>`}wU r2WZIE');
define('SECURE_AUTH_KEY',  '2TZIUreBGNPK|qM@Z]1zry0R2TM;Vn_GlCakM20 8w%Gw;qlkJpbsS^7VHt`Dp59');
define('LOGGED_IN_KEY',    'ih4u^,BKG?1n5/8stQDwnCpp.&pWx_}l1[^X9%7?H{P<b *e.*gR?vvL!k#q*+q2');
define('NONCE_KEY',        'A:5SgA!v{:Oqldc?M_S#MF2Oy{l=eRv5*Vnkw*{wg}Arf$bi#ie|ko]Wr{xeAU1;');
define('AUTH_SALT',        '4&u=/rFH&P9|?P5fs#B|H1~-^TBg_qlHr:@e ;s_BFkbAGyz>@Sg+>1#&pRWV[qu');
define('SECURE_AUTH_SALT', 'seHRX%FYmUqv1+7.8K3sJcW>K3.0,xio5mR7}TiL-WTQ/wLewWiKf-5+eDt]rhSj');
define('LOGGED_IN_SALT',   '9@[dEnhV[<6Zx9Q`;QkJEi4{0i^b#!ljAk5iUDg$*?NbO`n.^!f0PFn:pt@,6SIY');
define('NONCE_SALT',       'c ^@)I6k{fC2c1 ?Z|8U&4PmJ_/bIj)kATm&4]G}uAk$x{Exe9KGUhJ]aRox%d@W');

/**#@-*/

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

