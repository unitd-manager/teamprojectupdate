<?
class CP_Admin_Modules_Payroll_LeavePolicy_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
	function getLeavepolicy() {
        return $this->view->getLeavepolicy();
    }

    function getLeavepolicyFormSubmit() {
        return $this->model->getLeavepolicyFormSubmit();
    }

    function getLeavepolicyValidate() {
        return $this->model->getLeavepolicyValidate();
    }

    function getDeleteLeavepolicy() {
        return $this->model->getDeleteLeavepolicy();
    }

}