<?
abstract class CP_Common_Lib_WidgetModelAbstract
{
    var $controller = null;
    var $view = null;
    var $fns = null;
    var $dataArray = array();
    var $searchVar = null; //for widgets the global searchVar is not used. This one is used.

    function __construct() {
        $searchVar = includeCPClass('Lib', 'SearchVar', 'SearchVar');
        $this->searchVar = $searchVar;
    }

    /**
     *
     */
    function getSQL(){
    }

    /**
     *
     */
    function getDataArray() {
    }
}
