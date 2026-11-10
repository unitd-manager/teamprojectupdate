<?
class CP_Admin_Modules_Pms_AssessorLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_assessorLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'teacher'
           ,'keyField'  => 'teacher_id'
        ));
    }
}
