<?
class CPL_Admin_Modules_Tradingsg_PurchaseOrder_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getProduct() {
        return $this->view->getProduct();
    }

    function getProductFormSubmit() {
        return $this->model->getProductFormSubmit();
    }

    function getProductValidate() {
        return $this->model->getProductValidate();
    }

    function getAddMultipleLineItem() {
        return $this->view->getAddMultipleLineItem();
    }

    function getAddMultipleLineItemList() {
        return $this->view->getAddMultipleLineItemList();
    }

    function getAddMultipleLineItemSubmit() {
        return $this->model->getAddMultipleLineItemSubmit();
    }

    function getAddMultipleLineItemListSubmit() {
        return $this->model->getAddMultipleLineItemListSubmit();
    }

    function getAddSingleLineItem() {
        return $this->view->getAddSingleLineItem();
    }

    function getAddSingleLineItemNew() {
        return $this->view->getAddSingleLineItemNew();
    }

    function getAddSingleLineItemList() {
        return $this->view->getAddSingleLineItemList();
    }

    function getAddSingleLineItemNewList() {
        return $this->view->getAddSingleLineItemNewList();
    }

    function getEditPoProductRecordForm() {
        return $this->view->getEditPoProductRecordForm();
    }

    function getEditPoProductRecordSubmit() {
        return $this->model->getEditPoProductRecordSubmit();
    }

    function getAddProductDetail() {
        return $this->view->getAddProductDetail();
    }

    function getAddProduct() {
        return $this->view->getAddProduct();
    }

    function getPreviousOrderForClient() {
        return $this->view->getPreviousOrderForClient();
    }

    function getLastQuotedPrice() {
        return $this->view->getLastQuotedPrice();
    }

    function getPrintPOtoExcel() {
        return $this->model->getPrintPOtoExcel();
    }

    function getPrintPOtoPDF() {
        return $this->model->getPrintPOtoPDF();
    }

    function getPrintPOwithpricetoPDF() {
        return $this->model->getPrintPOwithpricetoPDF();
    }

    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

    function getSearchMOLProductList() {
        return $this->model->getSearchMOLProductList();
    }

    function getStockDisplayByLocation(){
        return $this->view->getStockDisplayByLocation();
    }

    function getUpdateQtyDelivered() {
        return $this->model->getUpdateQtyDelivered();
    }

    function getDeletePoProduct() {
        return $this->model->getDeletePoProduct();
    }

    function getDuplicatePO() {
        return $this->model->getDuplicatePO();
    }

    function getAddNewProduct() {
        return $this->view->getAddNewProduct();
    }

    function getAddNewProductSubmit() {
        return $this->model->getAddNewProductSubmit();
    }

    function getAddNewProductList() {
        return $this->view->getAddNewProductList();
    }

    function getAddNewProductListSubmit() {
        return $this->model->getAddNewProductListSubmit();
    }
    function getNewSupplier() {
        return $this->view->getNewSupplier();
    }

    function getAddNewProductMaster() {
        return $this->view->getAddNewProductMaster();
    }

    function getAddNewProductMasterSubmit() {
        return $this->model->getAddNewProductMasterSubmit();
    }

    function getCreateDeliveryOrder() {
        return $this->model->getCreateDeliveryOrder();
    }

    function getDeliveryOrderPortal() {
        return $this->view->getDeliveryOrderPortal();
    }

    function getEditDeliveryOrder() {
        return $this->view->getEditDeliveryOrder();
    }

    function getEditForDOSubmit() {
        return $this->model->getEditForDOSubmit();
    }

    function getPrintDeliveryOrderPdf(){
        return $this->view->getPrintDeliveryOrderPdf();
    }
}