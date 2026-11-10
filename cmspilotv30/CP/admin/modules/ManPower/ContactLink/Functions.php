<?
class CP_Admin_Modules_ManPower_ContactLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('manPower_contactLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'contact'
           ,'keyField'  => 'contact_id'
        ));
    }
}
