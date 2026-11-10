<?
class CP_Www_Modules_LawNews_Reporter_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

    //==================================================================//
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $fnName = $fn->getFnNameByAction();
        $text = $this->$fnName();
        
        return $text;
    }
 
}