<?
abstract class CP_Common_Lib_ModuleViewAbstract
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
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');

        $arr = explode('_', $module);
        $modPart1 = ucfirst($arr[0]);
        $modPart2 = ucfirst($arr[1]);

        $viewHelper->setJSSArrays('modules', $modPart1 . '/Lib', 'functions.js');

        if($cpCfg['cp.cssFramework'] != 'bootstrap'){
            $viewHelper->setJSSArrays('modules', $modPart1 . '/Lib', 'style.css');
        }

        if (!$cpCfg['cp.doNotLoadJSSFilesForModules']){
            $viewHelper->setJSSArrays('modules', $modPart1 . '/' . $modPart2, 'functions.js');
        }

        if($cpCfg['cp.cssFramework'] != 'bootstrap'){
            $viewHelper->setJSSArrays('modules', $modPart1 . '/' . $modPart2, 'style.css');
        }

        //dependent modules javascripts
        $depModulesForJSS = $modulesArr[$module]['depModulesForJSS'];
        foreach($depModulesForJSS as $moduleName){
            $arr = explode('_', $moduleName);
            $modPart1 = ucfirst($arr[0]);
            $modPart2 = ucfirst($arr[1]);

            if (!$cpCfg['cp.doNotLoadJSSFilesForModules']){
                $viewHelper->setJSSArrays('modules', $modPart1 . '/' . $modPart2, 'functions.js');
            }

            if($cpCfg['cp.cssFramework'] != 'bootstrap'){
                $viewHelper->setJSSArrays('modules', $modPart1 . '/' . $modPart2, 'style.css');
            }
        }

        CP_Common_Lib_Registry::arrayMerge('jssKeys', $this->jssKeys);
    }

    //==================================================================//
    function getDetail($row){
        $formObj = Zend_Registry::get('formObj');
        $formObj->mode = 'detail';
        return $this->getEdit($row);
    }
}
