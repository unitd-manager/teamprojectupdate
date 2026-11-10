<?
class CP_Www_Modules_Forex_RateBoard_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

    function __construct() {
        $tv = Zend_Registry::get('tv');

        $tv['pageCSSClass'] = 'hideboth';
        CP_Common_Lib_Registry::arrayMerge('tv', $tv);
    }

    //==================================================================//
    function getList() {
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        
        $list = parent::getList();
        $text = "
        {$list}
        ";
        
        return $text;
    }
}