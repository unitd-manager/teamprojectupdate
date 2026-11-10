<?
class CP_Admin_Modules_Ek_School_Functions extends CP_Common_Modules_Ek_School_Functions
{
    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('ek_school', 'logo', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }
}