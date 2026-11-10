<?
class CP_Common_Modules_Museum_Booking_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('museum_booking');
        $modules->registerModule($modObj, array(
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        
        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('museum_booking', 'picture', 'image');
        $mediaArr->registerMedia($mediaObj, array(
        ));

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('museum_booking', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));
    }

}