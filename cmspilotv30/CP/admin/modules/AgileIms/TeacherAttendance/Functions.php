<?
class CP_Admin_Modules_AgileIms_TeacherAttendance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_teacherAttendance');
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
        $mediaObj = $mediaArr->getMediaObj('agileIms_teacherAttendance', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array());                
    }
}