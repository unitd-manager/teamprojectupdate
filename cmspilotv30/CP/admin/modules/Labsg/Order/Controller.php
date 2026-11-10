<?
class CP_Admin_Modules_Labsg_Order_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    function getPrintCaptainCopy() {
        return $this->view->getPrintCaptainCopy();
    }

    function getPrintCaptainCopy1() {
        return $this->view->getPrintCaptainCopy1();
    }

    function getGenerateSalesReturnForm() {
        return $this->view->getGenerateSalesReturnForm();
    }

    function getGenerateSalesReturnFormSubmit() {
        return $this->model->getGenerateSalesReturnFormSubmit();
    }

    function getPrintSalesReturn() {
        return $this->view->getPrintSalesReturn();
    }

    function getGenerateInvoiceForm() {
        $modObj = getCPModuleObj('labsg_invoice');
        return $modObj->view->getGenerateInvoiceForm();
    }

    function getGenerateInvoiceFormSubmit() {
        $modObj = getCPModuleObj('labsg_invoice');
        return $modObj->model->getGenerateInvoiceFormSubmit();
    }

    function getGenerateIndividualInvoiceFormSubmit() {
        $modObj = getCPModuleObj('labsg_invoice');
        return $modObj->model->getGenerateIndividualInvoiceFormSubmit();
    }

    function getEditInvoiceFormSubmit() {
        $modObj = getCPModuleObj('labsg_invoice');
        return $modObj->model->getEditInvoiceFormSubmit();
    }

    function getGenerateReceiptForm() {
        $modObj = getCPModuleObj('labsg_receipt');
        return $modObj->view->getGenerateReceiptForm();
    }

    function getGenerateReceiptFormSubmit() {
        $modObj = getCPModuleObj('labsg_receipt');
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

    function getPrintDeliveryInvoiceRecord() {
        return $this->view->getPrintDeliveryInvoiceRecord();
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
        $modObj = getCPModuleObj('labsg_invoice');
        return $modObj->view->getEditInvoiceForm();
    }

    function getPrintBill() {
        $modObj = getCPModuleObj('tradingsg_pos');
        return $modObj->view->getEditInvoiceForm();
    }

	function getSummaryInOrder () {
        return $this->view->getSummaryInOrder();
	}

    function getInvoiceCodeVatUpdate() {
        $modObj = getCPModuleObj('labsg_invoice');
        return $modObj->model->getInvoiceCodeVatUpdate();
    }

    function getGenerateFullInvoice() {
        $modObj = getCPModuleObj('labsg_invoice');
        return $modObj->view->getGenerateFullInvoice();
    }

    function getEmployeeSubmit() {
        return $this->model->getEmployeeSubmit();
    }

    function getPatientInvoiceSubmit() {
        return $this->model->getPatientInvoiceSubmit();
    }

    function getPrintYearWiseInvoiceRecord() {
        return $this->view->getPrintYearWiseInvoiceRecord();
    }
}