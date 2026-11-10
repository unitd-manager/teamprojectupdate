<?
class CP_Admin_Modules_Trading_Shipment_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getShipmentReceived() {
        return $this->model->getShipmentReceived();
    }

    //-----------------------//
    function getEditInventoryForm() {
        return $this->view->getEditInventoryForm();
    }
    function getSaveInventory() {
        return $this->model->getSaveInventory();
    }    
}