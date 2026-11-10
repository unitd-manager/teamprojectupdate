<?
class CP_Admin_Modules_AceIms_SubjectLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_subjectLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'subject'
           ,'keyField'  => 'subject_id'
        ));
    }
}
