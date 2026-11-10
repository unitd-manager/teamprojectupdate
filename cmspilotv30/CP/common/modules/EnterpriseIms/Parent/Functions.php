<?
class CP_Common_Modules_EnterpriseIms_Parent_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_parent');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enterpriseIms_parent', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}