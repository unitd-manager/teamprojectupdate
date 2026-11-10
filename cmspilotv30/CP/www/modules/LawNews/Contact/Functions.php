<?
class CP_Www_Modules_LawNews_Contact_Functions
{

    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('lawNews_contact');
        $modules->registerModule($modObj, array(
             'actBtnsDetail' => array('changePassword', 'edit')
            ,'showActBtnsBelowForm' => true
        ));
    }
}
