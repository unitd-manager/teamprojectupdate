<?
if (!defined('CP_CMSROOT_ALIAS')){
    define('CP_CMSROOT_ALIAS', '/cmspilotv30/');
}

define('CP_NAMESPACE', 'CP_');
define('CP_NAMESPACE_LOCAL', 'CPL_');
define('CP_LIBRARY_PATH', CP_CORE_PATH . 'library/');
define('CP_LIBRARY_PATH_ALIAS', CP_CMSROOT_ALIAS . 'library/');
define('CP_PATH_ALIAS', CP_CMSROOT_ALIAS .'CP/');

$cpCfg = array();

/********************************************************************/
$cpCfg['cp.siteRootAlias'] = '/';

if (CP_SCOPE == 'www'){
    $cpCfg['cp.siteRoot'] = '';
    $cpCfg['cp.useSEOUrl'] = 1;
    $cpCfg['cp.scopeRootAlias'] = '/';
} else {
    $cpCfg['cp.siteRoot'] = '../';
    $cpCfg['cp.useSEOUrl'] = 0;
    $cpCfg['cp.scopeRootAlias'] = '/admin/';
}

$cpCfg['cp.hasAlternativeCore'] = false;
$cpCfg['cp.assetVersion'] = '';

if (defined('CP_PATH2')){
    $cpCfg['cp.hasAlternativeCore'] = true;
}

//path of CLI php executable. Used for running background jobs ($cpUtil->callScriptInBackground()
// when the php path is different from default path. Just for Linux.
$cpCfg['cp.phpPath'] = '';

/********************************************************************/
$cpCfg['cp.siteUrl']            = 'http://'. CP_HOST . '/';
$cpCfg['cp.siteUrlNoSlash']     = 'http://'. CP_HOST;
$cpCfg['cp.hasAdminOnly']       = false;
$cpCfg['cp.frameworkName']      = "CMS PILOT";
$cpCfg['cp.version']            = "3.0";
$cpCfg['cp.defaultLanguage']    = "eng";
$cpCfg['cp.defaultAdminIntLanguage'] = "eng"; //admin interface language
$cpCfg['cp.dbType']             = 'mysql';
$cpCfg['cp.runSetNamesUtf8']    = true;

/********************** CSS & JAVASCRIPT ************************/
$cpCfg['cp.useCdn']             = true;
$cpCfg['cp.useMinFiles']        = false;
$cpCfg['cp.cssFramework']       = 'yaml'; //yaml, bootstrap

$cpCfg['cp.yamlVersion']        = 'v3.3';
$cpCfg['cp.bootstrapVersion']   = '3.0.0';
$cpCfg['cp.loadFontAwesome']    = false;
$cpCfg['cp.fontAwesomeVersion'] = '3.2.1';
$cpCfg['cp.loadResponsiveCSS']  = false;

// if you do not want any files such as common/css/style.css turn this to false
$cpCfg['cp.loadCommonCssFiles'] = true;

$cpCfg['cp.jqVersion']          = '1.6.1';
$cpCfg['cp.jqUiVersion']        = '1.8.9';
$cpCfg['cp.jqUITheme']          = 'smoothness';
$cpCfg['cp.jqColorBoxVersion']  = '1.3.15';

$cpCfg['cp.defaultTheme']       = 'Default';
$cpCfg['cp.theme']              = isset($_REQUEST['cpTheme']) ? $_REQUEST['cpTheme'] : 'Default';
$cpCfg['cp.skin']               = isset($_REQUEST['cpSkin']) ? $_REQUEST['cpSkin'] : 'Default';
$cpCfg['cp.googleFontsString']  = '';

$cpCfg['cp.hasJqTools']         = true;
$cpCfg['cp.hasJqUi']            = true;
$cpCfg['cp.hasJqCpUtil']        = true;
$cpCfg['cp.hasModernizer']      = true;
$cpCfg['cp.hasRespondJS']       = true;
$cpCfg['cp.jqToolsVersion']     = '1.2.6';
$cpCfg['cp.hasJqLiveQuery']     = true;

