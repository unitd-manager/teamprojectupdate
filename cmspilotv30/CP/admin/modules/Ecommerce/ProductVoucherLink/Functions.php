<?
class CP_Admin_Modules_Ecommerce_ProductVoucherLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_productVoucherLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'product_voucher'
           ,'keyField'  => 'product_voucher_id'
        ));
    }
}
