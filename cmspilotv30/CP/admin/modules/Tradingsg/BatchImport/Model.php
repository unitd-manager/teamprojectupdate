<?
class CP_Admin_Modules_Tradingsg_BatchImport_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT bi.*
        FROM batch_import bi
        ";

        /*LEFT JOIN batch_history bh ON (bh.batch_import_id = bi.batch_import_id)
        LEFT JOIN product p ON (bh.product_id = p.product_id)
        LEFT JOIN company c ON (bh.company_id = c.company_id)*/
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'bi';

        $batch_import_id   = $fn->getReqParam('batch_import_id');
        $product_id        = $fn->getReqParam('product_id');
        $supplier_id       = $fn->getReqParam('supplier_id');

        if ($batch_import_id != "") {
            $searchVar->sqlSearchVar[] = "bi.batch_import_id = '{$batch_import_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "bi.batch_import_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'bi.batch_import_id');

            if ($product_id != "") {
                $searchVar->sqlSearchVar[] = "bh.product_id = '{$product_id}'";
            }

            if ($supplier_id != "") {
                $searchVar->sqlSearchVar[] = "bh.company_id = '{$supplier_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        bi.title   LIKE '%{$tv['keyword']}%'
				                    OR p.title LIKE '%{$tv['keyword']}%'
				                    OR p.item_code LIKE '%{$tv['keyword']}%'
				                    OR c.company_name LIKE '%{$tv['keyword']}%'
                                       )";
            }
            
        }        
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
		$validate = Zend_Registry::get('validate');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);

		$SQL = "
		SELECT bh.*
		FROM batch_history bh
		WHERE batch_import_id = '{$id}'
		";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
			$SQL1 = "
			SELECT sh.*
			FROM stock_history sh
			WHERE batch_history_id = '{$row['batch_history_id']}'
			AND batch_import_id = '{$id}'
			AND sh.status IS NULL
			";
	        $result1 = $db->sql_query($SQL1);
			$row1 = $db->sql_fetchrow($result1);

	        $numRows1  = $db->sql_numrows($result1);

			if ($numRows1 > 0){
				if ($row['product_id'] != $row1['product_id'] 
				 || $row['qty'] != $row1['qty'] 
				 || $row['price'] != $row1['price'] 
				 || $row['company_id'] != $row1['company_id']
				) {

					$updateSql= "
					UPDATE stock_history
					SET status = 'Updated'
					WHERE batch_history_id = '{$row['batch_history_id']}'
					AND batch_import_id = '{$id}'
					";
			        $updateResult = $db->sql_query($updateSql);

		            $fa = array();
		            $fa['product_id']       = $row['product_id'];
		            $fa['qty']		        = $row['qty'];
		            $fa['price']            = $row['price'];
		            $fa['company_id']       = $row['company_id'];		
		            $fa['batch_import_id']  = $id;
		            $fa['batch_history_id'] = $row['batch_history_id'];		            
		            $fa['creation_date']    = date("Y-m-d H:i:s");
		            $fa['created_by']       = $fn->getSessionParam('userName');
		           
		            $insertSQL              = $dbUtil->getInsertSQLStringFromArray($fa, 'stock_history');
		            $insertResult           = $db->sql_query($insertSQL);
				}
				
			} else{
	            $fa = array();
	            $fa['product_id']       = $row['product_id'];
	            $fa['qty']		        = $row['qty'];
	            $fa['price']            = $row['price'];
	            $fa['company_id']       = $row['company_id'];
	            //$fa['status']           = 'New';
	
	            $fa['batch_import_id']  = $id;
	            $fa['batch_history_id'] = $row['batch_history_id'];
	            
	            $fa['creation_date']    = date("Y-m-d H:i:s");
	            $fa['created_by']       = $fn->getSessionParam('userName');
	           
	            $insertSQL            = $dbUtil->getInsertSQLStringFromArray($fa, 'stock_history');
	            $insertResult         = $db->sql_query($insertSQL);
			}
		}		
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'batch_date');
        $fa = $fn->addToFieldsArray($fa, 'freight_cost');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'batch_import_id');
        
        return $fa;
    }

    /**
     *
     */
    function getTradingsgBatchImportTradingsgBatchHistoryLinkSQL($id) {
        
        $SQL = "
        SELECT bh.batch_history_id
              ,bh.product_id
              ,bh.qty
              ,bh.price
              ,bh.company_id
	          ,round(
	          (bh.qty  * bh.price) ,2) 
    	      as total_cost_price
              ,(SELECT SUM(round(
              (bhi.qty * bhi.price),2))
              FROM batch_history bhi WHERE bhi.batch_import_id = {$id}) as total_cost_price_sum
        FROM batch_history bh
        LEFT JOIN batch_import bi ON (bi.batch_import_id = bh.batch_import_id)
        WHERE bh.batch_import_id = {$id}
        ";
        return $SQL;
    }

    /**
     *
     */    
    function getSupplierJsonByProductId() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $product_id = $fn->getReqParam('product_id', '', true);

        $json  = array();
        
        $SQL = $this->getSupplierByProductSQL($product_id);
        $result = $db->sql_query($SQL);  
        $numRows = $db->sql_numrows($result);
        
        if ($numRows == 0) {
            $json[] = array('value' => '', 'caption' => 'Please Select');
        }
        
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row['company_id'], "caption" => $row['company_name']);
        }
        
        return json_encode($json);
    }

    /**
     *
     */
    function getSupplierByProductSQL($product_id = 0) {

        $append = "WHERE pc.product_id = {$product_id}";

        $sql = "
        SELECT pc.company_id
              ,c.company_name
        FROM product_company pc
        LEFT JOIN company c ON (c.company_id = pc.company_id)
        {$append}
        ORDER BY c.company_name
        ";
        return $sql;
    }

    function getUpdateTotalCostPrice() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $rec_id     = $fn->getReqParam('rec_id');
        $qty        = $fn->getReqParam('qty');
        $price      = $fn->getReqParam('price');

        $batchImportRec = $fn->getRecordRowById('batch_history', 'batch_history_id', $rec_id);

		$batch_import_id = $batchImportRec['batch_import_id'];        

        if($qty > 0){
            $SQLUpdate    = "
            UPDATE batch_history 
            set qty = {$qty}      
            WHERE batch_history_id = {$rec_id}
            "; 
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($price > 0){
            $SQLUpdate    = "
            UPDATE batch_history 
            set price = {$price}      
            WHERE batch_history_id = {$rec_id}
            "; 
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

 		$cost_price = $qty * $price ;
        
        $SQL ="
          SELECT SUM(round(
          (bhi.qty * bhi.price),2)) AS total_cost_price_sum
          FROM batch_history bhi WHERE bhi.batch_import_id = {$batch_import_id}
        ";
        
        $resultUpdate = $db->sql_query($SQL);
        $row          = $db->sql_fetchrow($resultUpdate);
        
        $arr['total_cost_price_sum'] = $row['total_cost_price_sum'];       

        $arr['cost_price']           = round($cost_price,2);
        
        return $cpUtil->getJsonFromArray($arr);
    }
}
