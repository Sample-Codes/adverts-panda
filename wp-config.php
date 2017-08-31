<?php
/**
 * Основные параметры WordPress.
 *
 * Скрипт для создания wp-config.php использует этот файл в процессе
 * установки. Необязательно использовать веб-интерфейс, можно
 * скопировать файл в "wp-config.php" и заполнить значения вручную.
 *
 * Этот файл содержит следующие параметры:
 *
 * * Настройки MySQL
 * * Секретные ключи
 * * Префикс таблиц базы данных
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Параметры MySQL: Эту информацию можно получить у вашего хостинг-провайдера ** //
/** Имя базы данных для WordPress */
define('DB_NAME', 'develop_panda');

/** Имя пользователя MySQL */
define('DB_USER', 'develop_panda');

/** Пароль к базе данных MySQL */
define('DB_PASSWORD', 'T8b7H0q1');

/** Имя сервера MySQL */
define('DB_HOST', 'localhost');

/** Кодировка базы данных для создания таблиц. */
define('DB_CHARSET', 'utf8');

/** Схема сопоставления. Не меняйте, если не уверены. */
define('DB_COLLATE', '');

/**#@+
 * Уникальные ключи и соли для аутентификации.
 *
 * Смените значение каждой константы на уникальную фразу.
 * Можно сгенерировать их с помощью {@link https://api.wordpress.org/secret-key/1.1/salt/ сервиса ключей на WordPress.org}
 * Можно изменить их, чтобы сделать существующие файлы cookies недействительными. Пользователям потребуется авторизоваться снова.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '!f0+OV`1s|s7w E+Qt%_m{dc9G%thP4(jl/O_73-rrMxV#h$IMo58nYR.uJ&sp&G');
define('SECURE_AUTH_KEY',  'NHz2(zbgLkWXUgPH8tBR)]|ldcW-}R-h<5=Q=AF7F4Z_gVcJ,qT0aX`T*WwA3,El');
define('LOGGED_IN_KEY',    'T3M/kI+`2!As{7T[tXjLfg JSLwt)l5u(~.B@ty|6tv.snj,<a}[91;?D}bj+Z o');
define('NONCE_KEY',        'tF`v(}(K0eN H2s5{1LEkhEi2^yg|CU%Rk CTQQ^a}:h/3;J~O`x^Ooc+aec9#-H');
define('AUTH_SALT',        'B-*pi(c%7j|,R-!K.nLIh)T}O,_rtXP&oq_aTx9*a}OT3m3ud*odZQJ^ta>w6iQj');
define('SECURE_AUTH_SALT', '25SXgzERe`E&x=f&_KLi<ka9NS$@3P}fR`nY n#DZbT^!BD5Qa+Sw8Swp*sD[fKK');
define('LOGGED_IN_SALT',   'VOZWqoEDmuLz07wy_S,Qf?ePYl&1Nik<pCdgm_vujb,RJEa0AGH;u*;J-ix}!(.S');
define('NONCE_SALT',       'sjFLL#=Pt4U.PVR_)iR VT$LRhZ,GVIDwF*{_jP^n+n)6G!UeWD?XE_KnKW3Rypz');
define('WP_SITEURL', 'http://develop.panda-code.com/');
define('WP_HOME', 'http://develop.panda-code.com/');

/**#@-*/

/**
 * Префикс таблиц в базе данных WordPress.
 *
 * Можно установить несколько сайтов в одну базу данных, если использовать
 * разные префиксы. Пожалуйста, указывайте только цифры, буквы и знак подчеркивания.
 */
$table_prefix  = 'wp_';

/**
 * Для разработчиков: Режим отладки WordPress.
 *
 * Измените это значение на true, чтобы включить отображение уведомлений при разработке.
 * Разработчикам плагинов и тем настоятельно рекомендуется использовать WP_DEBUG
 * в своём рабочем окружении.
 * 
 * Информацию о других отладочных константах можно найти в Кодексе.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* Это всё, дальше не редактируем. Успехов! */

/** Абсолютный путь к директории WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Инициализирует переменные WordPress и подключает файлы. */
require_once(ABSPATH . 'wp-settings.php');
