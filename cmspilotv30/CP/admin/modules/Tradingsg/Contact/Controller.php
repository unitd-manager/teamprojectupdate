<?
class CP_Admin_Modules_Tradingsg_Contact_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

    function getContactJsonByComId() {
        return $this->model->getContactJsonByComId();
    }
}