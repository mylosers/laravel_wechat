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
define( 'DB_NAME', 'laravels' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', '192.168.70.1' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ']Q@y4>AtMp:9rKx3Ol`?XpZ@5vhb5bn5GF|Kg&zaZ$9}Bo:Sk26U!4z2OW?ebc.R' );
define( 'SECURE_AUTH_KEY',  'l`<}TJ$39B|nP}h?e}^7<4J3r~C:V4k(lW^lw%8k!q+rkS)m<e^Q6Tux9!P6-EB[' );
define( 'LOGGED_IN_KEY',    'YbA07&rEijDX2U;2f4rwn+`/fqvG%hx4xhH3g]iU])~(MHRThyJ5yps@Bgt8L_)[' );
define( 'NONCE_KEY',        's5P/!_GsV)`|`c-GA?iE0^w}vgdhOs$@O_/2o|t=>=2<;P7Qa/vQr1Km%XIdzgg%' );
define( 'AUTH_SALT',        '<vJH]dVZO|w@]|Q)LT{9F$+bA)F,s}^&$q#F2Cd`ws-15;)mxAS!8O-)R(5C/^eH' );
define( 'SECURE_AUTH_SALT', 'YSGUm`sh-(u^6^;y0A}9Y9?E,Qb{N{@0jMS&#-O#%Y%N9c;N|/Ww(6@-v#/D++wf' );
define( 'LOGGED_IN_SALT',   'ol!XopotUBr[.4_fHpq=Jjuo0n!#N}zqF_[rRwNReNaNC[ R]FyMtG~^3WMjJpv:' );
define( 'NONCE_SALT',       'W*uLJ:On6 4_tn/k)i/I-h%^m1JHM%1C}kv{fN~nO|;eg*VihHD8lL|uW{Iv]/f:' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
