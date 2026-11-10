<?
class CP_Admin_Modules_Labsg_Product_Controller extends CP_Common_Lib_ModuleControllerAbstract
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
}