<?
class CP_Admin_Modules_AceIms_TeacherLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_teacherLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'teacher'
           ,'keyField'  => 'teacher_id'
        ));
    }
}
