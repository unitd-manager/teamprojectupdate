<?
class CP_Admin_Modules_AgileIms_SubjectLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_subjectLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'subject'
           ,'keyField'  => 'subject_id'
        ));
    }
}
