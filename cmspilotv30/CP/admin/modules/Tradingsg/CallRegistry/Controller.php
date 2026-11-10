<?
class CP_Admin_Modules_Tradingsg_CallRegistry_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getConvertToEnquiry(){
        return $this->model->getConvertToEnquiry();
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

}