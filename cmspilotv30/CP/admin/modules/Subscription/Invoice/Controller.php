<?
class CP_Admin_Modules_Subscription_Invoice_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getRaiseInvoice() {
        return $this->model->getRaiseInvoice();
    }

    /**
     *
     */
    function getGenerateInvoiceForCurrentMonth() {
        return $this->model->getGenerateInvoiceForCurrentMonth();
    }
}