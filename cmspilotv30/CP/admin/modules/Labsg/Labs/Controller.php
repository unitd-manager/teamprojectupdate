<?
class CP_Admin_Modules_Labsg_Labs_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getAddMultipleLineItem() {
        return $this->view->getAddMultipleLineItem();
    }

    function getAddMultipleLineItemSubmit() {
        return $this->model->getAddMultipleLineItemSubmit();
    }

    function getAddSingleLineItem() {
        return $this->view->getAddSingleLineItem();
    }



}