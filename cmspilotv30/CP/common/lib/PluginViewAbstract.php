<?
abstract class CP_Common_Lib_PluginViewAbstract
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
        $plugin = $this->controller->name;
        $cpPaths = Zend_Registry::get('cpPaths');
        $cpCfg = Zend_Registry::get('cpCfg');

        $viewHelper = Zend_Registry::get('viewHelper');

        $arr = explode('_', $plugin);
        $plPart1 = ucfirst($arr[0]);
        $plPart2 = ucfirst($arr[1]);
        
        $folder = $plPart1 . '/' . $plPart2;
        
        if (!$cpCfg['cp.doNotLoadJSSFilesForWidgets']){
            $viewHelper->setJSSArrays('plugins', $folder, 'functions.js');
        }
        
        if($cpCfg['cp.cssFramework'] != 'bootstrap'){
            $viewHelper->setJSSArrays('plugins', $folder, 'style.css');
        }

        CP_Common_Lib_Registry::arrayMerge('jssKeys', $this->jssKeys);
    }

}
