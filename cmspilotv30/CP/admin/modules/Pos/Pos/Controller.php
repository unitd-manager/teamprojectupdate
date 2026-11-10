<?
class CP_Admin_Modules_Pos_Pos_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    /**
     *
     */
    function getInsertOrderItem(){
        return $this->model->getInsertOrderItem();
    }

    /**
     *
     */
    function getOrderItems(){
        return $this->view->getOrderItems();
    }

    /**
     *
     */
    function getDeleteOrderItem(){
        return $this->model->getDeleteOrderItem();
    }

    /**
     *
     */
    function getDeleteOrderPayment(){
        return $this->model->getDeleteOrderPayment();
    }
    
    /**
     *
     */
    function getTotalValues(){
        return $this->model->getTotalValues();
    }

    /**
     *
     */
    function getPaymentTotalValues(){
        return $this->model->getPaymentTotalValues();
    }

    /**
     *
     */
    function getUpdateQtyOrderItem(){
        return $this->model->getUpdateQtyOrderItem();
    }

    /**
     *
     */
    function getUpdateDiscountOrderItem(){
        return $this->model->getUpdateDiscountOrderItem();
    }

    /**
     *
     */
    function getUpdateDiscountTypeOrderItem(){
        return $this->model->getUpdateDiscountTypeOrderItem();
    }

    /**
     *
     */
    function getUpdateUnitPriceOrderItem(){
        return $this->model->getUpdateUnitPriceOrderItem();
    }

    /**
     *
     */
    function getInvoicePaymentDetails(){
        return $this->view->getInvoicePaymentDetails();
    }

    /**
     *
     */
    function getInvoicePaymentSubmit(){
        return $this->model->getInvoicePaymentSubmit();
    }

    /**
     *
     */
    function getInvoicePaymentValidate(){
        return $this->model->getInvoicePaymentValidate();
    }

    /**
     *
     */
    function getPayByCash(){
        return $this->view->getPayByCash();
    }

    /**
     *
     */
    function getUpdatePaidAmount(){
        return $this->model->getUpdatePaidAmount();
    }

    /**
     *
     */
    function getPayByCashSubmit(){
        return $this->model->getPayByCashSubmit();
    }

    /**
     *
     */
    function getPayByCashValidate(){
        return $this->model->getPayByCashValidate();
    }

    /**
     *
     */
    function getPayByCreditCard(){
        return $this->view->getPayByCreditCard();
    }

    /**
     *
     */
    function getPayByCreditCardSubmit(){
        return $this->model->getPayByCreditCardSubmit();
    }

    /**
     *
     */
    function getPayByCreditCardValidate(){
        return $this->model->getPayByCreditCardValidate();
    }

    /**
     *
     */
    function getClearOrder(){
        return $this->model->getClearOrder();
    }

    /**
     *
     */
    function getClearAmount(){
        return $this->model->getClearAmount();
    }

    /**
     *
     */
    function getEditPayByCash(){
        return $this->view->getEditPayByCash();
    }

    /**
     *
     */
    function getEditPayByCashSubmit(){
        return $this->model->getEditPayByCashSubmit();
    }

    /**
     *
     */
    function getEditPayByCreditCard(){
        return $this->view->getEditPayByCreditCard();
    }

    /**
     *
     */
    function getEditPayByCreditCardSubmit(){
        return $this->model->getEditPayByCreditCardSubmit();
    }

    /**
     *
     */
    function getOrderPayments(){
        return $this->view->getOrderPayments();
    }

    /**
     *
     */
    function getUpdateOverallDiscountOrder(){
        return $this->model->getUpdateOverallDiscountOrder();
    }

   /**
     *
     */
    function getPaymentMethods(){
        return $this->view->getPaymentMethods();
    }

   /**
     *
     */
    function getPopulateMemberCode(){
        return $this->model->getPopulateMemberCode();
    }

   /**
     *
     */
    function getUnitPriceValidate(){
        return $this->model->getUnitPriceValidate();
    }

   /**
     *
     */
    function getItemDiscountValidate(){
        return $this->model->getItemDiscountValidate();
    }

   /**
     *
     */
    function getInvoiceDiscountValidate(){
        return $this->model->getInvoiceDiscountValidate();
    }

   /**
     *
     */
    function getWarningMessage(){
        return $this->model->getWarningMessage();
    }

    /**
     *
     */
    function getWarningMessageSubmit(){
        return $this->model->getWarningMessageSubmit();
    }

    /**
     *
     */
    function getWarningMessageValidate(){
        return $this->model->getWarningMessageValidate();
    }

    /**
     *
     */
    function getSecondaryAuthorization(){
        return $this->view->getSecondaryAuthorization();
    }

    /**
     *
     */
    function getSecondaryAuthorizationSubmit(){
        return $this->model->getSecondaryAuthorizationSubmit();
    }

    /**
     *
     */
    function getSecondaryAuthorizationValidate(){
        return $this->model->getSecondaryAuthorizationValidate();
    }

    /**
     *
     */
    function getSecondaryAuthorizationOverall(){
        return $this->view->getSecondaryAuthorizationOverall();
    }

    /**
     *
     */
    function getSecondaryAuthorizationOverallSubmit(){
        return $this->model->getSecondaryAuthorizationOverallSubmit();
    }

    /**
     *
     */
    function getSecondaryAuthorizationOverallValidate(){
        return $this->model->getSecondaryAuthorizationOverallValidate();
    }

}