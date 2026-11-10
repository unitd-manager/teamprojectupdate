<?
class CP_Admin_Modules_Project_Project_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getDuplicateProject() {
        return $this->model->getDuplicateProject();
    }
    
    /**
     *
     */
    function getProjectHistoryDetails() {
        return $this->view->getProjectHistoryDetails();
    }

    /**
     *
     */
    function getConvertOppToProject() {
        return $this->model->getConvertOppToProject();
    }
    
}