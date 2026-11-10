<?
abstract class CP_Common_Lib_WidgetViewAbstract
{
    var $controller = null;
    var $model = null;
    var $fns = null;
    var $rowsHTML = '';

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
        $widget = $this->controller->name;
        $cpPaths = Zend_Registry::get('cpPaths');
        $cpCfg = Zend_Registry::get('cpCfg');
        $viewHelper = Zend_Registry::get('viewHelper');

        $arr = explode('_', $widget);
        $widPart1 = ucfirst($arr[0]);
        $widPart2 = ucfirst($arr[1]);
        
        $folder = $widPart1 . '/' . $widPart2;

        if (!$cpCfg['cp.doNotLoadJSSFilesForWidgets']){
            $viewHelper->setJSSArrays('widgets', $folder, 'functions.js');
        }
        
        if($cpCfg['cp.cssFramework'] != 'bootstrap'){
            $viewHelper->setJSSArrays('widgets', $folder, 'style.css');
        }

        CP_Common_Lib_Registry::arrayMerge('jssKeys', $this->jssKeys);
    }

    /**
     *
     */
    function getRowsHTML() {
    }

}
