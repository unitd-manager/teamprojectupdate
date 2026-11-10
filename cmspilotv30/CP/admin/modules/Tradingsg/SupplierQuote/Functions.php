<?
class CP_Admin_Modules_Tradingsg_SupplierQuote_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tradingsg_supplierQuote');
        $modules->registerModule($modObj, array(
            'title'     => 'Supplier Quote'
           ,'tableName' => 'supplier_quote'
           ,'keyField'  => 'supplier_quote_id'
           ,'actBtnsEdit' => array('save', 'apply', 'cancel')
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('tradingsg_supplierQuote', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
                
    }

    function setLinksArray($inst) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('dbUtil');


        $sqlProduct = "
        SELECT p.product_id
        	  ,p.title
        FROM product p
        ORDER BY p.title
        ";
       
        $prodresult = $db->sql_query($sqlProduct);
        $productArr = $dbUtil->getResultsetAsArrayForForm($prodresult);

        $sqlSupplier = "
        SELECT c.company_id
        	  ,c.company_name
        FROM company c
        WHERE category = 'Supplier'
        ORDER BY c.company_name
        ";
       
        $supplierResult = $db->sql_query($sqlSupplier);
        $supplierArr = $dbUtil->getResultsetAsArrayForForm($supplierResult);

        $statusArr = $cpCfg['m.tradingsg.supplierQuoteHistory.statusArr'];

            $linkObj = $inst->getLinksArrayObj('tradingsg_supplierQuote', 'tradingsg_supplierQuoteHistoryLink', array(
                'historyTableName' => 'supplier_quote_history'
               ,'historyTableKeyField' => 'supplier_quote_history_id'
               ,'hasPortalEdit' => 0
               ,'hasPortalDelete' => 0
               ,'linkingType' => 'grid'
               ,'portalListLimit' => 100
               ,'showLinkPanelInNew'  => 0
               ,'showLinkPanelInEdit' => 1
               ,'showRowSerialNo' => true
               ,'showAnchorInLinkPortal' => false
               ,'fieldlabel' => array('Name of the Item'
                                     ,'Qty'
                                     ,'Price'
                                     ,'Supplier'
                                     ,'Status'
                                     ,'Total Price'
                                     ,'View History'
                                     ,'Created By'
                                     ,'Modified By'
                                )
               ,'gridFieldTypeArray'  => array(
                    //array('type' => 'dropdown', 'ddArr' => $productArr)
                    array('type' => 'textbox')
                   ,array('type' => 'textbox', 'editable' => 1)
                   ,array('type' => 'textbox', 'editable' => 1)
                   ,array('type' => 'dropdown', 'ddArr' => $supplierArr)
                   ,array('type' => 'dropdown', 'ddArr' => $statusArr, 'useKey' => 0)
                   ,array('type' => 'textbox', 'editable' => 0)
                   ,array('type' => 'textbox', 'editable' => 0)
                   ,array('type' => 'textbox', 'editable' => 0)
                   ,array('type' => 'textbox', 'editable' => 0)
               )
               , 'fieldClassArray' => array(
                     0 => ''
                   , 1 => 'w50'
                   , 2 => 'w50'
                   , 3 => ''
                   , 4 => 'totalCp'
                   , 5 => 'w75'
                   , 6 => 'w50'
                   , 7 => 'w50'
                   , 8 => 'w50'
               )
               ,'summaryFieldsArray' => array(
                    'total_cost_price'
                )
            ));
        
        $inst->registerLinksArray($linkObj);

    }
}