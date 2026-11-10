<?
class CPL_Admin_Widgets_EnggCrm_ProjectPurchaseOrder_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
	/**
    */
    function getPrintpurchaseorder(){
        return $this->view->getPrintpurchaseorder();
    }

    /**
    */
    function getAddMultiplePurchaseOrder(){
        return $this->view->getAddMultiplePurchaseOrder();
    }

    /**
    */
    function getAddSinglePurchaseOrderRecord(){
        return $this->view->getAddSinglePurchaseOrderRecord();
    }

    /**
    */
    function getAddMultiplePurchaseOrderSubmit(){
        return $this->model->getAddMultiplePurchaseOrderSubmit();
    }

    /**
    */
    function getCancelPoItem(){
        return $this->model->getCancelPoItem();
    }

    /**
    */
    function getPurchaseOrderPortal() {
        return $this->view->getPurchaseOrderPortal();
    }

    /**
    */
    function getCreationModificationPo() {
        return $this->model->getCreationModificationPo();
    }

    /**
    */
    function getEditPoMultipleLineItem(){
        return $this->view->getEditPoMultipleLineItem();
    }

    /**
    */
    function getEditMultiplePurchaseOrderSubmit(){
        return $this->model->getEditMultiplePurchaseOrderSubmit();
    }

    /**
    */
    function getUpdateQtyDelivered(){
        return $this->model->getUpdateQtyDelivered();
    }

    /**
    */
    function getEditPoLineItem() {
        return $this->view->getEditPoLineItem();
    }

    /**
    */
    function getEditPoLineItemSubmit() {
        return $this->model->getEditPoLineItemSubmit();
    }

    /**
    */
    function getEditForPo() {
        return $this->view->getEditForPo();
    }

    /**
    */
    function getEditForPoSubmit() {
        return $this->model->getEditForPoSubmit();
    }

    /**
    */
    function getTransferToOtherPO() {
        return $this->model->getTransferToOtherPO();
    }

    /**
    */
    function getUpdateQtyStockTransfer() {
        return $this->model->getUpdateQtyStockTransfer();
    }

    /**
    */
    function getAddNewProductMaster() {
        return $this->view->getAddNewProductMaster();
    }

    /**
    */
    function getAddNewProductMasterSubmit() {
        return $this->model->getAddNewProductMasterSubmit();
    }

    /**
    */
    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

    /**
    */
    function getCreateStockTransfer() {
        return $this->model->getCreateStockTransfer();
    }

    /**
    */
    function getProjectByCompanyJSON() {
        return $this->model->getProjectByCompanyJSON();
    }

    /**
    */
    function getSearchClientName() {
        return $this->model->getSearchClientName();
    }

    /**
    */
    function getSearchProjectTitle() {
        return $this->model->getSearchProjectTitle();
    }

    /**
    */
    function getAddMultipleMaterialRequest() {
        return $this->view->getAddMultipleMaterialRequest();
    }

    /**
    */
    function getAddSingleMaterialsRequestRecord(){
        return $this->view->getAddSingleMaterialsRequestRecord();
    }

    /**
    */
    function getAddMultipleMaterialRequestSubmit(){
        return $this->model->getAddMultipleMaterialRequestSubmit();
    }

    /**
    */
    function getMaterialRequesPortal(){
        return $this->view->getMaterialRequesPortal();
    }

    /**
    */
    function getEditForMaterialsRequest(){
        return $this->view->getEditForMaterialsRequest();
    }

    /**
    */
    function getEditForMaterialsRequestSubmit(){
        return $this->model->getEditForMaterialsRequestSubmit();
    }

    /**
    */
    function getEditMRMultipleLineItem(){
        return $this->view->getEditMRMultipleLineItem();
    }

    /**
    */
    function getEditMultipleMaterialRequestSubmit(){
        return $this->model->getEditMultipleMaterialRequestSubmit();
    }

    /**
    */
    function getPrintMaterialsRequest(){
        return $this->view->getPrintMaterialsRequest();
    }

    /**
    */
    function getCreationModificationMR(){
        return $this->model->getCreationModificationMR();
    }

    /**
    */
    function getUpdateMaterialSupplierConfirmStatus(){
        return $this->model->getUpdateMaterialSupplierConfirmStatus();
    }

    /**
    */
    function getApproveMaterialRequestByAdmin(){
        return $this->model->getApproveMaterialRequestByAdmin();
    }

    function getAddNewSupplier(){
        return $this->view->getAddNewSupplier();
    }

    function getAddNewSupplierSubmit(){
        return $this->model->getAddNewSupplierSubmit();
    }

    function getSupplierByJSON(){
        return $this->model->getSupplierByJSON();
    }
}