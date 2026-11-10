<?
class CP_Admin_Modules_Payroll_Leave_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
	function getLeave() {
        return $this->view->getLeave();
    }

    function getLeaveFormSubmit() {
        return $this->model->getLeaveFormSubmit();
    }

    function getLeaveValidate() {
        return $this->model->getLeaveValidate();
    }

    function getDeleteLeave() {
        return $this->model->getDeleteLeave();
    }

    function getViewLeaveRecords() {
        return $this->view->getViewLeaveRecords();
    }
    function getStaffEmailUpdate() {
        return $this->view->getStaffEmailUpdate();
    }
    function getStaffHRUpdate() {
        return $this->view->getStaffHRUpdate();
    }
}