<?
class CP_Www_Modules_Ecommerce_RegisterProduct_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getController() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = '';
        if ($tv['secType'] == 'Register Product') {
            $text = $this->view->getNew();
        } 
        
        return $text;
    }

}