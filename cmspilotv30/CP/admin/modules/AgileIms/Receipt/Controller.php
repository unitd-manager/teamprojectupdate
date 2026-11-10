<?
class CP_Admin_Modules_AgileIms_Receipt_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getGenerateReceiptForm() {
        return $this->view->getGenerateReceiptForm();
    }

    /**
     *
     */
    function getGenerateReceiptFormSubmit() {
        return $this->model->getGenerateReceiptFormSubmit();
    }    

    /**
     *
     */
    function getCancelReceipt() {
        return $this->model->getCancelReceipt();
    }

    /**
     *
     */
    function getEditReceiptForm() {
        return $this->view->getEditReceiptForm();
    }

    /**
     *
     */
    function getEditReceiptFormSubmit() {
        return $this->model->getEditReceiptFormSubmit();
    }

    /**
     *
     */
    function getGenerateMiscReceiptForm() {
        return $this->view->getGenerateMiscReceiptForm();
    }

    /**
     *
     */
    function getGenerateMiscReceiptFormSubmit() {
        return $this->model->getGenerateMiscReceiptFormSubmit();
    }

}