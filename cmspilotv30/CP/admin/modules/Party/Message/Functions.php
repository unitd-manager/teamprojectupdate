<?
class CP_Admin_Modules_Party_Message_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('party_message');
        $modules->registerModule($modObj, array(
        ));
    }

    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('party_message', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }    
}