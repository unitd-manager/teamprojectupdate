<?
class CP_Admin_Modules_Account_CounterMaster_Controller extends CP_Common_Lib_ModuleControllerAbstract
{
    function getSaveCounter() {
        $json = $this->model->getSaveCounter();
        return $json;
    }
    
    function getPrintCounter() {
        $json = $this->model->getPrintCounter();
        return $json;
    }
}