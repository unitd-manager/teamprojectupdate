<?
class CP_Admin_Modules_Trading_Inventory_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getChangeStatus() {
        return $this->view->getChangeStatus();
    }
    function getChangeStatusSubmit() {
        return $this->model->getChangeStatusSubmit();
    }
    function getCalculateSalePrice() {
        return $this->model->getCalculateSalePrice();
    }
    function getUpdateSalePriceFromList() {
        return $this->model->getUpdateSalePriceFromList();
    }
}