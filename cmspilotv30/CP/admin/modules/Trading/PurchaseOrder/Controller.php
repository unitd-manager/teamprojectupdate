<?
class CP_Admin_Modules_Trading_PurchaseOrder_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getEditInventoryForm() {
        return $this->view->getEditInventoryForm();
    }
    function getSaveInventory() {
        return $this->model->getSaveInventory();
    }

    //-----------------------//
    function getValidateEditProductItemLink() {
        return $this->model->getValidateEditProductItemLink();
    }
}