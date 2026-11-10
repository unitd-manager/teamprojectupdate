<?
class CP_Admin_Modules_Tradingsg_PurchaseOrder_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getEditInventoryForm() {
        return $this->view->getEditInventoryForm();
    }
    function getSaveInventory() {
        return $this->model->getSaveInventory();
    }

    //-----------------------//
    function getValidateEditProductItemLink() {
        return $this->model->getValidateEditProductItemLink();
    }

    function getRaiseInvoiceForm() {
        return $this->view->getRaiseInvoiceForm();
    }

    function getRaiseInvoiceFormSubmit() {
        return $this->model->getRaiseInvoiceFormSubmit();
    }

    function getEditInvoiceForm() {
        return $this->view->getEditInvoiceForm();
    }

    function getEditInvoiceFormSubmit() {
        return $this->model->getEditInvoiceFormSubmit();
    }

    function getPopulateReceiptAmount() {
        return $this->model->getPopulateReceiptAmount();
    }

    function getPrintPurchaseOrder() {
        return $this->view->getPrintPurchaseOrder();
    }

}