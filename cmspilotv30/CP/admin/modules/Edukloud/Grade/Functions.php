<?
class CP_Admin_Modules_Edukloud_Grade_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('edukloud_grade');
        $modules->registerModule($modObj, array(
            'actBtnsList' => array()
           ,'tableName' => 'student_grade'
           ,'keyField'  => 'student_grade_id'
        ));
    }

}