<?
class CP_Admin_Modules_EnterpriseIms_SubjectLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
    
        $modObj = $modules->getModuleObj('enterpriseIms_subjectLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'subject'
           ,'keyField'  => 'subject_id'
        ));
    }
}
