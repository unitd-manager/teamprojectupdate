<?
class CP_Admin_Modules_EnggCrm_Receipt_Controller extends CP_Common_Lib_ModuleControllerAbstract
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
    function getGenerateReceiptFormSubmit() {
        return $this->model->getGenerateReceiptFormSubmit();
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
    function getGenerateReceiptForMedia() {
        return $this->model->getGenerateReceiptForMedia();
    }

    function getGenerateReceiptForm() {
        return $this->view->getGenerateReceiptForm();
    }

    function getPopulateReceiptAmount() {
        return $this->model->getPopulateReceiptAmount();
    }

    function getPopulateInvoiceAmount() {
        return $this->model->getPopulateInvoiceAmount();
    }

}