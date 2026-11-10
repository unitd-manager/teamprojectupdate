<?
class CP_Www_Modules_Ecommerce_Product_Functions extends CP_Common_Modules_Ecommerce_Product_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('ecommerce_product');
        $modules->registerModule($modObj, array(
            'listLimit' => 50
        ));
    }
}