<?
class CP_Admin_Modules_Common_SiteLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('common_siteLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'site'
           ,'keyField'  => 'site_id'
        ));
    }
}
