<?
class CP_Admin_Modules_Directory_Ambiance_Functions extends CP_Common_Modules_Directory_Ambiance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_ambiance');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'title' => 'Ambiance'
           ,'keyField' => 'ambiance_id'
           ,'actBtnsList' => array('new', 'export')
        ));
    }

    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('directory_ambiance', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
    
}