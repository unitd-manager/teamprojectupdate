<?
class CP_Admin_Modules_Tradingsg_PackingList_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getPrintPackingListAsPdf() {
        return $this->model->getPrintPackingListAsPdf();
    }

    function getGeneratePackingListForm() {
        return $this->view->getGeneratePackingListForm();
    }

    function getGeneratePackingListFormSubmit() {
        return $this->model->getGeneratePackingListFormSubmit();
    }

    function getEditPackingListForm() {
        return $this->view->getEditPackingListForm();
    }

    function getEditPackingListFormSubmit() {
        return $this->model->getEditPackingListFormSubmit();
    }
}