<?
$start = (float) array_sum(explode(' ',microtime()));
/*** Retrived config variables which were set in the /conf.php  ***/
$cpCfg = Zend_Registry::get('cpCfg');

/*** global scope functions (mainly includeCPClass) ***/
require_once("{$cpCfg['cp.coreCommonFolder']}lib/functions_global_scope.php");

/*** another set of global functions which are specific to either admin or www **/
require_once("{$cpCfg['cp.masterPath']}lib/functions_global_scope.php");

/*** Get the bootstrap object located in /CP/Common/lib ***/
require_once("{$cpCfg['cp.coreCommonFolder']}lib/Bootstrap.php");
$bootstrapObj = includeCPClass('Lib', 'Bootstrap');

$bootstrapObj->startSession();
$bootstrapObj->startFirePhpForDebugging();
$bootstrapObj->initializeCfgObj();
$bootstrapObj->connectToDb();
$bootstrapObj->initializeCommonClassObjects();
$bootstrapObj->setHTTPProtocol();
$bootstrapObj->setCSRFToken();
$bootstrapObj->initializeArrays();
$bootstrapObj->addSettingsDataToCfg();
$bootstrapObj->includeConfigFiles();
$bootstrapObj->loadTheLastConfigFile();
$bootstrapObj->initializeModules();
$bootstrapObj->initializeWidgets();
$bootstrapObj->initializePlugins();

$bootstrapObj->initializeCommonClassObjects2();
$bootstrapObj->initializeCommonClassObjects3();
$bootstrapObj->setNavigationArraysAdmin();
$bootstrapObj->initializeLinkObjects();
$bootstrapObj->initializeMediaArray();
$bootstrapObj->loadTheOverrideFiles();
$bootstrapObj->setCurrentTheme();

if (!isLoggedInAdmin()){
    $login = getCPPluginObj('common_login');
    $login->loginWithCookie();
}

$bootstrapObj->checkAccess();
$bootstrapObj->outputTheTemplateAdmin();

//$dbObj = Zend_Registry::get('db');
//print $dbObj->overallQueryTime;
$end = (float) array_sum(explode(' ',microtime()));
//print "<hr><br>Page Processing time: ". sprintf("%.4f", ($end-$start))." seconds";
