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
define('DB_NAME', 'nghp');

/** MySQL database username */
define('DB_USER', 'nghp');

/** MySQL database password */
define('DB_PASSWORD', 'Rhksflwk1!');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         '{p>{ l6dqh,CjG)|_fVtL_}>zBlA~pmW3q{MZo&blgSoR*4l0xeq.,pcK_J}_Y8`');
define('SECURE_AUTH_KEY',  'i6t.:ESwb0EuJruSGM[}[*g.g/0;!P;=~0xQTVcE>t/DPwf51H]NGM~!wQAXPg&9');
define('LOGGED_IN_KEY',    '>@Y^b!M^t@@H?$u:V(Awom=)|m`T[2~OJigxWWRck>ub<BysCbJ2zL%l}UF$u(JY');
define('NONCE_KEY',        'Irck;S(_o_[=eZotg/`abp5=Pd@3y8Qjkg~qn`Z`eJ4Y{jhNK|VCd%>6][?-cT>F');
define('AUTH_SALT',        '-+Y7]HNQ;;fKox{yTfaMp9-mB>?z-s)aWwReu/_!&7 ]Si^RG`NP<BPsTe{*LOK)');
define('SECURE_AUTH_SALT', '% Vn#Y[y59VSs*O1By4?@Zs;21WeK]>`yyY%O!oN $vhg-@>IvWqC-<[ c),[u=V');
define('LOGGED_IN_SALT',   '-wG9@_UZA{Q?in?0k;pfM}W #wFK!@dfpkrZlU{$/6B_n>zZn3JY$/:j:XdX1@!e');
define('NONCE_SALT',       'u.<mu2qko 2O^1kgme8clw1]udFiP!*wmW6R>78G4{bf&-9:@`FHzd^4O|-b5$OP');

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
define('WP_MEMORY_LIMIT', '256M');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
