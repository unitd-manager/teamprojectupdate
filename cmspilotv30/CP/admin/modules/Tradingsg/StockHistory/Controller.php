<?
class CP_Admin_Modules_Tradingsg_StockHistory_Controller extends CP_Common_Lib_ModuleControllerAbstract
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
}