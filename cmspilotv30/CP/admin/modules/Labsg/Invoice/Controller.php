<?
class CP_Admin_Modules_Labsg_Invoice_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getGenerateInvoiceFormSubmit() {
        return $this->model->getGenerateInvoiceFormSubmit();
    }

    function getGenerateIndividualInvoiceFormSubmit() {
        return $this->model->getGenerateIndividualInvoiceFormSubmit();
    }

    /**
     *
     */
    function getCancelInvoiceForm() {
        return $this->view->getCancelInvoiceForm();
    }

    /**
     *
     */
    function getCancelInvoiceFormSubmit() {
        return $this->model->getCancelInvoiceFormSubmit();
    }
}