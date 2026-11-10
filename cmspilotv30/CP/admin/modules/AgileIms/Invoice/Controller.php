<?
class CP_Admin_Modules_AgileIms_Invoice_Controller extends CP_Common_Lib_ModuleControllerAbstract
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
    function getCancelInvoice() {
        return $this->model->getCancelInvoice();
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
     * Coming from Widget -> Ageing Report
     */
    function getPrintAgeingReport() {
        return $this->model->getPrintAgeingReport();
    }
}