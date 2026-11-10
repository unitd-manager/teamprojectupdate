<?
$start = (float) array_sum(explode(' ',microtime()));
/*** Retrieved config variables which were set in the /conf.php  ***/
$cpCfg = Zend_Registry::get('cpCfg');

/*** global scope functions (mainly includeCPC("{lass) ***/
require_once("{$cpCfg['cp.coreCommonFolder']}lib/functions_global_scope.php");

/*** another set of global functions which are specific to www only **/
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
$bootstrapObj->setSiteIdInCfgForMultiSite();
$bootstrapObj->initializeArrays();
$bootstrapObj->addSettingsDataToCfg();
$bootstrapObj->includeConfigFiles();
$bootstrapObj->setRedirections();
$bootstrapObj->initializeModules();
$bootstrapObj->initializeWidgets();
$bootstrapObj->initializePlugins();
$bootstrapObj->initializeCommonClassObjects2();
$bootstrapObj->initializeCommonClassObjects3();
$bootstrapObj->setNavigationArraysWww();
$bootstrapObj->setHTTPSRedirections();
$bootstrapObj->setHTTPRedirectionFromHTTPS();
$bootstrapObj->setCurrentModuleWww();
$bootstrapObj->setArraysByPageWww();
$bootstrapObj->initializeLinkObjects();
$bootstrapObj->loadTheLastConfigFile();
$bootstrapObj->initializeMediaArray();
$bootstrapObj->loadTheOverrideFiles();
$bootstrapObj->setCurrentTheme();
$bootstrapObj->outputTheTemplateWww();
$bootstrapObj->setReferrerUrl();

// $end = (float) array_sum(explode(' ',microtime()));
// $dbObj = Zend_Registry::get('db');
// FB::log($dbObj->overallQueryTime);
// FB::log("<hr><br>Page Processing time: ". sprintf("%.4f", ($end-$start))." seconds");
// print $dbObj->overallQueryTime . "<hr>";
// print "<hr><br>Page Processing time: ". sprintf("%.4f", ($end-$start))." seconds";
// exit();
