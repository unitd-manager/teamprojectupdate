<?
class CP_Admin_Modules_EnterpriseIms_TeacherAttendance_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_teacherAttendance');
        $modules->registerModule($modObj, array(
            'title'         => 'Teacher Attendance'
           ,'tableName'     => 'teacher_attendance'
           ,'keyField'      => 'teacher_attendance_id'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('enterpriseIms_teacherAttendance', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array(
        ));                
    }
}