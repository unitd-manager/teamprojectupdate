<?
class CPL_Admin_Modules_WebBasic_Content_Controller extends CP_Admin_Modules_WebBasic_Content_Controller
{
    function getHelpContentTask(){
        return $this->view->getHelpContentTask();
    }

    function getStartedContentTask(){
        return $this->view->getStartedContentTask();
    }


}