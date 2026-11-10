<?
class CPL_Admin_Widgets_EnggCrm_ProjectFinanceRenewal_Controller extends CP_Common_Lib_WidgetControllerAbstract
{
    function getInvoiceReceiptPortalDetails(){
        return $this->view->getInvoiceReceiptPortalDetails();
    }
	/**
    */
    function getFinanceSummaryLeftRows(){ 
        return $this->view->getFinanceSummaryLeftRows();
    }

    /**
    */
    function getGenerateOrderRecords(){ 
        return $this->model->getGenerateOrderRecords();
    }
	/**
    */
    function getFinanceSummaryRightRows(){
        return $this->view->getFinanceSummaryRightRows();
    }

    function getGenerateReceiptFormRenewal() {
        $modObj = getCPModuleObj('enggCrm_receipt');
        return $modObj->view->getGenerateReceiptFormRenewal();
    }


    function getGenerateReceiptFormSubmitRenewal() {
        $modObj = getCPModuleObj('enggCrm_receipt');
        return $modObj->view->getGenerateReceiptFormSubmitRenewal();
    }
}