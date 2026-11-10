<?
class CP_Admin_Modules_Trading_CompanyLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('trading_companyLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'company'
           ,'keyField'  => 'company_id'
        ));
    }
}
