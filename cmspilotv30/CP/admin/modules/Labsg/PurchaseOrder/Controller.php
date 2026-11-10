<?
class CP_Admin_Modules_Labsg_PurchaseOrder_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    function getAddMultipleLineItemSubmit() {
        return $this->model->getAddMultipleLineItemSubmit();
    }

    function getAddSingleLineItem() {
        return $this->view->getAddSingleLineItem();
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

}