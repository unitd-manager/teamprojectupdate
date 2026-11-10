<?
class CPL_Admin_Modules_Tradingin_Inventory_Controller extends CP_Admin_Modules_Tradingin_Inventory_Controller
{
    function getUpdateCurrentStockInventoryBatchRecordList() {
        return $this->model->getUpdateCurrentStockInventoryBatchRecordList();
    }

    function getUpdatedAdjustStockHistory() {
        return $this->view->getUpdatedAdjustStockHistory();
    }
}