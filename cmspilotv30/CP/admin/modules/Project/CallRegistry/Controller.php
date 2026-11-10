<?
class CP_Admin_Modules_Project_CallRegistry_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getDuplicateCallDate(){
        return $this->view->getDuplicateCallDate();
    }

    /**
     *
     */
    function getConvertToOpportunity(){
        return $this->model->getConvertToOpportunity();
    }
}