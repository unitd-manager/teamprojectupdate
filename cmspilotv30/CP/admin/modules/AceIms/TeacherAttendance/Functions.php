<?
class CP_Admin_Modules_AceIms_TeacherAttendance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_teacherAttendance');
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
        $mediaObj = $mediaArr->getMediaObj('aceIms_teacherAttendance', 'attachment', 'attachment');
        $mediaArr->registerMedia($mediaObj, array());                
    }
}