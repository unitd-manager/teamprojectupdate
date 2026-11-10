<?
class CP_Admin_Modules_AgileIms_SubsidyDiscountLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_subsidyDiscountLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'subsidy_discount'
           ,'keyField'  => 'subsidy_discount_id'
        ));
    }
}
