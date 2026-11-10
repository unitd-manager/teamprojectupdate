<?
class CP_Admin_Modules_Tradingsg_SupplierQuoteHistoryLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tradingsg_supplierQuoteHistoryLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'supplier_quote_history'
           ,'keyField'  => 'supplier_quote_history_id'
        ));
    }

    /**
     * @param type $record_id
     * @return string
    */
    function beforeDeletePortalHandler($hist_record_id, $linkName){
        $db = Zend_Registry::get('db');

        $sqlUpdate = "
		UPDATE stock_history
		SET status = 'Deleted'
		WHERE supplier_quote_history_id = '{$hist_record_id}' 
		  AND status IS NULL
        ";
        $updateResult = $db->sql_query($sqlUpdate);
    }    
}
