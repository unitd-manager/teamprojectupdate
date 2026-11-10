<?
class CP_Admin_Modules_Ecommerce_Product_Functions extends CP_Common_Modules_Ecommerce_Product_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_product');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList'   => array('new', 'import')
        ));
    }
}