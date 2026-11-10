<?
class CP_Admin_Modules_Edukloud_DiscountLink_Functions
{
    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('edukloud_discountLink');
        $modules->registerModule($modObj, array(
            'tableName'     => 'course_discount'
           ,'keyField'      => 'course_discount_id'
        ));
    }
}
