<?
class CP_Admin_Modules_Tradingsg_Company_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

	function getAddCompany(){
        return $this->model->getAddCompany();
    }

    function getAddCompanyFormSubmit(){
        return $this->model->getAddCompanyFormSubmit();
    }

    function getCompanyLinkDisplay(){
        return $this->view->getCompanyLinkDisplay();
    }

    function getDeleteCompanyLinkRecord(){
        return $this->model->getDeleteCompanyLinkRecord();
    }
}