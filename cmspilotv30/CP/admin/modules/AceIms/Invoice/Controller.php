<?
class CP_Admin_Modules_AceIms_Invoice_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getRaiseInvoice() {
        return $this->model->getRaiseInvoice();
    }

    /**
     *
     */
    function getGenerateInvoiceForCurrentMonth() {
        return $this->model->getGenerateInvoiceForCurrentMonth();
    }

    /**
     *
     */
    function getEditInvoiceForm() {
        return $this->view->getEditInvoiceForm();
    }

    /**
     *
     */
    function getEditInvoiceFormSubmit() {
        return $this->model->getEditInvoiceFormSubmit();
    }

    /**
     *
     */
    function getViewInvoiceDetails() {
        return $this->view->getViewInvoiceDetails();
    }

    /**
     *
     */
    function getGenerateInvoiceFormSubmit() {
        return $this->model->getGenerateInvoiceFormSubmit();
    }

    /**
     *
     */
    function getCancelInvoice() {
        return $this->model->getCancelInvoice();
    }    
}