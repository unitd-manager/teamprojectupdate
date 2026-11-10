<?
class CPL_Admin_Modules_Payroll_Expense_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
	function getAddNewValuelistForm() {
        return $this->view->getAddNewValuelistForm();
    }

    function getNewAdd() {
        return $this->model->getNewAdd();
    }

    function getNewList() {
        return $this->view->getNewList();
    }

    function getAddNewValuelistFormSubmit() {
        return $this->model->getAddNewValuelistFormSubmit();
    }

    function getValueByValuelistJSON() {
        return $this->model->getValueByValuelistJSON();
    }

    function getGroupByTypeJSON(){
        return $this->model->getGroupByTypeJSON();
    }

    function getSubgroupByGroupJSON(){
        return $this->model->getSubgroupByGroupJSON();
    }

    function getExpSubgroupByGroupJSON(){
        return $this->model->getExpSubgroupByGroupJSON();
    }

    function getIncomeSubgroupByGroupJSON(){
        return $this->model->getIncomeSubgroupByGroupJSON();
    }

    function getCalculateTotalAmt(){
        return $this->model->getCalculateTotalAmt();
    }

    function getCalculateTotalAmtWithServiceCharge(){
        return $this->model->getCalculateTotalAmtWithServiceCharge();
    }

    function getGenerateReceiptForm(){
        return $this->view->getGenerateReceiptForm();
    }

    function getGenerateReceiptFormSubmit(){
        return $this->model->getGenerateReceiptFormSubmit();
    }

    function getGeneratePaymentForm(){
        return $this->view->getGeneratePaymentForm();
    }

    function getGeneratePaymentFormSubmit(){
        return $this->model->getGeneratePaymentFormSubmit();
    }

    function getEditReceiptForm(){
        return $this->view->getEditReceiptForm();
    }

    function getEditReceiptFormSubmit(){
        return $this->model->getEditReceiptFormSubmit();
    }

    function getSearchSupplierName(){
        return $this->model->getSearchSupplierName();
    }

    function getPaymentPortalDisplay(){
        return $this->view->getPaymentPortalDisplay();
    }
}