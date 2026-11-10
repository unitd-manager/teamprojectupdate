<?
class CP_Admin_Modules_Tradingsg_StockTransfer_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

    function getOrderItems(){
        return $this->view->getOrderItems();
    }

    function getUpdateOrderLineItems() {
        return $this->model->getUpdateOrderLineItems();
    }

    function getUpdateQtyOrderItem(){
        return $this->model->getUpdateQtyOrderItem();
    }

    function getDeleteItem(){
        return $this->model->getDeleteItem();
    }


    function getUpdatePiecesOrderItem(){
        return $this->model->getUpdatePiecesOrderItem();
    }

    function getPrintExportAsPdf(){
        return $this->model->getPrintExportAsPdf();
    }

}