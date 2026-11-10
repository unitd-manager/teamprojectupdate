<?
class CP_Www_Modules_Ecommerce_Basket_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('ecommerce_basket');
        $modules->registerModule($modObj, array(
        ));
    }
}
