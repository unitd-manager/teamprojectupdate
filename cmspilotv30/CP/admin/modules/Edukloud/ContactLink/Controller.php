<?
class CP_Admin_Modules_Edukloud_ContactLink_Controller extends CP_Common_Lib_ModuleLinkControllerAbstract
{
    function getAddForResources() {
        return $this->model->getAddForResources();
    }

    /**
    */
    function getSaveForResources() {
        return $this->model->getSaveForResources();
    }
}
