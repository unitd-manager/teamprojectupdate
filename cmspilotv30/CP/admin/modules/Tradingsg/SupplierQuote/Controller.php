<?
class CP_Admin_Modules_Tradingsg_SupplierQuote_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSupplierJsonByProductId() {
        return $this->model->getSupplierJsonByProductId();
    }

    function getUpdateTotalCostPrice() {
        return $this->model->getUpdateTotalCostPrice();
    }

    function getRaisePurchaseOrder() {
        return $this->model->getRaisePurchaseOrder();
    }

    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

    function getPrintPurchaseOrder() {
        return $this->view->getPrintPurchaseOrder();
    }

    function getPrintPurchaseOrderWithPrice() {
        return $this->view->getPrintPurchaseOrderWithPrice();
    }

    function getPurchaseOrderViewDetail() {
        return $this->view->getPurchaseOrderViewDetail();
    }

    function getUpdateQtyDelivered() {
        return $this->model->getUpdateQtyDelivered();
    }

    function getUpdateSupplierProductLineItems() {
        return $this->model->getUpdateSupplierProductLineItems();
    }

    function getProductViewHistory() {
        return $this->view->getProductViewHistory();
    }

    function getPrintExcelSupplierQuote() {
        return $this->view->getPrintExcelSupplierQuote();
    }

    function getAddProductForm() {
        return $this->view->getAddProductForm();
    }

    function getAddProductFormSubmit() {
        return $this->model->getAddProductFormSubmit();
    }

}