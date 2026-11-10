<?
abstract class CP_Common_Lib_PluginModelAbstract
{
    var $controller = null;
    var $view = null; 
    var $fns = null; 
    var $dataArray;
    var $searchVar = null; //for plugins the global searchVar is not used. This one is used.

    function __construct() {
        $searchVar = includeCPClass('Lib', 'SearchVar', 'SearchVar');
        $this->searchVar = $searchVar;
    }
    
    /**
     *
     */
    function getDataArray() {
    }
}
