<?
class CP_Admin_Modules_Edukloud_SubjectLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
    
        $modObj = $modules->getModuleObj('edukloud_subjectLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'subject'
           ,'keyField'  => 'subject_id'
        ));
    }
}
