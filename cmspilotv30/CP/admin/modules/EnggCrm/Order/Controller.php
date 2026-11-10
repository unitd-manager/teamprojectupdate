<?
class CP_Admin_Modules_EnggCrm_Order_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    function getPrintinvoice() {
        return $this->view->getPrintinvoice();
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
        $modObj = getCPModuleObj('enggCrm_invoice');
        return $modObj->view->getEditInvoiceForm();
    }

    function getPrintBill() {
        $modObj = getCPModuleObj('enggCrm_pos');
        return $modObj->view->getEditInvoiceForm();
    }

	function getSummaryInOrder () {
        return $this->view->getSummaryInOrder();
	}

    function getInvoiceCodeVatUpdate() {
        $modObj = getCPModuleObj('enggCrm_invoice');
        return $modObj->model->getInvoiceCodeVatUpdate();
    }

    function getGenerateFullInvoice() {
        $modObj = getCPModuleObj('enggCrm_invoice');
        return $modObj->view->getGenerateFullInvoice();
    }


    function getAutoGenerateMaintenanceInvoice() {
        $modObj = getCPModuleObj('enggCrm_invoice');
        return $modObj->model->getAutoGenerateMaintenanceInvoice();
    }

    function getAutoGenerateRentalInvoice() {
        $modObj = getCPModuleObj('enggCrm_invoice');
        return $modObj->model->getAutoGenerateRentalInvoice();
    }

    function getViewReceiptDetails() {
        $modObj = getCPModuleObj('enggCrm_receipt');
        return $modObj->view->getViewReceiptDetails();
    }

    function getGenerateDetailInvoiceOrderItem() {
        $modObj = getCPModuleObj('enggCrm_invoice');
        return $modObj->view->getGenerateDetailInvoiceOrderItem();
    }

    function getPrintinvoiceManpower() {
        return $this->view->getPrintinvoiceManpower();
    }

    function getPrintinvoiceManpowerLot() {
        return $this->view->getPrintinvoiceManpowerLot();
    }

    function getPrintinvoiceManpowerNormal() {
        return $this->view->getPrintinvoiceManpowerNormal();
    }

    function getUpdateProjectDetailsInOrder() {
        return $this->model->getUpdateProjectDetailsInOrder();
    }

    function getPrintCreditNote() {
        return $this->view->getPrintCreditNote();
    }

    function getInvoicePortalDisplay() {
        return $this->view->getInvoicePortalDisplay();
    }

    function getReceiptPortalDisplay() {
        return $this->view->getReceiptPortalDisplay();
    }
}