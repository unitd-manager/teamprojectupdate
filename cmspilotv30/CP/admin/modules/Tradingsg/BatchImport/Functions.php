<?
class CP_Admin_Modules_Tradingsg_BatchImport_Functions
{
    /**
     *
     */
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tradingsg_batchImport');
        $modules->registerModule($modObj, array(
            'title'     => 'Batch Import'
           ,'tableName' => 'batch_import'
           ,'keyField'  => 'batch_import_id'
        ));
    }

    /**
     *
     */
    function setMediaArray($mediaArr) {

        //------------------------------------------------------------------------------//
        $mediaObj = $mediaArr->getMediaObj('tradingsg_batchImport', 'attachment', 'attachment');

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
        WHERE category = 'Supplier';
        ";
       
        $supplierResult = $db->sql_query($sqlSupplier);
        $supplierArr = $dbUtil->getResultsetAsArrayForForm($supplierResult);

            $linkObj = $inst->getLinksArrayObj('tradingsg_batchImport', 'tradingsg_batchHistoryLink', array(
                'historyTableName' => 'batch_history'
               ,'historyTableKeyField' => 'batch_history_id'
               ,'hasPortalEdit' => 0
               ,'hasPortalDelete' => 1
               ,'linkingType' => 'grid'
               ,'portalListLimit' => 100
               ,'showLinkPanelInNew'  => 0
               ,'showLinkPanelInEdit' => 1
               ,'showRowSerialNo' => false
               ,'showAnchorInLinkPortal' => false
               ,'fieldlabel' => array('Product'
                                     ,'Quantity'
                                     ,'Price'
                                     ,'Supplier'
                                     ,'Total Price'
                                )
               ,'gridFieldTypeArray'  => array(
                    array('type' => 'dropdown', 'ddArr' => $productArr)
                   ,array('type' => 'textbox', 'editable' => 1)
                   ,array('type' => 'textbox', 'editable' => 1)
                   ,array('type' => 'dropdown', 'ddArr' => $supplierArr)
                   ,array('type' => 'textbox', 'editable' => 0)
               )
               , 'fieldClassArray' => array(
                     0 => ''
                   , 1 => 'w50'
                   , 2 => 'w50'
                   , 3 => ''
                   , 4 => 'totalCp'
               )
               ,'summaryFieldsArray' => array(
                    'total_cost_price'
                )
            ));
        
        $inst->registerLinksArray($linkObj);

    }
}