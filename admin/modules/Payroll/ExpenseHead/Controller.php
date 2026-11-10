<?
class CPL_Admin_Modules_Payroll_ExpenseHead_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
	function getAddExpenseSubHead() {
        return $this->view->getAddExpenseSubHead();
    }

    function getExpenseSubHeadDetail() {
        return $this->view->getExpenseSubHeadDetail();
    }

    function getExpenseSubHeadFormSubmit() {
        return $this->model->getExpenseSubHeadFormSubmit();
    }

    function getEditExpenseSubHead() {
        return $this->view->getEditExpenseSubHead();
    }

    function getEditExpenseSubHeadFormSubmit() {
        return $this->model->getEditExpenseSubHeadFormSubmit();
    }

    function getDeleteExpenseSubHead() {
        return $this->model->getDeleteExpenseSubHead();
    }

}