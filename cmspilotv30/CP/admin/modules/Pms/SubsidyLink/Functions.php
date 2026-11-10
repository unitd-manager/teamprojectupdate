<?
class CP_Admin_Modules_Pms_SubsidyLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_subsidyLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'course_subsidy'
           ,'keyField'      => 'course_subsidy_id'
        ));
    }
}