$cpCfg['cp.hasAngularApp']      = false;
$cpCfg['cp.angularVersion']     = 'angular-1.0.1'; // default version
$cpCfg['cp.isMobileSiteUrl']    = false;
$cpCfg['cp.isMobileDevice']     = false;
$cpCfg['cp.cleanHtml']          = false;

$cpCfg['cp.doNotLoadCommonBootstrapFiles'] = false;
$cpCfg['cp.doNotLoadMasterThemeFiles']     = false;
$cpCfg['cp.doNotLoadJSSFilesForWidgets']   = false;
$cpCfg['cp.loadJSSFilesForOnlyWidgetsArr'] = array();

$cpCfg['cp.doNotLoadJSSFilesForModules'] = false;
$cpCfg['cp.loadJSSFilesForOnlyModulesArr'] = array();

$cpCfg['cp.doNotLoadJSSFilesForPlugins'] = false;
$cpCfg['cp.loadJSSFilesForOnlyPluginsArr'] = array();

/*********************** CORE COMMON ****************/
$cpCfg['cp.coreCommonFolder'] = CP_PATH . 'common/';

/*********************** LIBRARY PATHS ****************/
$cpCfg['cp.libPhpPath'] = CP_LIBRARY_PATH . 'lib_php/';
$cpCfg['cp.jssPath']    = (defined('CP_JSS_PATH')) ? CP_JSS_PATH : CP_CMSROOT_ALIAS . "library/jss/";

/*********************** MASTER PATHS ****************/
$cpCfg['cp.masterPath']      = (CP_SCOPE == 'admin') ? CP_PATH . 'admin/' : CP_PATH . 'www/';
$cpCfg['cp.masterPathAlias'] = (CP_SCOPE == 'admin') ? CP_PATH_ALIAS . 'admin/' : CP_PATH_ALIAS . 'www/';
$cpCfg['cp.masterImagesPathAlias'] = (CP_SCOPE == 'admin') ? CP_PATH_ALIAS . 'admin/images/' : CP_PATH_ALIAS . 'www/images/';

/*********************** LOCAL PATHS ****************/
$cpCfg['cp.localPath']      = (CP_SCOPE == 'admin') ? "{$cpCfg['cp.siteRoot']}admin/" : "{$cpCfg['cp.siteRoot']}www/";
$cpCfg['cp.localPathAlias'] = (CP_SCOPE == 'admin') ? "{$cpCfg['cp.siteRootAlias']}admin/" : "{$cpCfg['cp.siteRootAlias']}www/";
$cpCfg['cp.localImagesPathAlias'] = (CP_SCOPE == 'admin') ? "{$cpCfg['cp.siteRootAlias']}admin/images/"  : "{$cpCfg['cp.siteRootAlias']}www/images/";

define('CP_COMMON_PATH', CP_PATH . 'common/');
define('CP_MASTER_PATH', $cpCfg['cp.masterPath']);
define('CP_LOCAL_PATH', $cpCfg['cp.localPath']);

define('CP_COMMON_PATH_ALIAS', CP_PATH_ALIAS . 'common/');
define('CP_MASTER_PATH_ALIAS', $cpCfg['cp.masterPathAlias']);
define('CP_LOCAL_PATH_ALIAS', $cpCfg['cp.localPathAlias']);

define('CP_LIB_PATH_COMMON', CP_COMMON_PATH . 'lib/');
define('CP_LIB_PATH', CP_MASTER_PATH . 'lib/');
define('CP_LIB_PATH_LOCAL', CP_LOCAL_PATH . 'lib/');

define('CP_MODULES_PATH_COMMON', CP_COMMON_PATH . 'modules/');
define('CP_MODULES_PATH', CP_MASTER_PATH . 'modules/');
define('CP_MODULES_PATH_LOCAL', CP_LOCAL_PATH . 'modules/');
define('CP_MODULES_PATH_COMMON_ALIAS', CP_COMMON_PATH_ALIAS . 'modules/');
define('CP_MODULES_PATH_ALIAS', CP_MASTER_PATH_ALIAS . 'modules/');
define('CP_MODULES_PATH_LOCAL_ALIAS', CP_LOCAL_PATH_ALIAS . 'modules/');

