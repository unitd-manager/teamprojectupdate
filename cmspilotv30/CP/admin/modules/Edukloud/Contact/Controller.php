<?
class CP_Admin_Modules_Edukloud_Contact_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getPopulateCompanyDetails(){
        return $this->view->getPopulateCompanyDetails();
    }

    /**
    */
    function getPrintForm12() {
        return $this->model->getPrintForm12();
    }

    /**
    */
    function getPrintStudentContract() {
        return $this->model->getPrintStudentContract();
    }

    /**
    */
    function getPrintOfferLetter() {
        return $this->model->getPrintOfferLetter();
    }

    /**
    */
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