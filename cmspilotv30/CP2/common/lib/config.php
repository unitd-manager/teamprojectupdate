<?
$cpCfg = array();
define('CP_PATH2_ALIAS', CP_CMSROOT_ALIAS . 'CP2/');

$masterPath      = (CP_SCOPE == 'admin') ? CP_PATH2 . 'admin/' : CP_PATH2 . 'www/';
$masterPathAlias = (CP_SCOPE == 'admin') ? CP_PATH2_ALIAS . 'admin/' : CP_PATH2_ALIAS . 'www/';

define('CP_COMMON_PATH2', CP_PATH2 . 'common/');
define('CP_MASTER_PATH2', $masterPath);

define('CP_COMMON_PATH2_ALIAS', CP_PATH2_ALIAS . 'common/');
define('CP_MASTER_PATH2_ALIAS', $masterPathAlias);

define('CP_LIB_PATH2_COMMON', CP_COMMON_PATH2 . 'lib/');
define('CP_LIB_PATH2', CP_MASTER_PATH2 . 'lib/');

define('CP_MODULES_PATH2_COMMON', CP_COMMON_PATH2 . 'modules/');
define('CP_MODULES_PATH2', CP_MASTER_PATH2 . 'modules/');
define('CP_MODULES_PATH2_COMMON_ALIAS', CP_COMMON_PATH2_ALIAS . 'modules/');
define('CP_MODULES_PATH2_ALIAS', CP_MASTER_PATH2_ALIAS . 'modules/');

define('CP_WIDGETS_PATH2_COMMON', CP_COMMON_PATH2 . 'widgets/');
define('CP_WIDGETS_PATH2', CP_MASTER_PATH2 . 'widgets/');
define('CP_WIDGETS_PATH2_COMMON_ALIAS', CP_COMMON_PATH2_ALIAS . 'widgets/');
define('CP_WIDGETS_PATH2_ALIAS', CP_MASTER_PATH2_ALIAS . 'widgets/');

define('CP_PLUGINS_PATH2_COMMON', CP_COMMON_PATH2 . 'plugins/');
define('CP_PLUGINS_PATH2', CP_MASTER_PATH2 . 'plugins/');
define('CP_PLUGINS_PATH2_COMMON_ALIAS', CP_COMMON_PATH2_ALIAS . 'plugins/');
define('CP_PLUGINS_PATH2_ALIAS', CP_MASTER_PATH2_ALIAS . 'plugins/');

define('CP_THEMES_PATH2_COMMON', CP_COMMON_PATH2 . 'themes/');
define('CP_THEMES_PATH2', CP_MASTER_PATH2 . 'themes/');
define('CP_THEMES_PATH2_COMMON_ALIAS', CP_COMMON_PATH2_ALIAS . 'themes/');
define('CP_THEMES_PATH2_ALIAS', CP_MASTER_PATH2_ALIAS . 'themes/');

define('CP_SKINS_PATH2_COMMON', CP_COMMON_PATH2 . 'skins/');
define('CP_SKINS_PATH2', CP_MASTER_PATH2 . 'skins/');
define('CP_SKINS_PATH2_COMMON_ALIAS', CP_COMMON_PATH2_ALIAS . 'skins/');
define('CP_SKINS_PATH2_ALIAS', CP_MASTER_PATH2_ALIAS . 'skins/');

return $cpCfg;
