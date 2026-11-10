<?
class CP_Admin_Modules_Directory_BusinessContactLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('directory_businessContactLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'business_contact'
           ,'keyField'  => 'business_contact_id'
        ));
    }
}
