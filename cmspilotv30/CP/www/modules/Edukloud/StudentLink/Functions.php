<?
class CP_Www_Modules_Edukloud_StudentLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_studentLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'student'
           ,'keyField'      => 'student_id'
        ));
    }
}
