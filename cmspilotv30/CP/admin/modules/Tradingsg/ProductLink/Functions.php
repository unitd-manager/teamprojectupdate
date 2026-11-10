<?
class CP_Admin_Modules_Tradingsg_ProductLink_Functions
{
    //==================================================================//
    function setModuleArray($modules){
        $cpCfg = Zend_Registry::get('cpCfg');
        $modObj = $modules->getModuleObj('tradingsg_productLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'quote_product'
           ,'keyField'  => 'quote_product_id'
        ));
    }

    /**
     * @param type $record_id
     * @return string
    */
    function beforeDeletePortalHandler($hist_record_id, $linkName){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');


        $qpRec = $fn->getRecordRowByID('quote_product', 'quote_product_id', $hist_record_id);

        if($qpRec['product_id'] > 0){
            $expPoRec = array('condn' => " AND product_id = {$qpRec['product_id']}");
            $poRec    = $fn->getRecordRowByID('po_product', 'quote_id', $qpRec['quote_id'], $expPoRec);

            $delateSql = "
            DELETE FROM po_product 
            WHERE product_id = {$qpRec['product_id']} 
            AND quote_id = {$qpRec['quote_id']}
            ";
            $result = $db->sql_query($delateSql);
            
            if($poRec['purchase_order_id'] != ''){                
                $poSQL = "SELECT purchase_order_id
                          FROM po_product
                          WHERE purchase_order_id = {$poRec['purchase_order_id']}
                ";
                $result = $db->sql_query($poSQL);
    
                $numRows  = $db->sql_numrows($result);
                if ($numRows != 0){
                    $delateSql = "
                    DELETE FROM purchase_order 
                    WHERE purchase_order_id = {$poRec['purchase_order_id']} 
                    ";
                    $result = $db->sql_query($delateSql);
                }
            }
        }
    }    
}
