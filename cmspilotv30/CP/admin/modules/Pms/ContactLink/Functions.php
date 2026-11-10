<?
class CP_Admin_Modules_Pms_ContactLink_Functions
{
    function setModuleArray($modules){
        $fn = Zend_Registry::get('fn');

        $srcRoom = $fn->getReqParam('srcRoom');        

        $modObj = $modules->getModuleObj('pms_contactLink');
        if ($srcRoom == 'pms_resources') {
            $modules->registerModule($modObj, array(
                'tableName' => 'resources_contact'
               ,'keyField'  => 'resources_contact_id'
            ));
        } else {
            $modules->registerModule($modObj, array(
                'tableName' => 'contact'
               ,'keyField'  => 'contact_id'
            ));
        }
    }
}
