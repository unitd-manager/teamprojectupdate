<?
class CP_Admin_Modules_Payroll_Training_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
	function getTrainingEmplyoee() {
        return $this->view->getTrainingEmplyoee();
    }

    function getTrainingEmplyoeeFormSubmit() {
        return $this->model->getTrainingEmplyoeeFormSubmit();
    }

    function getTrainingEmplyoeeValidate() {
        return $this->model->getTrainingEmplyoeeValidate();
    }

    function getDeleteTrainingEmplyoee() {
        return $this->model->getDeleteTrainingEmplyoee();
    }

    function getLinkEmployeeToCourse() {
        return $this->model->getLinkEmployeeToCourse();
    }

    function getAddTrainingEmplyoee() {
        return $this->view->getAddTrainingEmplyoee();
    }

    function getUpdateEmployeeId() {
        return $this->model->getUpdateEmployeeId();
    }

    function getUpdateStaffFromDateForEmployeeLink() {
        return $this->model->getUpdateStaffFromDateForEmployeeLink();
    }

    function getUpdateStaffToDateForEmployeeLink() {
        return $this->model->getUpdateStaffToDateForEmployeeLink();
    }

}