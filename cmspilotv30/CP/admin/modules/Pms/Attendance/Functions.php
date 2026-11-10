<?
class CP_Admin_Modules_Pms_Attendance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_attendance');
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