<?
$includePath = './' . PATH_SEPARATOR .
               CP_CORE_PATH . 'library' . PATH_SEPARATOR .
               CP_CORE_PATH . 'library/lib_php';

ini_set("include_path", $includePath);

include_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->suppressNotFoundWarnings(false);
