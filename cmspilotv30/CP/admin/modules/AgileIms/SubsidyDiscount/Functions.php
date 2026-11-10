<?
class CP_Admin_Modules_AgileIms_SubsidyDiscount_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('agileIms_subsidyDiscount');
        $modObj['tableName'] = 'subsidy_discount';
        $modObj['keyField']  = 'subsidy_discount_id';
        $modules->registerModule($modObj, array(
            'title'       => 'Subsidy/Discount'
           ,'actBtnsEdit' => array('save', 'apply', 'delete')
        ));
    }
}