<?
class CP_Admin_Modules_Edukloud_TeacherLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_teacherLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'teacher'
           ,'keyField'  => 'teacher_id'
        ));
    }
}
