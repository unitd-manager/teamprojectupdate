<?
class CPL_Admin_Modules_EnggCrm_Renewal_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

	 function getAddActualCharge(){
        return $this->view->getAddActualCharge();
    }

    function getPrintAcMaintenancePdf(){
        return $this->view->getPrintAcMaintenancePdf();
    }
    function getPrintElectricalPdf(){
        return $this->view->getPrintElectricalPdf();
    }

    function getAddServiceLineItemRecord(){
        return $this->view->getAddServiceLineItemRecord();
    }

    function getAddNewValuelistForm() {
        return $this->view->getAddNewValuelistForm();
    }

    function getAddServiceMultipleLineItem() {
        return $this->view->getAddServiceMultipleLineItem();
    }

    function getAddServiceMultipleLineItemSubmit() {
        return $this->model->getAddServiceMultipleLineItemSubmit();
    }


    function getAddShopMultipleLineItem() {
        return $this->view->getAddShopMultipleLineItem();
    }

    function getAddShopMultipleLineItemSubmit() {
        return $this->model->getAddShopMultipleLineItemSubmit();
    }

    function getAddNewValuelistFormSubmit() {
        return $this->model->getAddNewValuelistFormSubmit();
    }

    function getValueByValuelistJSON() {
        return $this->model->getValueByValuelistJSON();
    }

      function getPrintACElectricalPdf(){
        return $this->view->getPrintACElectricalPdf();
    }
      /**
    */
    function getAddQuoteFormSubmit() {
        return $this->model->getAddQuoteFormSubmit();
    }

    function getEditForQuoteSubmit() {
        return $this->model->getEditForQuoteSubmit();
    }

    function getEditForShopSubmit() {
        return $this->model->getEditForShopSubmit();
    }

    function getEditForQuote() {
        return $this->view->getEditForQuote();
    }

    function getEditForShop() {
        return $this->view->getEditForShop();
    }

      function getPrintAMCMEPPdf(){
        return $this->view->getPrintAMCMEPPdf();
    }

    function getPrintDrainFlushPdf(){
        return $this->view->getPrintDrainFlushPdf();
    }

    function getRenewalHistoryPortal(){
        return $this->view->getRenewalHistoryPortal();
    }

    function getActualChargeSubmit(){
        return $this->model->getActualChargeSubmit();
    }

    function getNewCompanyJSON(){
        return $this->model->getNewCompanyJSON();
    }

    function getaddMonthly(){
        return $this->model->getaddMonthly();
    }

    function getdeleteMonthly(){
        return $this->model->getdeleteMonthly();
    }

    function getaddQuaterly(){
        return $this->model->getaddQuaterly();
    }

    function getdeleteQuaterly(){
        return $this->model->getdeleteQuaterly();
    }

    function getaddAnually(){
        return $this->model->getaddAnually();
    }

     function getaddRemarks(){
        return $this->model->getaddRemarks();
    }

    function getdeleteAnually(){
        return $this->model->getdeleteAnually();
    }
    
    function getAddRenewalDate(){
        return $this->view->getAddRenewalDate();
    }

    function getRenewalDateSubmit(){
        return $this->model->getRenewalDateSubmit();
    }
    function getAddService(){
        return $this->view->getAddService();
    }

    function getServiceSubmit(){
        return $this->model->getServiceSubmit();
    }

    function getDeleteShopRenewal(){
        return $this->model->getDeleteShopRenewal();
    }
}