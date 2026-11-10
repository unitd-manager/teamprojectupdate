<?
class CP_Admin_Modules_Edukloud_SubsidyDiscountLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_subsidyDiscountLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'subsidy_discount'
           ,'keyField'  => 'subsidy_discount_id'
        ));
    }
}
