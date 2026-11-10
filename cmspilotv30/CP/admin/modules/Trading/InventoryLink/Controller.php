<?
class CP_Admin_Modules_Trading_InventoryLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    function getSavePortalFromEnquiry(){
        return $this->model->getSavePortalFromEnquiry();
    }

    function getSavePortalFromQuote(){
        return $this->model->getSavePortalFromQuote();
    }

    function getDetailPortal(){
        return $this->view->getDetailPortal();
    }
}
