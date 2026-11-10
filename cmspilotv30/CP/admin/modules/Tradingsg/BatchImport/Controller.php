<?
class CP_Admin_Modules_Tradingsg_BatchImport_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSupplierJsonByProductId() {
        return $this->model->getSupplierJsonByProductId();
    }

    function getUpdateTotalCostPrice() {
        return $this->model->getUpdateTotalCostPrice();
    }
}