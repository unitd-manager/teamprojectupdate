<?
class CPL_Admin_Modules_Payroll_JobInformation_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getContactByCompanyJSON(){
        return $this->model->getContactByCompanyJSON();
    }

    function getSearchEmployeeDetails(){
    	return $this->model->getSearchEmployeeDetails();
    }

    function getKETPdf() {
        return $this->model->getKETPdf();
    }

    function getPrintEmploymentContract() {
        return $this->model->getPrintEmploymentContract();
    }
    function getPrintImplement() {
        return $this->model->getPrintImplement();
    }

    function getDuplicateJobInformation() {
        return $this->model->getDuplicateJobInformation();
    }
}