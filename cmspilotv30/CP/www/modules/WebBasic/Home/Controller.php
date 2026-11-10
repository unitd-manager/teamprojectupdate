<?
class CP_Www_Modules_WebBasic_Home_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function __construct() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $tv['pageCSSClassTop'] = "home";
        CP_Common_Lib_Registry::arrayMerge('tv', $tv);
    }

    /**
     *
     */
    function getFacebookShareContent() {
        return $this->view->getFacebookShareContent();
    }
}