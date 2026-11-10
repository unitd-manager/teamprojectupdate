<?
class CP_Admin_Modules_Ecommerce_RegisterProduct_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ecommerce_registerProduct');
        $modObj['tableName'] = 'register_product';
        $modObj['keyField']  = 'register_product_id';
        $modules->registerModule($modObj, array(
            'title'         => 'Product Registration'
           ,'actBtnsList'   => array()
        ));
    }
}