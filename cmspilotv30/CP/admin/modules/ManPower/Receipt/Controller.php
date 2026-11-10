<?
class CP_Admin_Modules_ManPower_Receipt_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getGenerateReceiptFormFromInvoice() {
        return $this->view->getGenerateReceiptFormFromInvoice();
    }

    /**
     *
     */
    function getGenerateReceiptFormFromInvoiceSubmit() {
        return $this->model->getGenerateReceiptFormFromInvoiceSubmit();
    }

    /**
     *
     */
    function getEditReceiptFormFromInvoice() {
        return $this->view->getEditReceiptFormFromInvoice();
    }

    /**
     *
     */
    function getEditReceiptFormFromInvoiceSubmit() {
        return $this->model->getEditReceiptFormFromInvoiceSubmit();
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
    function getGenerateReceiptFormSubmitClient() {
        return $this->model->getGenerateReceiptFormSubmitClient();
    }

    /**
     *
     */
    function getGenerateReceiptFormSubmitCandidate() {
        return $this->model->getGenerateReceiptFormSubmitCandidate();
    }

    /**
     *
     */
    function getGenerateReceiptForMedia() {
        return $this->model->getGenerateReceiptForMedia();
    }

    /**
     *
     */
    function getGenerateReceiptFormValidate() {
        return $this->model->getGenerateReceiptFormValidate();
    }
}