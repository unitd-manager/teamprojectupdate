<?
class CP_Admin_Modules_Labsg_ContactLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('labsg_contactLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'contact'
           ,'keyField'  => 'contact_id'
        ));
    }
}
