<?
class CP_Admin_Modules_Edukloud_TeacherAttendance_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('edukloud_teacherAttendance');
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
        $mediaObj = $mediaArr->getMediaObj('edukloud_teacherAttendance', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }
}