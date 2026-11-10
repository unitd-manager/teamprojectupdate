<?
class CP_Admin_Modules_Hms_Expense_Controller extends CP_Common_Lib_ModuleControllerAbstract
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

    function getUpdateRequestQtyOrderItem(){
        return $this->model->getUpdateRequestQtyOrderItem();
    }

    function getUpdateStatusOrderItem(){
        return $this->model->getUpdateStatusOrderItem();
    }

    function getUpdateCompleteTransactionProduct(){
        return $this->model->getUpdateCompleteTransactionProduct();
    }

    function getRollbackCompleteTransactionProduct(){
        return $this->model->getRollbackCompleteTransactionProduct();
    }

    function getUpdateDeductStockProduct(){
        return $this->model->getUpdateDeductStockProduct();
    }

    function getEditDisplay(){
        return $this->view->getEditDisplay();
    }

    function getUpdateStatusExpense(){
        return $this->model->getUpdateStatusExpense();
    }

}