define('CP_WIDGETS_PATH_COMMON', CP_COMMON_PATH . 'widgets/');
define('CP_WIDGETS_PATH', CP_MASTER_PATH . 'widgets/');
define('CP_WIDGETS_PATH_LOCAL', CP_LOCAL_PATH . 'widgets/');
define('CP_WIDGETS_PATH_COMMON_ALIAS', CP_COMMON_PATH_ALIAS . 'widgets/');
define('CP_WIDGETS_PATH_ALIAS', CP_MASTER_PATH_ALIAS . 'widgets/');
define('CP_WIDGETS_PATH_LOCAL_ALIAS', CP_LOCAL_PATH_ALIAS . 'widgets/');

define('CP_PLUGINS_PATH_COMMON', CP_COMMON_PATH . 'plugins/');
define('CP_PLUGINS_PATH', CP_MASTER_PATH . 'plugins/');
define('CP_PLUGINS_PATH_LOCAL', CP_LOCAL_PATH . 'plugins/');
define('CP_PLUGINS_PATH_COMMON_ALIAS', CP_COMMON_PATH_ALIAS . 'plugins/');
define('CP_PLUGINS_PATH_ALIAS', CP_MASTER_PATH_ALIAS . 'plugins/');
define('CP_PLUGINS_PATH_LOCAL_ALIAS', CP_LOCAL_PATH_ALIAS . 'plugins/');

define('CP_THEMES_PATH_COMMON', CP_COMMON_PATH . 'themes/');
define('CP_THEMES_PATH', CP_MASTER_PATH . 'themes/');
define('CP_THEMES_PATH_LOCAL', CP_LOCAL_PATH . 'themes/');
define('CP_THEMES_PATH_COMMON_ALIAS', CP_COMMON_PATH_ALIAS . 'themes/');
define('CP_THEMES_PATH_ALIAS', CP_MASTER_PATH_ALIAS . 'themes/');
define('CP_THEMES_PATH_LOCAL_ALIAS', CP_LOCAL_PATH_ALIAS . 'themes/');

define('CP_SKINS_PATH_COMMON', CP_COMMON_PATH . 'skins/');
define('CP_SKINS_PATH', CP_MASTER_PATH . 'skins/');
define('CP_SKINS_PATH_LOCAL', CP_LOCAL_PATH . 'skins/');
define('CP_SKINS_PATH_COMMON_ALIAS', CP_COMMON_PATH_ALIAS . 'skins/');
define('CP_SKINS_PATH_ALIAS', CP_MASTER_PATH_ALIAS . 'skins/');
define('CP_SKINS_PATH_LOCAL_ALIAS', CP_LOCAL_PATH_ALIAS . 'skins/');

$cpCfg['cp.themePathAlias'] = "{$cpCfg['cp.masterPathAlias']}themes/";
$cpCfg['cp.commonImagesPathAlias'] = CP_COMMON_PATH_ALIAS . 'images/';

/*********************** USER DATA ****************/
$cpCfg['cp.userDataPath']       = "{$cpCfg['cp.siteRoot']}user-data/";
$cpCfg['cp.langFilesPath']      = "{$cpCfg['cp.userDataPath']}lang/";
$cpCfg['cp.adminLangFilesPath'] = "{$cpCfg['cp.userDataPath']}lang-admin/";
$cpCfg['cp.configWWWFilePath']  = "{$cpCfg['cp.userDataPath']}config_www.php";
$cpCfg['cp.userTimeZone']       = '';

