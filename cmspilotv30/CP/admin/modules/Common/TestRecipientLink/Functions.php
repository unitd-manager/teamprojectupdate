<?
class CP_Admin_Modules_Common_TestRecipientLink_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('common_testRecipientLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'contact'
           ,'keyField'  => 'contact_id'
           ,'mainModuleName'  => 'common_contact'
        ));
    }
}
