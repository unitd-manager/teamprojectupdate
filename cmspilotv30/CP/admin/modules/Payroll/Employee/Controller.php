<?
class CP_Admin_Modules_Payroll_Employee_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getContactByCompanyJSON(){
        return $this->model->getContactByCompanyJSON();
    }

    function getAddNewValuelistForm() {
        return $this->view->getAddNewValuelistForm();
    }

    function getAddNewValuelistFormSubmit() {
        return $this->model->getAddNewValuelistFormSubmit();
    }

    function getValueByValuelistJSON() {
        return $this->model->getValueByValuelistJSON();
    }

    function importDataRowCallbackForPayslip() {
        $modObj = getCPModuleObj('payroll_payrollManagement');
        return $modObj->model->importDataRowCallbackForPayslip();
    }
}