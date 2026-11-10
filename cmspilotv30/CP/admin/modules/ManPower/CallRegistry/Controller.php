<?
class CP_Admin_Modules_ManPower_CallRegistry_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getConvertToOpportunity(){
        return $this->model->getConvertToOpportunity();
    }

    function getDuplicateCallDate(){
        return $this->view->getDuplicateCallDate();
    }

    function getSearchCompanyName() {
        return $this->model->getSearchCompanyName();
    }

    function getUpdateCompanyDetails() {
        return $this->model->getUpdateCompanyDetails();
    }

    function getAddNewValuelistForm() {
        return $this->view->getAddNewValuelistForm();
    }

    function getAddNewValuelistFormSubmit() {
        return $this->model->getAddNewValuelistFormSubmit();
    }

}