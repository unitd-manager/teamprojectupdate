<?
class CP_Admin_Modules_Museum_Booking_Functions extends CP_Common_Modules_Museum_Booking_Functions
{

    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('museum_booking');
        $modules->registerModule($modObj, array(
            'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'export')
           ,'hasMultiLang' => 1
        ));
    }

}