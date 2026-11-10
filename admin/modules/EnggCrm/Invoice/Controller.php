<?
class CPL_Admin_Modules_EnggCrm_Invoice_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    function getGenerateInvoiceForm() {
        return $this->view->getGenerateInvoiceForm();
    }

    
    function getGenerateInvoiceForm1() {
        return $this->view->getGenerateInvoiceForm1();
    }

    function getAddInvoiceItemRecord() {
        return $this->view->getAddInvoiceItemRecord();
    }

    function getAddInvoiceItemRecordDetail() {
        return $this->view->getAddInvoiceItemRecordDetail();
    }

    function getAddInvoiceItemRecordManpower() {
        return $this->view->getAddInvoiceItemRecordManpower();
    }

    function getGenerateInvoiceFormSubmit() {
        return $this->model->getGenerateInvoiceFormSubmit();
    }

    function getGenerateDetailInvoiceForm() {
        return $this->view->getGenerateDetailInvoiceForm();
    }

    function getEditInvoiceFormSubmit() {
        return $this->model->getEditInvoiceFormSubmit();
    }

    function getSendOutstandingEmailToAdmin() {
        return $this->model->getSendOutstandingEmailToAdmin();
    }

    function getPrintAgeingReport() {
        return $this->model->getPrintAgeingReport();
    }

    function getPrintStatementOfAccount() {
        return $this->model->getPrintStatementOfAccount();
    }

    function getGenerateCreditNoteForm() {
        return $this->view->getGenerateCreditNoteForm();
    }

    function getEditCreditNoteFormSubmit() {
        return $this->model->getEditCreditNoteFormSubmit();
    }

    function getGenerateCreditNoteFormSubmit() {
        return $this->model->getGenerateCreditNoteFormSubmit();
    }
}