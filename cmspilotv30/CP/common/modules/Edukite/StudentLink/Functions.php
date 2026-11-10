<?
class CP_Common_Modules_Edukite_StudentLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        
        $modObj = $modules->getModuleObj('edukite_studentLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'student'
           ,'keyField'  => 'student_id'
        ));
    }
}
