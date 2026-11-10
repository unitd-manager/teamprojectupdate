<?
class CP_Admin_Modules_Tradingsg_BatchHistoryLink_Functions
{
    function setModuleArray($modules){
        $modObj = $modules->getModuleObj('tradingsg_batchHistoryLink');
        $modules->registerModule($modObj, array(
            'tableName' => 'batch_history'
           ,'keyField'  => 'batch_history_id'
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
		WHERE batch_history_id = '{$hist_record_id}' 
		  AND status IS NULL
        ";
        $updateResult = $db->sql_query($sqlUpdate);
    }    
}
