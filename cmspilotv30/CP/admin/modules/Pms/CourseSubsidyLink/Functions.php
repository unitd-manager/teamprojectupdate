<?
class CP_Admin_Modules_Pms_CourseSubsidyLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_courseSubsidyLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'subsidy_discount'
           ,'keyField'  => 'subsidy_discount_id'
        ));
    }
}
