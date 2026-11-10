<?
class CP_Admin_Modules_EzTrade_CompanyLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ezTrade_companyLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'company'
           ,'keyField'  => 'company_id'
        ));
    }
}
