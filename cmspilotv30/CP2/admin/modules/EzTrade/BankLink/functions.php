<?
class CP_Admin_Modules_EzTrade_BankLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ezTrade_bankLink');
        $modules->registerModule($modObj, array(
            'tableName'   => 'bank'
           ,'keyField'    => 'bank_id'
        ));
    }
}
