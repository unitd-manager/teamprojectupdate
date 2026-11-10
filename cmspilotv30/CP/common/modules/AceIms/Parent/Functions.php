<?
class CP_Common_Modules_AceIms_Parent_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_parent');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('aceIms_parent', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}