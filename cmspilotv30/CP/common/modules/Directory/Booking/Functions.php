<?
class CP_Common_Modules_Directory_Booking_Functions
{

    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('directory_booking');
        $modules->registerModule($modObj, array(
        	'hasFlagInList' => 0
           ,'title' => 'Booking'
           ,'tableName' => 'booking'
           ,'keyField' => 'booking_id'
           ,'actBtnsList' => array('new', 'export')
        ));
    }

    function setMediaArray($mediaArr) {
        $mediaObj = $mediaArr->getMediaObj('directory_booking', 'picture', 'image');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }    
}