<?
class CP_Admin_Modules_Tradingsg_Pos_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSearchProductTitle() {
        return $this->model->getSearchProductTitle();
    }

    function getSearchCustomerDetails() {
        return $this->model->getSearchCustomerDetails();
    }

    function getDisplayCustomerDetails() {
        return $this->model->getDisplayCustomerDetails();
    }

    function getUpdateOrderLineItems() {
        return $this->model->getUpdateOrderLineItems();
    }

    function getOrderItems(){
        return $this->view->getOrderItems();
    }

    function getUpdateQtyOrderItem(){
        return $this->model->getUpdateQtyOrderItem();
    }

    function getCreateNewOrder(){
        return $this->model->getCreateNewOrder();
    }

    function getCancelOrder(){
        return $this->model->getCancelOrder();
    }

    function getGenerateBill(){
        return $this->view->getGenerateBill();
    }

    function getPrintBill(){
        return $this->view->getPrintBill();
    }

    function getPrintBillForPrinter(){
        return $this->view->getPrintBillForPrinter();
    }

    function getDeleteItem(){
        return $this->model->getDeleteItem();
    }

    function getCloseOrder(){
        return $this->model->getCloseOrder();
    }

    function getUpdateDiscountOrder(){
        return $this->model->getUpdateDiscountOrder();
    }

    function getUpdateBalance(){
        return $this->model->getUpdateBalance();
    }

    function getProductPrice(){
        return $this->view->getProductPrice();
    }

    function getProductPriceDisplay(){
        return $this->view->getProductPriceDisplay();
    }

    function getUpdatediscountType(){
        return $this->model->getUpdatediscountType();
    }

    function getUpdateDiscountPercentOrderItem(){
        return $this->model->getUpdateDiscountPercentOrderItem();
    }

    function getUpdatePiecesOrderItem(){
        return $this->model->getUpdatePiecesOrderItem();
    }

    function getCheckPendingOrderDetails(){
        return $this->view->getCheckPendingOrderDetails();
    }

    function getOrderStatusToPending(){
        return $this->view->getOrderStatusToPending();
    }

    function getInsertOldOrder(){
        return $this->view->getInsertOldOrder();
    }

    function getApplyDiscount(){
        return $this->view->getApplyDiscount();
    }

    function getApplyDiscountSubmit(){
        return $this->model->getApplyDiscountSubmit();
    }

    function getPrintbillcondition(){
        return $this->view->getPrintbillcondition();
    }

    function getPrintbillconditionForPrinter(){
        return $this->view->getPrintbillconditionForPrinter();
    }

    function getPrintBillPdf(){
        return $this->view->getPrintBillPdf();
    }

    function getAddClient(){
        return $this->view->getAddClient();
    }

    function getAddClientSubmit(){
        return $this->model->getAddClientSubmit();
    }

    function getRemoveClient(){
        return $this->model->getRemoveClient();
    }

}