<?
class CP_Admin_Modules_EnterpriseIms_Grade_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('enterpriseIms_grade');
        $modules->registerModule($modObj, array(
            'actBtnsList' => array()
           ,'tableName' => 'student_grade'
           ,'keyField'  => 'student_grade_id'
        ));
    }
}