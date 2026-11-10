<?
class CP_Admin_Modules_Pms_DiscountLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('pms_discountLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'course_discount'
           ,'keyField'      => 'course_discount_id'
        ));
    }
}
