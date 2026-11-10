<?
class CP_Admin_Modules_Tuitionsg_Contact_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    /**
    */
    function getContactChecklistSubmit(){
        $this->model->getContactChecklistSubmit();
    }

    /**
    */
    function getOfferLetterToStudentForm() {
        return $this->view->getOfferLetterToStudentForm();
    }

    /**
    */
    function getOfferLetterToStudentFormSubmit() {
        return $this->model->getOfferLetterToStudentFormSubmit();
    }

    /**
    */
    function getContactNew() {
        return $this->view->getContactNew();
    }

    /**
    */
    function getContactAddSubmit() {
        return $this->model->getContactAddSubmit();
    }

    /**
    */
    function getContactDetails() {
        return $this->view->getContactDetails();
    }

    /**
    */
    function getContactEdit() {
        return $this->view->getContactEdit();
    }

    /**
    */
    function getContactSave() {
        return $this->model->getContactSave();
    }

    /**
    */
    function getFindContactCountForRegNo($enrollment_year) {
        return $this->model->getFindContactCountForRegNo($enrollment_year);
    }
}