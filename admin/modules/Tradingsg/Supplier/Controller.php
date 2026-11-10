<?
class CPL_Admin_Modules_Tradingsg_Supplier_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getCreateLoginForm() {
        return $this->view->getCreateLoginForm();
    }

    function getCreateLoginFormSubmit() {
        return $this->model->getCreateLoginFormSubmit();
    }

    function getGeneratePurchaseOrderForm() {
        return $this->view->getGeneratePurchaseOrderForm();
    }

    function getGeneratePurchaseOrderFormSubmit() {
        return $this->model->getGeneratePurchaseOrderFormSubmit();
    }

    function getGeneratePurchaseOrderFormValidate() {
        return $this->model->getGeneratePurchaseOrderFormValidate();
    }

    function getPopulatePOAmount() {
        return $this->model->getPopulatePOAmount();
    }

    function getNewSupplier() {
        return $this->view->getNewSupplier();
    }
    function getAddSupplier() {
        return $this->model->getAddSupplier();
    }
    function getSupplierList(){
        return $this->model->getSupplierList();
    }
    function getReceiptHistoryForSupplier() {
        return $this->view->getReceiptHistoryForSupplier();
    }

    function getCancelSupplierReceipt() {
        return $this->model->getCancelSupplierReceipt();
    }
}