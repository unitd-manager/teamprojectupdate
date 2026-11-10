<?
class CPL_Admin_Modules_Tradingsg_Booking_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tradingsg_booking');
        $modules->registerModule($modObj, array(
            'hasFlagInList'=> 0
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('tradingsg_booking', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));

    }
}