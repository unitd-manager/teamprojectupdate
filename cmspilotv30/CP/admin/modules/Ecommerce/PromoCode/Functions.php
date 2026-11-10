<?
class CP_Admin_Modules_Ecommerce_PromoCode_Functions extends CP_Common_Modules_Ecommerce_PromoCode_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ecommerce_promoCode');
        $modObj['tableName'] = 'promo_code';
        $modObj['keyField']  = 'promo_code_id';
        $modules->registerModule($modObj, array(
            'title'         => 'Promo Code'
           ,'hasMultiLang' => 1
           ,'hasFlagInList' => 0
        ));
    }
    
    /**
     *
     */
    function setLinksArray($inst) {
        $ln = Zend_Registry::get('ln');

        $linkObj = $inst->getLinksArrayObj('ecommerce_promoCode', 'directory_cityLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'  => 'promo_code_city'
            ,'showAnchorInLinkPortal' => 0
            , 'fieldlabel' => array($ln->gd('m.ecommerce.promoCode.lbl.city', 'City')
                                   ,$ln->gd('m.ecommerce.promoCode.lbl.cityCode', 'City Code')
                                   ,$ln->gd('m.ecommerce.promoCode.lbl.country', 'Country')
                                    )      
            , 'fieldClassArray' => array(
                  0 => 'w25p'
                , 1 => 'w50'
                , 2 => 'w50p'
            )            
        ));        
 
    }    
}