<?
class CP_Common_Modules_Ecommerce_PromoCode_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ecommerce_promoCode');
        $modObj['tableName'] = 'promo_code';
        $modObj['keyField']  = 'promo_code_id';
        $modules->registerModule($modObj, array(
            'title' => 'Promo Code'
        ));
    }    
}