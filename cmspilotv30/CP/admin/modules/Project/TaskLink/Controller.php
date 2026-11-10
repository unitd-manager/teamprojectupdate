<?
class CP_Admin_Modules_Project_TaskLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    /**
     *
     */
    function getNewPortalFromDashboard(){
        return $this->view->getNewPortalFromDashboard();
    }

    /**
     *
     */
    function getAddPortalFromDashboard(){
        return $this->model->getAddPortalFromDashboard();
    }
}
