<?
class CP_Common_Modules_Edukloud_Parent_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('edukloud_parent');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('edukloud_parent', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}