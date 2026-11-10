<?
class CP_Admin_Modules_Account_AccCategory_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getListItems() {
        return $this->view->getListItems();
    }

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
    function getEditMenuItem() {
        return $this->view->getEditMenuItem();
    }

    /**
     *
     */
    function getSaveMenuItem() {
        return $this->model->getSaveMenuItem();
    }

    /**
     *
     */
    function getDeleteMenuItem() {
        return $this->model->getDeleteMenuItem();
    }
}