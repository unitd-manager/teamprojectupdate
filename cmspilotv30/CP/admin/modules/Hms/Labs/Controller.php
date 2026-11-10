<?
class CP_Admin_Modules_Hms_Labs_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getAddMultipleLineItem() {
        return $this->view->getAddMultipleLineItem();
    }

    function getAddMultipleLineItemSubmit() {
        return $this->model->getAddMultipleLineItemSubmit();
    }

    function getAddSingleLineItem() {
        return $this->view->getAddSingleLineItem();
    }

    function getLabsSupplierJSON() {
        return $this->model->getLabsSupplierJSON();
    }

    function getGenerateReceiptForm() {
        return $this->view->getGenerateReceiptForm();
    }

    function getCreateInvoiceLabs() {
        return $this->model->getCreateInvoiceLabs();
    }

    function getPopulateReceiptAmount(){
        return $this->model->getPopulateReceiptAmount();
    }

    function getGenerateReceiptFormSubmit(){
        return $this->model->getGenerateReceiptFormSubmit();
    }

    function getReceiptPortalDisplay(){
        return $this->view->getReceiptPortalDisplay();
    }

    function getCancelReceipt(){
        return $this->model->getCancelReceipt();
    }

    function getLabsDisplay(){
        return $this->view->getLabsDisplay();
    }
}