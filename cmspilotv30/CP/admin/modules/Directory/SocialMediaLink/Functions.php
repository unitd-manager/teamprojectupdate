<?
class CP_Admin_Modules_Directory_SocialMediaLink_Functions extends CP_Common_Modules_Directory_SocialMediaLink_Functions
{
    function setModuleArray($modules){
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $tableName = 'social_media';
        $keyField = 'social_media_id';
        $modObj = $modules->getModuleObj('directory_socialMediaLink');
        $modules->registerModule($modObj, array(
             'tableName' => $tableName
            ,'keyField'  => $keyField
        ));
    }
}
