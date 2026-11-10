<?
class CP_Admin_Modules_Ecommerce_Stock_Functions
{
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('ecommerce_stock');
        $modules->registerModule($modObj, array(
            'tableName'     => 'product_item'
           ,'title'         => 'Stock'
           ,'keyField'      => 'product_item_id'
           ,'hasFlagInList' => 0
        ));
    }

    /**
     *
     */
    function setLinksArray($inst) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $linkObj = $inst->getLinksArrayObj('ecommerce_stock', 'ecommerce_stockInLink');
        //$productArr = $fn->getDdDataAsArray($cpCfg['m.ecommerce.order.itemsMainModule']);

        $inst->registerLinksArray($linkObj, array(
             'historyTableName'       => 'stock_in'
            ,'linkingType'            => 'grid'
            ,'historyTableKeyField'   => 'stock_in_id'
            ,'showLinkPanelInEdit'    => 1
            ,'hasPortalEdit'          => 0
            ,'hasPortalDelete'        => 1
            ,'fieldlabel' => array(
                 'Date'
                ,'Qty'
            )
            ,'fieldClassArray'        => array()
            ,'showAnchorInLinkPortal' => false
            ,'fieldClassArray' => array()
            ,'showAnchorInLinkPortal' => false
            ,'gridFieldTypeArray'  => array(
                 array('type' => 'date')
                ,array('type' => 'textbox')
            )
            ,'additionalFieldsArray'  => array(
                 'b.stock_date'
                ,'b.qty'
            )
    ));
    }

}