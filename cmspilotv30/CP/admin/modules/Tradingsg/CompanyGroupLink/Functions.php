<?
class CP_Admin_Modules_Tradingsg_CompanyGroupLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('tradingsg_companyGroupLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'company_group'
           ,'keyField'  => 'company_group_id'
        ));
    }
}
