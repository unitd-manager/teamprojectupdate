<?
class CP_Admin_Modules_Edukloud_Attendance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_attendance');
        $modules->registerModule($modObj, array(
            'title'         => 'Attendance'
        ));
    }

   /**
    *
    */
   function setLinksArray($inst) {
   }    
}