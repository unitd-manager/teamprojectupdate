<?
class CP_Admin_Modules_Party_PartySetup_Controller extends CP_Common_Lib_ModuleControllerAbstract
{

    function getChangeGuestAmount() {
        $cpUtil = Zend_Registry::get('cpUtil');
        return $cpUtil->getJsonFromArray($this->model->getChangeGuestAmount());
    }    
}