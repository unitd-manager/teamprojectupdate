<?
class CP_Admin_Modules_EzTrade_RfqItemsLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    var $fieldsArray = array();

    //==================================================================//
    function getDetailPortal(){
        $formObj = Zend_Registry::get('formObj');
        $formObj->mode = 'detail';
        $text = $this->view->getEditPortal();

        return $text;
    }

    //==================================================================//
}