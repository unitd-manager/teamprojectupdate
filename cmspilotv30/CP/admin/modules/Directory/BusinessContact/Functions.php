<?
class CP_Admin_Modules_Directory_BusinessContact_Functions extends CP_Common_Modules_Directory_BusinessContact_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_businessContact');
        $modObj['tableName'] = 'business_contact';
        $modObj['keyField']  = 'business_contact_id';
        $modules->registerModule($modObj, array(
            'title' => 'Business Contact'
           ,'actBtnsList' => array('new', 'export')
        ));
    }
}