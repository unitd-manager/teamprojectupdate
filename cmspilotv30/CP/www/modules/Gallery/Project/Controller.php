<?
class CP_Www_Modules_Gallery_Project_Controller extends CP_Common_Modules_Gallery_Project_Controller
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