/*********************** MEDIA FOLDERS ************/
$cpCfg['cp.mediaFolder']             = "{$cpCfg['cp.siteRoot']}media/";
$cpCfg['cp.mediaFolderAlias']        = "{$cpCfg['cp.siteRootAlias']}media/";
$cpCfg['cp.fckEditorMediaFolder']    = "{$cpCfg['cp.mediaFolderAlias']}fck/";
$cpCfg['cp.UserFilesAbsolutePath']   = realpath("{$cpCfg['cp.mediaFolder']}fck/");
$cpCfg['cp.maxUploadLimit']          = '100'; //in MB
$cpCfg['cp.imageFileTypeExts']       = '*.gif; *.jpg; *.png';
$cpCfg['cp.attachmentFileTypeExts']  = '*.*';
$cpCfg['cp.uploadTechnology'] 		 = 'html'; // flash or html
$cpCfg['cp.dragAndDropUpload']       = ''; // '' or dragAndDrop

/***************************************/
$cpCfg['cp.includeFbLite'] = false;

/*********************** REVIEW LATER ****************/
$cpCfg['cp.showDeleteActionBtnInList'] = 0;
$cpCfg['cp.showAnchorInLinkPortal']    = 1; // whether to show anchors in link portal
$cpCfg['cp.autoAlignPortalCells']      = false; // this will automatically aling the numbers to right
$cpCfg['cp.hasShortcutIcon']           = 0;

$cpCfg['cp.dateDisplayFormat']         = 'd M Y';
$cpCfg['cp.dateDisplayFormatLong']     = 'd M Y l h:i:s A';
$cpCfg['cp.dateDisplayFormatTBS']      = 'dd/mmm/yyyy'; //date format for TBS (tiny but strong) template plugin
$cpCfg['cp.dateTimeDisplayFormatTBS']  = 'dd/mmm/yyyy  hh:nn:ss';
$cpCfg['cp.numberFormatTBS']           = '0,000.00';
$cpCfg['cp.numberFormatTBS2']          = '0,000.0000';
$cpCfg['cp.displayDecimalLength']      = 2;
$cpCfg['cp.displayDecimalLength2']     = 4;
$cpCfg['cp.locale'] = '';
$cpCfg['cp.showActionButtonsBelowNewForm'] = true;

$cpCfg['cp.hasMultiSites']     = false;
$cpCfg['cp.hasDeleteInList']   = false;
$cpCfg['cp.hasCheckboxInList'] = false;
$cpCfg['cp.hasCheckboxInListGlobal'] = false; //to enable checkbox in list in all the modules
$cpCfg['cp.hasMultiLangForMetaData'] = false;

/********************* MULTI COUNTRY ****************/
$cpCfg['cp.multiCountry']  = false;
$cpCfg['w.common_multiCountry.ignoreModules'] = array();

/********************* MULTI YEARS ****************/
$cpCfg['cp.hasMultiYears'] = false;
$cpCfg['w.common_multiYear.yearArray'] = array();
$cpCfg['w.common_multiYear.ignoreModules'] = array();

/********************* MULTI UNIQUE SITE ****************/
$cpCfg['cp.hasMultiUniqueSites'] = false;
$cpCfg['cp.chooseSiteLbl'] = "Choose Site:";
$cpCfg['w.common_multiUniqueSite.ignoreModules'] = array();

/********************* SECURITY ****************/
$cpCfg['cp.hasEncryptedDbPassword'] = false;

/********************* SERVER LEVEL CONFIG DEFAULTS ****************/

/********************* MAILCHIMP ****************/
$cpCfg['p.common.mailChimp.apiKey'] = '';
$cpCfg['p.common.mailChimp.newsletterListId'] = '';

$cpCfg['cp.useLangsInConfigForMultiCountry'] = false;
$cpCfg['cp.useLangsInConfigForMultiSite']    = true;

/********************* COOKIES ****************/
$cpCfg['cp.cookieExpiryInSeconds'] = time() + 1209600; //2 weeks
$cpCfg['cp.cookieSecure'] = NULL;

/********************* COPY CONSTANTS TO $CFG ****************/
$cpCfg['cp_namespace']                = CP_NAMESPACE;
$cpCfg['cp_namespace_local']          = CP_NAMESPACE_LOCAL;
$cpCfg['cp_library_path']             = CP_LIBRARY_PATH;
$cpCfg['cp_library_path_alias']       = CP_LIBRARY_PATH_ALIAS;
$cpCfg['cp_path_alias']               = CP_PATH_ALIAS;

