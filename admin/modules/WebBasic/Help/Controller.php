<?
class CPL_Admin_Modules_WebBasic_Help_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getHelpContentTask(){
        return $this->view->getHelpContentTask();
    }

    function getStartedContentTask(){
        return $this->view->getStartedContentTask();
    }


}