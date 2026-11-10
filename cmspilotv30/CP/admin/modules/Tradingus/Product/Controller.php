<?
class CP_Admin_Modules_Tradingus_Product_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getGenerateBulkVouchers() {
        return $this->model->getGenerateBulkVouchers();
    }

    function getGenerateVoucherFormSubmit() {
        return $this->model->getGenerateVoucherFormSubmit();
    }
    
    function getPrintVoucher() {
        return $this->model->getPrintVoucher();
    }

    function getCategoryJsonByProductGroupId() {
        return $this->model->getCategoryJsonByProductGroupId();
    }

    function getQuickAdd(){
        return $this->view->getQuickAdd();
    }

    function getQuickAddSubmit(){
        return $this->model->getQuickAddSubmit();
    }

    function getAddProductRecord(){
        return $this->view->getAddProductRecord();
    }

    function getAddPrice(){
        return $this->model->getAddPrice();
    }

    function getAddPriceFormSubmit(){
        return $this->model->getAddPriceFormSubmit();
    }

    function getPriceDisplay(){
        return $this->view->getPriceDisplay();
    }

    function getEditPrice(){
        return $this->model->getEditPrice();
    }

    function getEditPriceFormSubmit(){
        return $this->model->getEditPriceFormSubmit();
    }

    function getDeletePriceRecord(){
        return $this->model->getDeletePriceRecord();
    }

}