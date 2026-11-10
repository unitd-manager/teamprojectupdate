<?
class CP_Admin_Modules_Elearn_TeacherLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('elearn_teacherLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'teacher'
           ,'keyField'  => 'teacher_id'
        ));
    }
}
