<?
class CP_Admin_Modules_Ek_StudentLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('ek_studentLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'student'
           ,'keyField'  => 'student_id'
        ));
    }
}
