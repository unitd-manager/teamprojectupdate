<?
class CP_Admin_Modules_ManPower_Order_Controller extends CP_Common_Lib_ModuleControllerAbstract
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
        $modObj = getCPModuleObj('manPower_invoice');
        return $modObj->view->getGenerateInvoiceForm();
    }

    function getGenerateInvoiceFormSubmit() {
        $modObj = getCPModuleObj('manPower_invoice');
        return $modObj->model->getGenerateInvoiceFormSubmit();
    }

    function getEditInvoiceFormSubmit() {
        $modObj = getCPModuleObj('manPower_invoice');
        return $modObj->model->getEditInvoiceFormSubmit();
    }

    function getGenerateReceiptFormClient() {
        $modObj = getCPModuleObj('manPower_receipt');
        return $modObj->view->getGenerateReceiptFormClient();
    }

    function getGenerateReceiptFormSubmit() {
        $modObj = getCPModuleObj('manPower_receipt');
        return $modObj->model->getGenerateReceiptFormSubmit();
    }

    function getGenerateReceiptFormSubmitClient() {
        $modObj = getCPModuleObj('manPower_receipt');
        return $modObj->model->getGenerateReceiptFormSubmitClient();
    }

    function getGenerateReceiptFormSubmitCandidate() {
        $modObj = getCPModuleObj('manPower_receipt');
        return $modObj->model->getGenerateReceiptFormSubmitCandidate();
    }

    function getGenerateReceiptFormCandidate() {
        $modObj = getCPModuleObj('manPower_receipt');
        return $modObj->view->getGenerateReceiptFormCandidate();
    }

    function getGenerateReceiptFormReferral() {
        $modObj = getCPModuleObj('manPower_receipt');
        return $modObj->view->getGenerateReceiptFormReferral();
    }

    function getGenerateReceiptFormSubmitReferral() {
        $modObj = getCPModuleObj('manPower_receipt');
        return $modObj->model->getGenerateReceiptFormSubmitReferral();
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

    function getPrintInvoiceRecordReferral() {
        return $this->view->getPrintInvoiceRecordReferral();
    }

    function getReceiptPortalDisplayReferral() {
        return $this->view->getReceiptPortalDisplayReferral();
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
        $modObj = getCPModuleObj('manPower_invoice');
        return $modObj->view->getEditInvoiceForm();
    }

    function getPrintBill() {
        $modObj = getCPModuleObj('tradingsg_pos');
        return $modObj->view->getEditInvoiceForm();
    }

	function getSummaryInOrder () {
        return $this->view->getSummaryInOrder();
	}

    function getInvoicePortalDisplayDetail() {
        return $this->view->getInvoicePortalDisplayDetail();
    }

    function getInvoicePortalDisplayDetailCandidate() {
        return $this->view->getInvoicePortalDisplayDetailCandidate();
    }

    function getReceiptPortalDisplay() {
        return $this->view->getReceiptPortalDisplay();
    }

    function getReceiptPortalDisplayCandidate() {
        return $this->view->getReceiptPortalDisplayCandidate();
    }

    function getReferralInvoicePortalDisplayDetail() {
        return $this->view->getReferralInvoicePortalDisplayDetail();
    }

    function getPrintPayStub() {
        return $this->view->getPrintPayStub();
    }

    function getPrintPayStubReceipt() {
        return $this->view->getPrintPayStubReceipt();
    }

    function getGenerateEmpTaxForm() {
        $modObj = getCPModuleObj('manPower_invoice');
        return $modObj->view->getGenerateEmpTaxForm();
    }

    function getGenerateEmpTaxFormSubmit() {
        $modObj = getCPModuleObj('manPower_invoice');
        return $modObj->model->getGenerateEmpTaxFormSubmit();
    }

    function getGenerateInvoiceFormDetail() {
        $modObj = getCPModuleObj('manPower_invoice');
        return $modObj->view->getGenerateInvoiceFormDetail();
    }

    function getGenerateEmpTaxFormDetail() {
        $modObj = getCPModuleObj('manPower_invoice');
        return $modObj->view->getGenerateEmpTaxFormDetail();
    }

    function getGenerateReceiptFormEmployerTax() {
        $modObj = getCPModuleObj('manPower_receipt');
        return $modObj->view->getGenerateReceiptFormEmployerTax();
    }

    function getGenerateReceiptFormEmployerTaxSubmit() {
        $modObj = getCPModuleObj('manPower_receipt');
        return $modObj->model->getGenerateReceiptFormEmployerTaxSubmit();
    }

    function getEmployerTaxFormDetail() {
        $modObj = getCPModuleObj('manPower_receipt');
        return $modObj->view->getEmployerTaxFormDetail();
    }

    function getCancelTaxReceipt() {
        return $this->model->getCancelTaxReceipt();
    }

    function getEditReceiptFormCandidate() {
        $modObj = getCPModuleObj('manPower_receipt');
        return $modObj->view->getEditReceiptFormCandidate();
    }

    function getEditCandidateReceiptFormSubmit() {
        $modObj = getCPModuleObj('manPower_receipt');
        return $modObj->model->getEditCandidateReceiptFormSubmit();
    }

}