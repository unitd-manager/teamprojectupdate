<?
class CP_Admin_Modules_Payroll_PayrollManagement_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

	function getUpdateRecords() {
        return $this->model->getUpdateRecords();
    }

	function getUpdateRecordsValidate() {
        return $this->model->getUpdateRecordsValidate();
    }

	function getUpdateOverTimeAmount() {
        return $this->model->getUpdateOverTimeAmount();
    }

	function getpayslipprintPdf() {
        return $this->model->getpayslipprintPdf();
    }

    function getPrintPayslipForm() {
        return $this->view->getPrintPayslipForm();
    }

    function getPrintPayslipFormSubmit() {
        return $this->model->getPrintPayslipFormSubmit();
    }

    function getPrintPaySlipForAllPdf() {
        return $this->view->getPrintPaySlipForAllPdf();
    }

    function getCPFCalculatorValueUpdate() {
        return $this->model->getCPFCalculatorValueUpdate();
    }

    function getEditLoanPaymentHistory() {
        return $this->view->getEditLoanPaymentHistory();
    }

    function getEditLoanPaymentHistorySubmit() {
        return $this->model->getEditLoanPaymentHistorySubmit();
    }

    function getPrintIr8aForm() {
        return $this->model->getPrintIr8aForm();
    }

    function getPrintIr8aFormInPdf() {
        return $this->model->getPrintIr8aFormInPdf();
    }

    function getTerminatingEmployeeListForm() {
        return $this->view->getTerminatingEmployeeListForm();
    }

    function getPayslipFormSubmitForTerminatingEmployees() {
        return $this->model->getPayslipFormSubmitForTerminatingEmployees();
    }

    function getFindage() {
        return $this->model->getFindage();
    }

    function getGenerateAisTxtFile() {
        return $this->model->getGenerateAisTxtFile();
    }

    function getSentPaySlipToEmployee() {
        return $this->model->getSentPaySlipToEmployee();
    }

    function getApprovedEmployeesName() {
        return $this->model->getApprovedEmployeesName();
    }
}