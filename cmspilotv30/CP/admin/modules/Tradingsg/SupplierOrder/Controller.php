<?
class CP_Admin_Modules_Tradingsg_SupplierOrder_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getPORelatedProducts() {
        return $this->view->getPORelatedProducts();
    }

    function getCreateSOHistoryRecord() {
        return $this->model->getCreateSOHistoryRecord();
    }

    function getDeleteSupplierHistoryRecord() {
        return $this->model->getDeleteSupplierHistoryRecord();
    }

    function getPrintPurchaseOrder() {
        return $this->view->getPrintPurchaseOrder();
    }

    function getProductPortalDisplay() {
        return $this->view->getProductPortalDisplay();
    }
}