<?
class CP_Admin_Modules_EnterpriseIms_TeacherLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_teacherLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'teacher'
           ,'keyField'  => 'teacher_id'
        ));
    }
}
