<?
class CP_Admin_Modules_Tuitionsg_SubjectLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tuitionsg_subjectLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'subject'
           ,'keyField'  => 'subject_id'
        ));
    }
}
