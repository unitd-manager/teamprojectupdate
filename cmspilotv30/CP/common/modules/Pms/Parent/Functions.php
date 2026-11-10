<?
class CP_Common_Modules_Pms_Parent_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('pms_parent');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('pms_parent', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}