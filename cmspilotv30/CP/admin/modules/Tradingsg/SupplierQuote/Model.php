<?
class CP_Admin_Modules_Tradingsg_SupplierQuote_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT sq.*
        FROM supplier_quote sq
        ";

        /*LEFT JOIN supplier_quote_history sqh ON (sqh.supplier_quote_id = sq.supplier_quote_id)
        LEFT JOIN product p ON (sqh.product_id = p.product_id)
        LEFT JOIN company c ON (sqh.company_id = c.company_id)*/
        
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'sq';

        $supplier_quote_id   = $fn->getReqParam('supplier_quote_id');
        $product_id        = $fn->getReqParam('product_id');
        $supplier_id       = $fn->getReqParam('supplier_id');

        if ($supplier_quote_id != "") {
            $searchVar->sqlSearchVar[] = "sq.supplier_quote_id = '{$supplier_quote_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "sq.supplier_quote_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'sq.supplier_quote_id');

            if ($product_id != "") {
                $searchVar->sqlSearchVar[] = "sqh.product_id = '{$product_id}'";
            }

            if ($supplier_id != "") {
                $searchVar->sqlSearchVar[] = "sqh.company_id = '{$supplier_id}'";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                                        sq.title   LIKE '%{$tv['keyword']}%'
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
		SELECT sqh.*
		FROM supplier_quote_history sqh
		WHERE supplier_quote_id = '{$id}'
		";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
			$SQL1 = "
			SELECT sh.*
			FROM stock_history sh
			WHERE supplier_quote_history_id = '{$row['supplier_quote_history_id']}'
			AND supplier_quote_id = '{$id}'
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
					WHERE supplier_quote_history_id = '{$row['supplier_quote_history_id']}'
					AND supplier_quote_id = '{$id}'
					";
			        $updateResult = $db->sql_query($updateSql);

		            $fa = array();
		            $fa['product_id']       = $row['product_id'];
		            $fa['qty']		        = $row['qty'];
		            $fa['price']            = $row['price'];
		            $fa['company_id']       = $row['company_id'];		
		            $fa['supplier_quote_id']  = $id;
		            $fa['supplier_quote_history_id'] = $row['supplier_quote_history_id'];		            
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
	
	            $fa['supplier_quote_id']  = $id;
	            $fa['supplier_quote_history_id'] = $row['supplier_quote_history_id'];
	            
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
        $fa = $fn->addToFieldsArray($fa, 'date');
        $fa = $fn->addToFieldsArray($fa, 'freight_cost');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'supplier_quote_id');
        
        return $fa;
    }

    /**
     *
     */
    function getTradingsgSupplierQuoteTradingsgSupplierQuoteHistoryLinkSQL($id) {
            
        $SQL = "
        SELECT sqh.supplier_quote_history_id
              ,p.title AS product_title
              ,sqh.qty
              ,sqh.price
              ,sqh.supplier_id
              ,sqh.status
	          ,round(
	          (sqh.qty  * sqh.price) ,2) 
    	      as total_cost_price
              ,(SELECT SUM(round(
              (sqhi.qty * sqhi.price),2))
              FROM supplier_quote_history sqhi WHERE sqhi.supplier_quote_id = {$id}) as total_cost_price_sum
              ,CONCAT_WS('', '<a href=index.php?_topRm=inventory&module=tradingsg_supplierQuote&_spAction=productViewHistory&showHTML=0&supplier_quote_history_id=', sqh.supplier_quote_history_id, ' class=supplierViewHistory', '>View History</a>') as view_history
              ,CONCAT_WS(' - ', sqh.created_by, sqh.creation_date) AS created_by_user
              ,CONCAT_WS(' - ', sqh.modified_by, sqh.modification_date) AS modified_by_user
        FROM supplier_quote_history sqh
        LEFT JOIN supplier_quote sq ON (sq.supplier_quote_id = sqh.supplier_quote_id)
        LEFT JOIN product p ON (p.product_id = sqh.product_id)
        WHERE sqh.supplier_quote_id = {$id}
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

        $supplierQuoteRec = $fn->getRecordRowById('supplier_quote_history', 'supplier_quote_history_id', $rec_id);

		$supplier_quote_id = $supplierQuoteRec['supplier_quote_id'];        

        if($qty > 0){
            $SQLUpdate    = "
            UPDATE supplier_quote_history 
            set qty = {$qty}      
            WHERE supplier_quote_history_id = {$rec_id}
            "; 
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($price > 0){
            $SQLUpdate    = "
            UPDATE supplier_quote_history 
            set price = {$price}      
            WHERE supplier_quote_history_id = {$rec_id}
            "; 
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

 		$cost_price = $qty * $price ;
        
        $SQL ="
          SELECT SUM(round(
          (sqhi.qty * sqhi.price),2)) AS total_cost_price_sum
          FROM supplier_quote_history sqhi WHERE sqhi.supplier_quote_id = {$supplier_quote_id}
        ";
        
        $resultUpdate = $db->sql_query($SQL);
        $row          = $db->sql_fetchrow($resultUpdate);
        
        $arr['total_cost_price_sum'] = $row['total_cost_price_sum'];       

        $arr['cost_price']           = round($cost_price,2);
        
        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getRaisePurchaseOrder() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $supplier_quote_id  = $fn->getReqParam('id');

        $SQL = "
        SELECT sqh.*
              ,p.title AS product_title
              ,p.unit
              ,sq.title
              ,sq.date
              ,c.company_name
        FROM supplier_quote_history sqh
        LEFT JOIN product p ON (p.product_id = sqh.product_id)
        LEFT JOIN supplier_quote sq ON (sq.supplier_quote_id = sqh.supplier_quote_id)
        LEFT JOIN company c ON (c.company_id = sqh.supplier_id)
        WHERE sqh.supplier_quote_id = {$supplier_quote_id}
          AND sqh.supplier_id != ''
          AND sqh.product_id > 0
          AND sqh.status = 'confirmed'
        GROUP BY c.company_name
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            //To check if the po is already created or not, if not create a purchase order
            $purchaseOrderRec = $fn->getRecordByCondition('purchase_order', 
                                                      "company_id_supplier = '{$row['supplier_id']}' AND supplier_quote_id = {$supplier_quote_id}");

            if(is_array($purchaseOrderRec)){
                $purchase_order_id = $purchaseOrderRec['purchase_order_id'];
            } else {
                //Getting max code to create po
                $fa = array();
                $fa['supplier_quote_id']   = $supplier_quote_id;
                //$fa['status'] = 'new';
                $fa['company_id_supplier'] = $row['supplier_id'];
                $fa['purchase_order_date'] = date('Y-m-d');
                $fa['po_code']             = $this->getUpdatePOCode();

                $purchase_order_id = $fn->addRecord($fa, 'purchase_order');
            }
                       
            //This sql is used to get the values from supplier_quote_history. Below code will create the record in po and po_product history table .
            $SQLSelect = "
            SELECT sqh.*
                  ,p.title AS product_title
                  ,p.unit
                  ,sq.title
                  ,sq.date
                  ,c.company_name
            FROM supplier_quote_history sqh
            LEFT JOIN product p ON (p.product_id = sqh.product_id)
            LEFT JOIN supplier_quote sq ON (sq.supplier_quote_id = sqh.supplier_quote_id)
            LEFT JOIN company c ON (c.company_id = sqh.supplier_id)
            WHERE sqh.supplier_quote_id = {$supplier_quote_id}
              AND sqh.supplier_id = {$row['supplier_id']}
              AND sqh.product_id > 0
              AND sqh.status = 'confirmed'
            ORDER BY p.title
            ";
            $resultSelect = $db->sql_query($SQLSelect); 

            while ($rowSupHist = $db->sql_fetchrow($resultSelect)) {
                $fa1 = array();
                $fa1['product_id']                = $rowSupHist['product_id'];
                $fa1['price']                     = $rowSupHist['price'];
                $fa1['qty']                       = $rowSupHist['qty'];
                $fa1['supplier_quote_history_id'] = $rowSupHist['supplier_quote_history_id'];
                $fa1['supplier_id']               = $row['supplier_id'];
                $fa1['purchase_order_id']         = $purchase_order_id;

                /*
                $poProductRec = $fn->getRecordByCondition('po_product', 
                                                          "supplier_id = {$row['supplier_id']} AND supplier_quote_history_id = {$row['supplier_quote_history_id']}");
                                                          */
                $poProductRec = $fn->getRecordRowById('po_product',         'supplier_quote_history_id', $rowSupHist['supplier_quote_history_id']);
                
                //to update the po records if already present else create new
                if(is_array($poProductRec)){
                    $fn->saveRecord($fa1, 'po_product', 'po_product_id', $poProductRec['po_product_id']);
                } else {
                    $po_product_id = $fn->addRecord($fa1, 'po_product');
                }

                //to update the modified by and modification date fields in po
                $fa3 = array();
                $fa3['company_id_supplier']  = $row['supplier_id'];
                $fn->saveRecord($fa3, 'purchase_order', 'purchase_order_id', $purchase_order_id);
            }
            
        }        
        
        //delete unwanted purchase order record and related po product records
        $POSql = " 
        SELECT purchase_order_id FROM purchase_order 
        WHERE supplier_quote_id = {$supplier_quote_id} AND company_id_supplier NOT IN
        (SELECT supplier_id FROM supplier_quote_history WHERE supplier_quote_id = {$supplier_quote_id} AND product_id > 0 AND status= 'confirmed')
        ";
        $resultPOSql = $db->sql_query($POSql); 
        while ($rowPOSql = $db->sql_fetchrow($resultPOSql)) {
            $deleteSql = "
            DELETE FROM po_product 
            WHERE purchase_order_id = {$rowPOSql['purchase_order_id']}
            ";
            $resultDelete = $db->sql_query($deleteSql); 
            
            $deleteSql = "
            DELETE FROM purchase_order 
            WHERE purchase_order_id = {$rowPOSql['purchase_order_id']}
            ";
            $resultDelete = $db->sql_query($deleteSql); 
        }
    }

    /**
     *
     */
    function getUpdatePOCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Purchase order Code */
        $poCode = $fn->getSettingsValueByKey("poCode");

        if($poCode < 10){
            $POCode = $fn->getSettingsValueByKey('poCodePrefix') . $poCode;
        }
        else if($poCode < 99){
            $POCode = $fn->getSettingsValueByKey('poCodePrefix') . $poCode;
        }
        else if($poCode > 99 || $nextOppCode < 999){
            $POCode = $fn->getSettingsValueByKey('poCodePrefix') . $poCode;
        }
        else{
            $POCode = $fn->getSettingsValueByKey('poCodePrefix') . $poCode;
        }
        
        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'poCode'";
        $result = $db->sql_query($SQL);

        return $POCode;
    }

    /**
     *
     */
    function getSearchProductTitle() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title = $fn->getReqParam('term', '', true);
        $extractor = explode(" **** ", $title);
        
        $productTitle = $extractor[0];
        
        $SQL = "
        SELECT p.title AS value
              ,p.title AS label
              ,CONCAT_WS(' **** ', p.title, p.price, p.unit, c.company_name) AS label
        	  ,p.product_id AS id
        FROM product p
        LEFT JOIN product_company pc ON (pc.product_id = p.product_id)
        LEFT JOIN company c ON (c.company_id = pc.company_id)
        WHERE p.title LIKE '%{$productTitle}%'
        AND p.published = 1
        ORDER BY p.title
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getUpdateQtyDelivered() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $po_product_id  = $fn->getReqParam('po_product_id');
        $qtyDelivered   = $fn->getReqParam('qtyDelivered');
        $qtyCancelled   = $fn->getReqParam('qtyCancelled');
        
        $fa = array();
        $fa['qty_delivered']  = $qtyDelivered;
        $fa['qty_cancelled']  = $qtyCancelled;
        $fn->saveRecord($fa, 'po_product', 'po_product_id', $po_product_id);

        $poProductRec = $fn->getRecordRowById('po_product','po_product_id', $po_product_id);
        //to update the modified by and modification date fields in po
        $fa3 = array();
        $fn->saveRecord($fa3, 'purchase_order', 'purchase_order_id', $poProductRec['purchase_order_id']);
        
    }
    /**
     *
     */
     function getUpdateSupplierProductLineItems() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $product_id = $fn->getReqParam('product_id');
        $rec_id = $fn->getReqParam('rec_id');
        $id = $tv['srcRoomId'];
        
        $arr = array('price' => 0);

        $SQLUpdate    = "
        UPDATE supplier_quote_history
        set product_id = {$product_id}
        WHERE supplier_quote_history_id = {$rec_id}
        "; 
        $resultUpdate = $db->sql_query($SQLUpdate);
        
        
        return $cpUtil->getJsonFromArray($arr);
    }


    /**
     *
     */
    /**
     *
     */
    function getAddProductFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        
        $unit 				= $fn->getPostParam('unit');
        $description 		= $fn->getPostParam('description');
        $product_title 		= $fn->getPostParam('title');
        $product_group_id 	= $fn->getPostParam('product_group_id');
        $price 		        = $fn->getPostParam('price');

        /*if (!$this->getAddProductFormValidate()){
            return $validate->getErrorMessageXML();
        }*/
        

        $fa = array();
        $fa['unit'] 			= $unit;
        $fa['title']         	= $product_title;
        $fa['description'] 		= $description;
	    $fa['product_group_id'] = $product_group_id;
        $fa['item_code']        = $this->getUpdateProductCode();
	    $fa['published']        = 1;
        
        $insert = $dbUtil->getInsertSQLStringFromArray($fa, 'product');
        $result = $db->sql_query($insert);
        $id     = $db->sql_nextid();
            
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getUpdateProductCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Product Code */
        $nextProductItemCode = $fn->getSettingsValueByKey("nextProductItemCode");

        if($nextProductItemCode < 10){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '000' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 99){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '00' . $nextProductItemCode;
        }
        else if($nextProductItemCode < 999){
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . '0' . $nextProductItemCode;
        }
        else{
            $ProCode = $fn->getSettingsValueByKey('productCodePrefix') . $nextProductItemCode;
        }
        
        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextProductItemCode'";
        $result = $db->sql_query($SQL);

        return $ProCode;
    }

    /**
     *
     */
    function getAddProductFormValidate() {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('unit', 'Please Select the unit');
        $validate->validateData('product_group_id', 'Please Select the Product Group');
        $validate->validateData('title', 'Please Enter the Product Name');
        

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

}
