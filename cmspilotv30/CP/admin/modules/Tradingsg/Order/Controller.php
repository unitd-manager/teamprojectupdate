<?
class CP_Admin_Modules_Tradingsg_Order_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getUploadToDHL() {
        return $this->model->getUploadToDHL();
    }

    function getGenerateDHLLabel() {
        return $this->model->getGenerateDHLLabel();
    }

    function getAttachDHLLabelToOrder() {
        return $this->model->getAttachDHLLabelToOrder();
    }

    function getPrintDeliveryOrder() {
        return $this->view->getPrintDeliveryOrder();
    }

    function getPrintDeliveryOrderByProductGroup() {
        return $this->view->getPrintDeliveryOrderByProductGroup();
    }

    function getPrintOrderSummary() {
        return $this->view->getPrintOrderSummary();
    }

    function getPrintOrderSummaryExcel() {
        return $this->view->getPrintOrderSummaryExcel();    
    }

    function getPrintCaptainCopy() {
        return $this->view->getPrintCaptainCopy();
    }

    function getPrintCaptainCopy1() {
        return $this->view->getPrintCaptainCopy1();
    }

    function getGenerateInvoiceForm() {
        $modObj = getCPModuleObj('tradingsg_invoice');
        return $modObj->view->getGenerateInvoiceForm();
    }

    function getGenerateInvoiceFormSubmit() {
        $modObj = getCPModuleObj('tradingsg_invoice');
        return $modObj->model->getGenerateInvoiceFormSubmit();
    }

    function getEditInvoiceFormSubmit() {
        $modObj = getCPModuleObj('tradingsg_invoice');
        return $modObj->model->getEditInvoiceFormSubmit();
    }

    function getGenerateReceiptForm() {
        $modObj = getCPModuleObj('tradingsg_receipt');
        return $modObj->view->getGenerateReceiptForm();
    }

    function getGenerateReceiptFormSubmit() {
        $modObj = getCPModuleObj('tradingsg_receipt');
        return $modObj->model->getGenerateReceiptFormSubmit();
    }

    function getPopulateReceiptAmount() {
        return $this->model->getPopulateReceiptAmount();
    }

    function getPopulateInvoiceAmount() {
        return $this->model->getPopulateInvoiceAmount();
    }

    function getCancelInvoice() {
        return $this->model->getCancelInvoice();
    }

    function getCancelReceipt() {
        return $this->model->getCancelReceipt();
    }

    function getPrintInvoiceRecord() {
        return $this->view->getPrintInvoiceRecord();
    }

    function getPrintInvoiceRecordForPurchaseOrder() {
        return $this->view->getPrintInvoiceRecordForPurchaseOrder();
    }

    function getPrintReceipt() {
        return $this->view->getPrintReceipt();
    }

    function getTransporterInvoiceRecord() {
        return $this->view->getTransporterInvoiceRecord();
    }

    function getExtraInvoiceRecord() {
        return $this->view->getExtraInvoiceRecord();
    }

    function getEditInvoiceForm() {
        $modObj = getCPModuleObj('tradingsg_invoice');
        return $modObj->view->getEditInvoiceForm();
    }

    function getPrintProformaOrderItemInvoiceRecord() {
        return $this->view->getPrintProformaOrderItemInvoiceRecord();
    }

    function getSummaryInOrder () {
        return $this->view->getSummaryInOrder();
    }

}