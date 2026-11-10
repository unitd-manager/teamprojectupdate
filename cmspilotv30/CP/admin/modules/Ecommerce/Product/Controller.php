<?
class CP_Admin_Modules_Ecommerce_Product_Controller extends CP_Common_Modules_Ecommerce_Product_Controller
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
}