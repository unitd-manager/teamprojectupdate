<?
class CP_Admin_Modules_EnterpriseIms_Contact_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getPrintWithdrawalForm() {
        return $this->model->getPrintWithdrawalForm();
    }

    /**
    */
    function getChangeStatusForm() {
        return $this->view->getChangeStatusForm();
    }

    /**
    */
    function getChangeStatusFormSubmit() {
        return $this->model->getChangeStatusFormSubmit();
    }

    /**
    */
    function getChangeStatusFormValidate() {
        return $this->model->getChangeStatusFormValidate();
    }

    /**
    */
    function getChangeStatusToActive() {
        return $this->view->getChangeStatusToActive();
    }
}