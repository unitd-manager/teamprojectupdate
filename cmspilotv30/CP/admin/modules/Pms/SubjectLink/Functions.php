<?
class CP_Admin_Modules_Pms_SubjectLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
    
        $modObj = $modules->getModuleObj('pms_subjectLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'subject'
           ,'keyField'  => 'subject_id'
        ));
    }
}
