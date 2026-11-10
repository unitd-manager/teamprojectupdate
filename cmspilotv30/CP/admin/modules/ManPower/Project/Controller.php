<?
class CP_Admin_Modules_ManPower_Project_Controller extends CP_Common_Lib_ModuleControllerAbstract
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
    function getGenerateOrderRecord() {
        return $this->view->getGenerateOrderRecord();
    }

    /**
     *
     */
    function getCommissionPercent() {
        return $this->view->getCommissionPercent();
    }

}