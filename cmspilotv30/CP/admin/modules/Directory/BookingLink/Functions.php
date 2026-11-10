<?
class CP_Admin_Modules_Directory_BookingLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('directory_bookingLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'business_booking'
           ,'keyField'  => 'business_booking_id'
        ));
    }
}
