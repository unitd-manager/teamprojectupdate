<?
class CP_Admin_Modules_Pos_PurchaseOrder_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getEditInventoryForm() {
        return $this->view->getEditInventoryForm();
    }

    /**
     */
    function getSaveInventory() {
        return $this->model->getSaveInventory();
    }

    /**
     */
    function getValidateEditProductItemLink() {
        return $this->model->getValidateEditProductItemLink();
    }

    /**
     */
    function getPurchaseOrderItemSubmit() {
        return $this->model->getPurchaseOrderItemSubmit();
    }

    /**
     */
    function getPurchaseOrderItemValidate() {
        return $this->model->getPurchaseOrderItemValidate();
    }

    /**
     */
    function getPurchaseOrderItems(){
        return $this->view->getPurchaseOrderItems();
    }

    /**
     */
    function getInsertPurchaseOrderItems() {
        return $this->model->getInsertPurchaseOrderItems();
    }

    /**
     */
    function getDeletePurchaseOrderItem(){
        return $this->model->getDeletePurchaseOrderItem();
    }

    /**
     */
    function getUpdateSkuNoPurchaseOrderItem(){
        return $this->model->getUpdateSkuNoPurchaseOrderItem();
    }

    /**
     */
    function getUpdateVendorSkuNoPurchaseOrderItem(){
        return $this->model->getUpdateVendorSkuNoPurchaseOrderItem();
    }

    /**
     */
    function getUpdateQtyPurchaseOrderItem(){
        return $this->model->getUpdateQtyPurchaseOrderItem();
    }

    /**
     */
    function getUpdateUnitPricePurchaseOrderItem(){
        return $this->model->getUpdateUnitPricePurchaseOrderItem();
    }

    /**
     */
    function getUpdateDiscountPurchaseOrderItem(){
        return $this->model->getUpdateDiscountPurchaseOrderItem();
    }

    /**
     */
    function getUpdateOverallDiscountPurchaseOrder(){
        return $this->model->getUpdateOverallDiscountPurchaseOrder();
    }

    /**
     */
    function getTotalValues(){
        return $this->model->getTotalValues();
    }

    /**
     */
    function getPopulateVendorName(){
        return $this->model->getPopulateVendorName();
    }

    /**
     */
    function getPopulateStaffName(){
        return $this->model->getPopulateStaffName();
    }

    /**
     */
    function getPopulateShopName(){
        return $this->model->getPopulateShopName();
    }

    /**
     */
    function getPopulateWarehouseName(){
        return $this->model->getPopulateWarehouseName();
    }

    function getDeliveryUpdate() {
        return $this->model->getDeliveryUpdate();
    }

    function getOrderNoLocation() {
        return $this->model->getOrderNoLocation();
    }
}