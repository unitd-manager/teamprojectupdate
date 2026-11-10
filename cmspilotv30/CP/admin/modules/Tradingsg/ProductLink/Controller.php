<?
class CP_Admin_Modules_Tradingsg_ProductLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    function getSavePortalFromEnquiry(){
        return $this->model->getSavePortalFromEnquiry();
    }

    function getSavePortalFromRfq(){
        return $this->model->getSavePortalFromRfq();
    }

    function getSavePortalFromQuote(){
        return $this->model->getSavePortalFromQuote();
    }

    function getSavePortalFromSO(){
        return $this->model->getSavePortalFromSO();
    }

    function getSavePortalFromPO(){
        return $this->model->getSavePortalFromPO();
    }

    function getSavePortalFromShipment(){
        return $this->model->getSavePortalFromShipment();
    }

    function getSavePortalFromInvoice(){
        return $this->model->getSavePortalFromInvoice();
    }

    function getDetailPortal(){
        return $this->view->getDetailPortal();
    }    
}
