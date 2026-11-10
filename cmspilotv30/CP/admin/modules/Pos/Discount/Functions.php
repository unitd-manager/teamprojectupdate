<?
class CP_Admin_Modules_Pos_Discount_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('pos_discount');
        $modules->registerModule($modObj, array(
            'hasMultiLang' => 1
           ,'hasFlagInList' => 0
           ,'actBtnsList' => array('new', 'printListScreen')
           ,'actBtnsDetail' => array('edit', 'delete', 'printListScreen')
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_discount', 'pos_shopLink');

        $inst->registerLinksArray($linkObj, array(
            'historyTableName'      => 'discount_shop'
           ,'historyTableKeyField'  => 'discount_shop_id'
           ,'fieldlabel'            => array('Title')
           ,'linkingType'           => 'modal'
           ,'showLinkPanelInEdit'   => 1
        ));

        //------------------------------------------------------------------------------//
        $linkObj = $inst->getLinksArrayObj('pos_discount', 'pos_productLink');

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'  => 'discount_product'
            ,'showAnchorInLinkPortal' => 1
        ));
    }
}