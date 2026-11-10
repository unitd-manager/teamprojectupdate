<?
class CP_Admin_Modules_Account_CashMaster_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSaveCash() {
        $json = $this->model->getSaveCash();
        return $json;
    }
    
    function getPrintCash() {
        $json = $this->model->getPrintCash();
        return $json;
    }
}