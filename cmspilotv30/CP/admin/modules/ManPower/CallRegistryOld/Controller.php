<?
class CP_Admin_Modules_ManPower_CallRegistry_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getCompanyDetailsJSON(){
        return $this->model->getCompanyDetailsJSON();
    }

    function getConvertToOpportunity(){
        return $this->model->getConvertToOpportunity();
    }

    function getDuplicateSubmit(){
        return $this->model->getDuplicateSubmit();
    }

    function getSearchCompanyName() {
        return $this->model->getSearchCompanyName();
    }

    function getUpdateCompanyDetails() {
        return $this->model->getUpdateCompanyDetails();
    }

    function getDuplicateCallDate(){
        return $this->view->getDuplicateCallDate();
    }

    function getCreateClientRec(){
        return $this->model->getCreateClientRec();
    }

    function getStatusByCategoryJSON(){
        return $this->model->getStatusByCategoryJSON();
    }

    function getSendProfileToClientForm(){
        return $this->view->getSendProfileToClientForm();
    }

    function getSendProfileToClientFormSubmit(){
        return $this->model->getSendProfileToClientFormSubmit();
    }
}