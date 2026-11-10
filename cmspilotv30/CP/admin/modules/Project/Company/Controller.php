<?
class CP_Admin_Modules_Project_Company_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getIncorpDateByCompanyJSON(){
        return $this->model->getIncorpDateByCompanyJSON();
    }

    function getRenewalDisplay(){
        return $this->view->getRenewalDisplay();
    }

    function getAddRenewal(){
        return $this->model->getAddRenewal();
    }

    function getEditRenewal(){
        return $this->model->getEditRenewal();
    }

    function getAddRenewalFormSubmit(){
        return $this->model->getAddRenewalFormSubmit();
    }

    function getEditRenewalFormSubmit(){
        return $this->model->getEditRenewalFormSubmit();
    }

    function getDeleteRenewalRecord(){
        return $this->model->getDeleteRenewalRecord();
    }

    function getExtendRenewalForm(){
        return $this->model->getExtendRenewalForm();
    }

    function getExtendRenewalFormSubmit(){
        return $this->model->getExtendRenewalFormSubmit();
    }
}