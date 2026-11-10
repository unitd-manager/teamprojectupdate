<?
class CP_Admin_Modules_Ecommerce_WishList_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('ecommerce_wishList');
        $modObj['tableName'] = 'wish_list';
        $modObj['keyField']  = 'wish_list_id';
        $modules->registerModule($modObj, array(
            'hasMultiLang'  => 1
           ,'title'         => 'Wish List'
        ));
    }

    //==================================================================//
    //==================================================================//
    function getQuickSearch() {
    }

    //==================================================================//
    function setMediaArray($mediaArr) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

    }
}