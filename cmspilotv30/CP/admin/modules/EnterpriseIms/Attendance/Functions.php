<?
class CP_Admin_Modules_EnterpriseIms_Attendance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('enterpriseIms_attendance');
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