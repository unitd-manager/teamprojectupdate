<?
class CP_Admin_Modules_EnterpriseIms_ContactLink_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enterpriseIms_contactLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'contact'
           ,'keyField'  => 'contact_id'
        ));
    }
}
