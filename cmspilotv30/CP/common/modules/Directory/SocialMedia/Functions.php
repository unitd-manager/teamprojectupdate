<?
class CP_Common_Modules_Directory_SocialMedia_Functions extends CP_Common_Lib_ModuleFunctionsAbstract
{

    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_socialMedia');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'title' => 'Social Media'
           ,'tableName' => 'social_media'
           ,'keyField' => 'social_media_id'
           ,'actBtnsList' => array('new', 'export')
        ));
    }

    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('directory_socialMedia', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }    
}