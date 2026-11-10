<?
class CP_Admin_Modules_AceIms_SubsidyDiscountLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('aceIms_subsidyDiscountLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'subsidy_discount'
           ,'keyField'  => 'subsidy_discount_id'
        ));
    }
}
