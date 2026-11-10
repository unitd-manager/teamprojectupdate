<?
class CP_Admin_Modules_Trading_Quote_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

    function getRaiseSOList() {
        return $this->view->getRaiseSOList();
    }

    function getRaiseSOListValidation() {
        return $this->model->getRaiseSOListValidation();
    }

    function getRaiseSOValidation() {
        return $this->model->getRaiseSOListValidation();
    }

    function getRaiseSO() {
        return $this->model->getRaiseSO();
    }

    function getDuplicateQuote() {
        return $this->model->getDuplicateQuote();
    }

}