$cpCfg['cp_common_path']              = CP_COMMON_PATH;
$cpCfg['cp_master_path']              = CP_MASTER_PATH;
$cpCfg['cp_local_path']               = CP_LOCAL_PATH;
$cpCfg['cp_local_path']               = CP_LOCAL_PATH;
$cpCfg['cp_common_path_alias']        = CP_COMMON_PATH_ALIAS;
$cpCfg['cp_master_path_alias']        = CP_MASTER_PATH_ALIAS;
$cpCfg['cp_local_path_alias']         = CP_LOCAL_PATH_ALIAS;
$cpCfg['cp_lib_path_common']          = CP_LIB_PATH_COMMON;
$cpCfg['cp_lib_path']                 = CP_LIB_PATH;
$cpCfg['cp_lib_path_local']           = CP_LIB_PATH_LOCAL;
$cpCfg['cp.pageNavLinksCount'] = '5'; //no. of page links in the pager panel

$cpCfg['cp_modules_path_common']      = CP_MODULES_PATH_COMMON;
$cpCfg['cp_modules_path']             = CP_MODULES_PATH;
$cpCfg['cp_modules_path_local']       = CP_MODULES_PATH_LOCAL;

$cpCfg['cp_modules_path_common_alias']= CP_MODULES_PATH_COMMON_ALIAS;
$cpCfg['cp_modules_path_alias']       = CP_MODULES_PATH_ALIAS;
$cpCfg['cp_modules_path_local_alias'] = CP_MODULES_PATH_LOCAL_ALIAS;

$cpCfg['cp_widgets_path_common']      = CP_WIDGETS_PATH_COMMON;
$cpCfg['cp_widgets_path']             = CP_WIDGETS_PATH;
$cpCfg['cp_widgets_path_local']       = CP_WIDGETS_PATH_LOCAL;
$cpCfg['cp_widgets_path_common_alias']= CP_WIDGETS_PATH_COMMON_ALIAS;
$cpCfg['cp_widgets_path_alias']       = CP_WIDGETS_PATH_ALIAS;
$cpCfg['cp_widgets_path_local_alias'] = CP_WIDGETS_PATH_LOCAL_ALIAS;

$cpCfg['cp_plugins_path_common']      = CP_PLUGINS_PATH_COMMON;
$cpCfg['cp_plugins_path']             = CP_PLUGINS_PATH;
$cpCfg['cp_plugins_path_local']       = CP_PLUGINS_PATH_LOCAL;
$cpCfg['cp_plugins_path_common_alias']= CP_PLUGINS_PATH_COMMON_ALIAS;
$cpCfg['cp_plugins_path_alias']       = CP_PLUGINS_PATH_ALIAS;
$cpCfg['cp_plugins_path_local_alias'] = CP_PLUGINS_PATH_LOCAL_ALIAS;

$cpCfg['cp_themes_path_common']       = CP_THEMES_PATH_COMMON;
$cpCfg['cp_themes_path']              = CP_THEMES_PATH;
$cpCfg['cp_themes_path_local']        = CP_THEMES_PATH_LOCAL;
$cpCfg['cp_themes_path_common_alias'] = CP_THEMES_PATH_COMMON_ALIAS;
$cpCfg['cp_themes_path_alias']        = CP_THEMES_PATH_ALIAS;
$cpCfg['cp_themes_path_local_alias']  = CP_THEMES_PATH_LOCAL_ALIAS;

$cpCfg['cp_skins_path_common']        = CP_SKINS_PATH_COMMON;
$cpCfg['cp_skins_path']               = CP_SKINS_PATH;
$cpCfg['cp_skins_path_local']         = CP_SKINS_PATH_LOCAL;
$cpCfg['cp_skins_path_common_alias']  = CP_SKINS_PATH_COMMON_ALIAS;
$cpCfg['cp_skins_path_alias']         = CP_SKINS_PATH_ALIAS;
$cpCfg['cp_skins_path_local_alias']   = CP_SKINS_PATH_LOCAL_ALIAS;

return $cpCfg;