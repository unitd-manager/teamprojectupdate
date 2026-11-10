<?
class CP_Admin_Modules_Account_CurrencyConvert_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getExchangeRate() {
        $fn = Zend_Registry::get('fn');

        $acc_head_id = $fn->getReqParam('acc_head_id');
        $rateFor = $fn->getReqParam('rateFor');
        $rowAccHead = $fn->getRecordRowByID('acc_head', 'acc_head_id', $acc_head_id);
        return $this->model->getExchangeRate($rowAccHead['currency_id'], $rateFor);
    }
    
    function getUpdateEveningRate() {
        return $this->model->getUpdateEveningRate();
    }

}