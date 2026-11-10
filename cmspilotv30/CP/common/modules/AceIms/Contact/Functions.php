<?
class CP_Common_Modules_AceIms_Contact_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_contact');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('aceIms_student', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}