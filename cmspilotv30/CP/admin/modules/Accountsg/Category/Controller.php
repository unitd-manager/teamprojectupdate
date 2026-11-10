<?
class CP_Admin_Modules_Accountsg_Category_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getNewMenuItem() {
        return $this->view->getNewMenuItem();
    }

    /**
     *
     */
    function getAddMenuItem() {
        return $this->model->getAddMenuItem();
    }

    /**
     *
     */
    function getListItems() {
        return $this->view->getListItems();
    }

    /**
     *
     */
    function getEditMenuItem() {
        return $this->view->getEditMenuItem();
    }

    /**
     *
     */
    function getSaveMenuItem() {
        return $this->model->getSaveMenuItem();
    }
}