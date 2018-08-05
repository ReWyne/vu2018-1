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
define('DB_NAME', 'wp_db_2');

/** MySQL database username */
define('DB_USER', 'wp_db_2_admin');

/** MySQL database password */
define('DB_PASSWORD', 't');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

define('FS_METHOD', 'direct');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */

define('AUTH_KEY',         'H<m0JfR7_53zU-L<Yb}_>|ZG4k1X7Ie~REi|z:KaZ3G<C?Td2xn)J({^)8wtErVN');
define('SECURE_AUTH_KEY',  'd]QHV:5/JTx,sA~/2!|&,#m$7Uf^l,oT.1losop+`. [NO1-! J+) fI;RU]YfjQ');
define('LOGGED_IN_KEY',    'r?xrYAc1+;ekGk33eK77@!n{g7:uyJ|IpkkbL*g*=O]M~rV8o%I(;%7$$u/PyDS<');
define('NONCE_KEY',        '3`[<5k}/#%xZ.N3A4UK=R I776=a4M(uS#_VMx3?UNdi0}*{xj`Z2^+G|Yf0<8_%');
define('AUTH_SALT',        '6Q8~[KG<s0`s:DHrhauHA)/Q|Ur4H*BrD!el}eAL8(m2#lvi[iPuR?_4`~HLMj#]');
define('SECURE_AUTH_SALT', 'k;UTZ<ak?`mfMoiNwK[&`ItM|n]V+>NO;,-u-~0wv(GQg`!HNnD?Y]2UkvoBT|Sq');
define('LOGGED_IN_SALT',   '^cm73KiMEB]^?a|~+S;%0%Z$=*DIl51J;:[Y)cg{XW4$*&B3L@7Jgg2_=Y|kQ){m');
define('NONCE_SALT',       'k`8!}O#ZXfp(_zN?]@k,M42ko=shlM*DZL~|KiYsfn7>78h*YNV``%73F>>TgO?K');

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
// Enable WP_DEBUG mode
define('WP_DEBUG', true);
// Enable Debug logging to the /wp-content/debug.log file
define('WP_DEBUG_LOG', true);

define('WP_DEBUG_DISPLAY', true);

define( 'SAVEQUERIES', true );
// Disable display of errors and warnings 
//define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors',0);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
