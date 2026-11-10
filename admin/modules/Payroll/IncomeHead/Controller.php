<?
class CPL_Admin_Modules_Payroll_IncomeHead_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
	function getAddIncomeSubHead() {
        return $this->view->getAddIncomeSubHead();
    }

    function getIncomeSubHeadDetail() {
        return $this->view->getIncomeSubHeadDetail();
    }

    function getIncomeSubHeadFormSubmit() {
        return $this->model->getIncomeSubHeadFormSubmit();
    }

    function getEditIncomeSubHead() {
        return $this->view->getEditIncomeSubHead();
    }

    function getEditIncomeSubHeadFormSubmit() {
        return $this->model->getEditIncomeSubHeadFormSubmit();
    }

    function getDeleteIncomeSubHead() {
        return $this->model->getDeleteIncomeSubHead();
    }

}