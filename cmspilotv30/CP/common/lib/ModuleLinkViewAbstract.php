<?
abstract class CP_Common_Lib_ModuleLinkViewAbstract
{
    var $controller = null;
    var $model = null;
    var $fns = null;

    var $jssKeys = array();
    var $jsFilesArr = array();
    var $jsFilesArrLocal = array();
    var $jsFilesArrLast = array();

    var $cssFilesArr = array();
    var $cssFilesArrLocal = array();
    var $cssFilesArrLast = array();
    var $cssPatchFilesArr = array();

    /**
     *
     */
    function includeJSSFiles() {
        $module = $this->controller->name;
        $cpPaths = Zend_Registry::get('cpPaths');
        $viewHelper = Zend_Registry::get('viewHelper');

        $arr = explode('_', $module);
        $modPart1 = ucfirst($arr[0]);
        $modPart2 = ucfirst($arr[1]);

        $viewHelper->setJSSArrays('modules', $modPart1 . '/Lib', 'functions.js');
        $viewHelper->setJSSArrays('modules', $modPart1 . '/Lib', 'style.css');

        $viewHelper->setJSSArrays('modules', $modPart1 . '/' . $modPart2, 'functions.js');
        $viewHelper->setJSSArrays('modules', $modPart1 . '/' . $modPart2, 'style.css');

        CP_Common_Lib_Registry::arrayMerge('jssKeys', $this->jssKeys);
    }

    //==================================================================//
    function getQuickSearch() {
        $tv = Zend_Registry::get('tv');
        $mainModule = substr($tv['lnkRoom'], 0, strpos($tv['lnkRoom'], 'Link'));
        $modObj = getCPModuleObj($mainModule);

        if (method_exists($modObj->view, 'getQuickSearchLink')) {
        	return $modObj->view->getQuickSearchLink();
        }

        if (method_exists($modObj->view, 'getQuickSearch')) {
        	return $modObj->view->getQuickSearch();
        }
    }
}
