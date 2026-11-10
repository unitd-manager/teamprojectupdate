<?
class CP_Admin_Modules_Project_Invoice_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getRaiseInvoice() {
        return $this->model->getRaiseInvoice();
    }

    function getPrintInvoiceList() {
        return $this->model->getPrintInvoiceList();
    }

    function getSendReminderEmail() {
        return $this->view->getSendReminderEmail();
    }

    function getSendReminderEmailSubmit() {
        return $this->model->getSendReminderEmailSubmit();
    }

    function getPrintSubscriptionPdf() {
        return $this->model->getPrintSubscriptionPdf();
    }

}