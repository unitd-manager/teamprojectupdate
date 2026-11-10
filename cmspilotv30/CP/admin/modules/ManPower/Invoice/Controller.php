<?
class CP_Admin_Modules_ManPower_Invoice_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getRaiseInvoice() {
        return $this->model->getRaiseInvoice();
    }

    function getInvoiceNoItemsPrintToFpdf() {
        return $this->model->getInvoiceNoItemsPrintToFpdf();
    }
}