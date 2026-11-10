<?
class CP_Admin_Modules_Tradingsg_Quote_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $fnsModDeliveryAddress = getCPFnObj('trading_deliveryAddressLink');

        $SQL = "
      	SELECT q.*
      	      ,co.company_name
              ,CONCAT_WS(' ', con.first_name, con.last_name) AS contact_name
              ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
      	FROM quote q
        LEFT JOIN company co ON (q.company_id = co.company_id)
      	LEFT JOIN contact con ON (q.contact_id = con.contact_id)
      	LEFT JOIN staff s ON (q.staff_id = s.staff_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'q';

        $status 	   = $fn->getReqParam('status');
        $priority 	   = $fn->getReqParam('priority');
        $company_id    = $fn->getReqParam('company_id');
        $quoteDate1    = $fn->getReqParam('quoteDate1');
        $quoteDate2    = $fn->getReqParam('quoteDate2');
        $deliveryDate1 = $fn->getReqParam('deliveryDate1');
        $deliveryDate2 = $fn->getReqParam('deliveryDate2');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "q.quote_id = {$tv['record_id']}";
        } else {
            if ($_SESSION['userGroupName'] == "SALES") {
                $searchVar->sqlSearchVar[] = "q.staff_id = {$_SESSION['staff_id']}";
            }

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "q.status = '{$status}'";
            }

            if ($quoteDate1 != "" && $quoteDate1 != "From"
            && $quoteDate2 != "" && $quoteDate2 != "To" ) {
                $searchVar->sqlSearchVar[] = "(q.quote_date BETWEEN '{$quoteDate1}' AND '{$quoteDate2}')";
            }

            if ($quoteDate1 != "" && $quoteDate1 != "From" && $quoteDate2 == "To") {
                $searchVar->sqlSearchVar[] = "(q.quote_date >= '{$quoteDate1}')";
            }


            if ($quoteDate2 != "" && ($quoteDate1 == "From"
            || $quoteDate1 == "") && $quoteDate2 != "To") {
                $searchVar->sqlSearchVar[] = "(q.quote_date <= '{$quoteDate2}')";
            }

            if ($deliveryDate1 != "" && $deliveryDate2 != "") {
                $searchVar->sqlSearchVar[] = "(q.delivery_date BETWEEN '{$deliveryDate1}' AND '{$deliveryDate2}')";
            } else if ($deliveryDate1 != "" && $deliveryDate2 == "") {
                $deliveryDate2 = date('Y-m-d');
                $searchVar->sqlSearchVar[] = "(q.delivery_date BETWEEN '{$deliveryDate1}' AND '{$deliveryDate2}')";
            } else if ($deliveryDate1 == "" && $deliveryDate2 != "") {
                $deliveryDate1 = date('Y') . '-01-01';
                $searchVar->sqlSearchVar[] = "(q.delivery_date BETWEEN '{$deliveryDate1}' AND '{$deliveryDate2}')";
            }

            if ($priority != "") {
                $searchVar->sqlSearchVar[] = "q.priority = '{$priority}'";
            }
            if ($company_id != "") {
                $searchVar->sqlSearchVar[] = "q.company_id = '{$company_id}'";
            }
            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "q.flag = 1";
            }
            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(q.flag != 1 OR q.flag IS null)";
            }
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       q.quote_code LIKE '%{$tv['keyword']}%'
                    OR co.company_name LIKE '%{$tv['keyword']}%'
                    OR con.first_name LIKE '%{$tv['keyword']}%'
                    OR con.last_name LIKE '%{$tv['keyword']}%'
                    OR q.title LIKE '%{$tv['keyword']}%'
                    OR q.delivery_location LIKE '%{$tv['keyword']}%'
                )";
            }
        }
        $searchVar->sortOrder = "q.creation_date DESC";

    }

    /**
    */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('quote_type', 'Please select the quote type');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('company_id', 'Please enter Client Name');
        $validate->validateData('title', 'Please enter Title');

        //$validate->validateData('subject', 'Please enter enquiry title');


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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        if ($cpCfg['countryForCurrency'] == 'India'){
            $currency = 'INR';
        } else if ($cpCfg['countryForCurrency'] == 'Singapore'){
            $currency = 'SGD';
        } else {
            $currency = '';
        }

        $SQL = "SELECT max(quote_code) AS quote_code FROM quote";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $fa = $this->getFields();
        $fa['quote_code']       = $this->getUpdateQuoteCode();
        $fa['staff_id']         = $_SESSION['staff_id'];
        $fa['status']           = 'New';
        $fa['priority']         = 'Medium';
        $fa['follow_up_date']   = date("Y-m-d", strtotime('+1 week'));
        $fa['currency']         = $currency;
        $fa['quote_date']       = date("Y-m-d");

        $id = $fn->addRecord($fa);

        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getUpdateQuoteCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Quote Code */
        $nextQuoteCode = $fn->getSettingsValueByKey("nextQuoteCode");

        $current_year = date('y');
        $current_month = date('m');

        /*
        if($nextQuoteCode < 10){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix') .  ($current_year) . '-' . ($current_month) . '-' . $nextQuoteCode;
        }
        else if($nextQuoteCode < 99){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix') . ($current_year) . '-' . ($current_month) . '-' . $nextQuoteCode;
        }
        else if($nextQuoteCode > 99 || $nextOppCode < 999){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix') . ($current_year) . '-' . ($current_month) . '-' . $nextQuoteCode;
        }
        else{
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix') . ($current_year) . '-' . ($current_month) . '-' . $nextQuoteCode;
        }
        */

        if($nextQuoteCode < 10){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix') . $nextQuoteCode;
        }
        else if($nextQuoteCode < 99){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix'). $nextQuoteCode;
        }
        else if($nextQuoteCode > 99 || $nextOppCode < 999){
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix') . $nextQuoteCode;
        }
        else{
            $quoteCode = $fn->getSettingsValueByKey('quoteCodePrefix')  . $nextQuoteCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextQuoteCode'";
        $result = $db->sql_query($SQL);

        return $quoteCode;
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $quote_id = $fn->getReqParam('quote_id');
        $status = $fn->getReqParam('status');
        $shipping_method = $fn->getReqParam('shipping_method');
        $delivery_terms = $fn->getReqParam('delivery_terms');

        $rowQuote = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);

        //if status changed
        if ($status != $rowQuote['status']) {
            //if quote cancelled then revert the stock status
            if ($status == 'cancelled') {
                getCPModelObj('trading_inventory')
                ->getRevertStockStatus('trading_quote', $quote_id);
            }

        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        //update history records in bulk
        /*if ($shipping_method != $rowQuote['shipping_method']) {
            $SQL = "
            UPDATE quote_items
            SET shipping_method = '{$shipping_method}'
            WHERE quote_id = {$quote_id}
              AND (shipping_method = '' OR shipping_method IS NULL)
            ";
            $db->sql_query($SQL);
        } */

        /*
        if ($delivery_terms != $rowQuote['delivery_terms']) {
            $SQL = "
            UPDATE quote_items
            SET delivery_terms = '{$delivery_terms}'
            WHERE quote_id = {$quote_id}
              AND (delivery_terms = '' OR delivery_terms IS NULL)
            ";
            $db->sql_query($SQL);
        }
        */
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields(){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'quote_code');
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'company_id_customer');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'priority');
        $fa = $fn->addToFieldsArray($fa, 'quote_date');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'currency');
        $fa = $fn->addToFieldsArray($fa, 'note');
        $fa = $fn->addToFieldsArray($fa, 'delivery_terms');
        $fa = $fn->addToFieldsArray($fa, 'payment_terms');
        $fa = $fn->addToFieldsArray($fa, 'creation_date');
        $fa = $fn->addToFieldsArray($fa, 'tax_percentage');
        $fa = $fn->addToFieldsArray($fa, 'shipping_method');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'delivery_date');
        $fa = $fn->addToFieldsArray($fa, 'delivery_location');
        $fa = $fn->addToFieldsArray($fa, 'quote_type');

        return $fa;
    }
    /**
     *
     */
    function getTradingsgQuoteTradingsgProductLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $product_group_id   = $fn->getReqParam('product_group_id');

        $whereSQL = '';
        $discount = '';
        $discountSum ='';

        if ($product_group_id != "") {
            $whereSQL .= " AND pg.product_group_id = '{$product_group_id}'";
        }

        //TO CHECK IF THE SUM OF DISCOUNT TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForPercentSum = "
        SELECT SUM(round(((qp.selling_price * qp.discount_percentage )/100)* qp.qty,2)) as discount_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$id}
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['discount_sum'] > 0){
            $subSqlForPercentSum = "
            SELECT SUM(round(((qp.selling_price * qp.discount_percentage )/100)* qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$id}
            ";
        }
        else{
            $subSqlForPercentSum = 0;
        }

        //TO CHECK IF THE SUM OF DISCOUNT TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        /*
        $subSqlForValueSum ="
        SELECT SUM(round(qp.discount_percentage  * qp.qty,2)) as discount_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$id}
            AND qp.discount_type = 'Value'
        ";
        $resultSubSql = $db->sql_query($subSqlForValueSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['discount_sum'] > 0){
            $subSqlForValueSum ="
            SELECT SUM(round(qp.discount_percentage  * qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$id}
                AND qp.discount_type = 'Value'
            ";
        }
        else{
            $subSqlForValueSum = 0;
        }
        */
            $subSqlForValueSum = 0;

        //TO CHECK IF THE SUM OF MARK UP TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForMarkUpPercentSum = "
        SELECT SUM(round(((qp.cost_price * qp.mark_up )/100)* qp.qty,2)) as mark_up_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$id}
            AND qp.mark_up_type = '%'
        ";
        $resultSubSql = $db->sql_query($subSqlForMarkUpPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['mark_up_sum'] > 0){
            $subSqlForMarkUpPercentSum = "
            SELECT SUM(round(((qp.cost_price * qp.mark_up )/100)* qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$id}
            AND qp.mark_up_type = '%'
            ";
        }
        else{
            $subSqlForMarkUpPercentSum = 0;
        }


        //TO CHECK IF THE SUM OF MARK UP TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForMarkUpValueSum ="
        SELECT SUM(round(qp.mark_up * qp.qty,2)) as mark_up_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$id}
            AND qp.mark_up_type = 'Value'
        ";
        $resultSubSql = $db->sql_query($subSqlForMarkUpValueSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['mark_up_sum'] > 0){
            $subSqlForMarkUpValueSum ="
            SELECT SUM(round(qp.mark_up * qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$id}
                AND qp.mark_up_type = 'Value'
            ";
        }
        else{
            $subSqlForMarkUpValueSum = 0;
        }

        $SQL = "
        SELECT qp.quote_product_id
              ,p.item_code
              ,p.title AS product_title
              ,qp.client_id
              ,qp.cost_price
              ,p.unit
              ,qp.qty
              ,IF(
              (SELECT qphist.cost_price
                FROM quote_product qphist
                LEFT JOIN quote qt ON ( qt.quote_id = qphist.quote_id )
                WHERE qt.company_id = q.company_id
                AND qphist.product_id = qp.product_id
                AND qphist.product_id > 0
                AND qphist.quote_product_id != qp.quote_product_id
                ORDER BY qphist.quote_id
                LIMIT 0 , 1
                )
                ,CONCAT_WS(''
                ,'<a href=index.php?_topRm=order&module=tradingsg_quote&_spAction=previousOrderForClient&showHTML=0&quote_product_id=', qp.quote_product_id, '  class=productViewHistory', '> View</a>', '::'
                ,(SELECT qphist.cost_price
                FROM quote_product qphist
                LEFT JOIN quote qt ON ( qt.quote_id = qphist.quote_id )
                WHERE qt.company_id = q.company_id
                AND qphist.product_id = qp.product_id
                AND qphist.quote_product_id != qp.quote_product_id
                ORDER BY qphist.quote_id DESC
                LIMIT 0 , 1))
                , '')
                as view_history

              ,round(qp.cost_price * qp.qty,2)
              as total_cost_price


              ,qp.mark_up_type
              ,qp.mark_up as mark_up_amount

              ,round(selling_price,2)
              as selling_price_amount

              ,qp.discount_percentage as discount_percentage_amount

              ,round(
              (qp.selling_price  * qp.qty) - (qp.selling_price  * qp.qty)*(qp.discount_percentage/100)
              ,2)
              as total_selling_price

              ,qp.quote_product_id AS qo_po_id
              ,(SELECT SUM(round(qp.cost_price * qp.qty,2))
               FROM quote_product qp WHERE qp.quote_id = {$id})
               AS total_cost_price_sum

               ,(SELECT
              ($subSqlForPercentSum)
               )
               as discount_percentage_amount_sum

               ,(SELECT
              ($subSqlForMarkUpPercentSum)
               +
              ($subSqlForMarkUpValueSum)
               )
               as mark_up_amount_sum

              ,(SELECT SUM(round(
              (qp.selling_price * qp.qty)
              -(qp.selling_price  * qp.qty)*(qp.discount_percentage/100)
              ,2))

              FROM quote_product qp WHERE qp.quote_id = {$id}) as total_selling_price_sum

        FROM quote_product qp
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        LEFT JOIN product p ON (p.product_id = qp.product_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        ,(SELECT @row := 0) r
        WHERE qp.quote_id = {$id}
              {$whereSQL}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getTradingsgQuoteTradingsgProductLinkSQLOld12092013($id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $product_group_id   = $fn->getReqParam('product_group_id');

        $whereSQL = '';
        $discount = '';
        $discountSum ='';

        if ($product_group_id != "") {
            $whereSQL .= " AND pg.product_group_id = '{$product_group_id}'";
        }

        //BELOW SQL IS FOR GENERAL TRADING
        //TO CHECK IF THE SUM OF MARK UP TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForPercentSum = "
        SELECT SUM(round(((qp.cost_price * qp.mark_up)/100)* qp.qty,2)) as mark_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$id}
            AND (qp.mark_up_type = '%'
            || qp.mark_up_type = ''
            || qp.mark_up_type is null)
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['mark_sum'] > 0){
            $subSqlForPercentSum = "
            SELECT SUM(round(((qp.cost_price * qp.mark_up)/100)* qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$id}
                AND (qp.mark_up_type = '%'
                || qp.mark_up_type = ''
                || qp.mark_up_type is null)
            ";
        }
        else{
            $subSqlForPercentSum = 0;
        }


        //TO CHECK IF THE SUM OF MARK UP TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForValueSum ="
        SELECT SUM(round(qp.mark_up * qp.qty,2)) as mark_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$id}
            AND qp.mark_up_type = 'Value'
        ";
        $resultSubSql = $db->sql_query($subSqlForValueSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['mark_sum'] > 0){
            $subSqlForValueSum ="
            SELECT SUM(round(qp.mark_up * qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$id}
                AND qp.mark_up_type = 'Value'
            ";
        }
        else{
            $subSqlForValueSum = 0;
        }

        $SQL = "
        SELECT qp.quote_product_id
              ,p.item_code
              ,p.title AS product_title
              ,substr(pg.title,1,4) AS pg_title
              ,qp.client_id
              ,qp.cost_price
              ,p.unit
              ,qp.qty
              ,qp.remarks

              ,round(qp.cost_price * qp.qty,2)
              as total_cost_price
              ,qp.mark_up_type
              ,qp.mark_up as mark_up_amount

              ,round(selling_price,2)
              as selling_price_amount

              ,round(
              (qp.selling_price  * qp.qty) ,2)
              as total_selling_price

              ,(SELECT SUM(round(qp.cost_price * qp.qty,2))
               FROM quote_product qp WHERE qp.quote_id = {$id})
               AS total_cost_price_sum

               ,(SELECT
              ($subSqlForPercentSum)
               +
              ($subSqlForValueSum)
               )
               as mark_up_amount_sum

              ,(SELECT SUM(round(
              (qp.selling_price * qp.qty),2))

              FROM quote_product qp WHERE qp.quote_id = {$id}) as total_selling_price_sum
        FROM quote_product qp
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        LEFT JOIN product p ON (p.product_id = qp.product_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        ,(SELECT @row := 0) r
        WHERE qp.quote_id = {$id}
              {$whereSQL}
        ";

        return $SQL;
    }

    /**
     *
     */
    function getTradingsgQuoteTradingsgExpenseLinkSQL($id) {

        $SQL = "
        SELECT e.expense_id
              ,e.amount
              ,e.date
              ,e.description
        FROM expense e
        LEFT JOIN quote q ON (q.quote_id = e.quote_id)
        WHERE e.quote_id = {$id}
        ";
        return $SQL;
    }

    /**
     *
     */
    function getTradingsgQuoteTradingsgPurchaseOrderLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT po.purchase_order_id
              ,po.po_code
              ,com.company_name AS supplier_name
              ,(CONCAT_WS('', '<a href=\'index.php?module=tradingsg_quote&_spAction=printPurchaseOrder&id=', po.purchase_order_id, '\' target=\'_blank\'>PDF to Supplier</a>'))
              ,(CONCAT_WS('', '<a href=\'index.php?module=tradingsg_quote&_spAction=printPurchaseOrderWithPrice&id=', po.purchase_order_id, '\' target=\'_blank\'>PDF  with Price</a>'))

        FROM purchase_order po
        LEFT JOIN company com ON po.company_id_supplier = com.company_id
        WHERE po.quote_id = {$id}
        ORDER BY com.company_name
        ";

        return $SQL;
    }
    /**
     *
     */
    function getDuplicateQuote() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');

        $quote_id_source = $fn->getReqParam('quote_id');

        $rowQuoteSrc = $fn->getRecordRowByID('quote', 'quote_id', $quote_id_source);

        //create header
        $quote_code = 'Q' . $fn->getSequenceFromSettings('m.trading.quote.nextCode');
        $quote_date    = strtotime(date('Y-m-d'));
        $followup_date = strtotime('+7 days', $quote_date);

        $fa = array();
        $fa['quote_code']              = $this->getUpdateQuoteCode();
        $fa['quote_date']              = date('Y-m-d', $quote_date);
        $fa['follow_up_date']          = date('Y-m-d', $followup_date);
        $fa['status']                  = $rowQuoteSrc['status'];
        $fa['creation_date']           = date('Y-m-d');
        $fa['note']                    = $rowQuoteSrc['note'];
        $fa['title']                   = $rowQuoteSrc['title'];
        $fa['company_id']              = $rowQuoteSrc['company_id'];
        $fa['contact_id']              = $rowQuoteSrc['contact_id'];
        $fa['priority']                = $rowQuoteSrc['priority'];
        $fa['delivery_location']       = $rowQuoteSrc['delivery_location'];
        $fa['delivery_date']           = $rowQuoteSrc['delivery_date'];
        $fa['currency']                = $rowQuoteSrc['currency'];
        $fa['enquiry_id']              = $rowQuoteSrc['enquiry_id'];
        $fa['delivery_terms']          = $rowQuoteSrc['delivery_terms'];
        $fa['payment_terms']           = $rowQuoteSrc['payment_terms'];
        $fa['quote_type']              = $rowQuoteSrc['quote_type'];
        $fa['staff_id']                = $rowQuoteSrc['staff_id'];
        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'quote');

        if ($rowQuoteSrc['enquiry_id']) {
            $fa1 = array();
        }

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'quote');
        $db->sql_query($SQL);
        $quote_id = $db->sql_nextid();

        $topRm = $fn->getTopRoomName('quote');
        $url = "index.php?_topRm=order&module=tradingsg_quote" .
               "&_action=edit&record_id={$quote_id}";

        $arr = array('status' => 'success', 'returnUrl' => $url);
        return $cpUtil->getJsonFromArray($arr);

    }
    /**
     *
     */
     function getUpdateProductLineItems() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $product_id = $fn->getReqParam('product_id');
        $rec_id = $fn->getReqParam('rec_id');
        $id = $tv['srcRoomId'];
        $selling_price_per = '';
        $discountValue     = '';
        $marginValue     = '';
        $cost_price_discount =  '';
        $cost_price_margin = '';
        $mark_up_type     = '';

        $arr = array('price' => 0, 'margin' => 0, 'title' => '', 'sellingPrice' => 0);

        $SQL    = "
        SELECT p.price
              ,p.product_group_id
              ,p.item_code
              ,p.unit
              ,p.category_id
              ,pg.margin
              ,pg.title
              ,c.company_name
              ,c.company_id
        FROM product p
        LEFT JOIN product_group pg ON (pg.product_group_id = p.product_group_id)
        LEFT JOIN product_company pc ON (pc.product_id = p.product_id)
        LEFT JOIN company c ON (c.company_id = pc.company_id)
        WHERE p.product_id = '{$product_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $quoteProductRec = $fn->getRecordRowByID('quote_product', 'quote_product_id', $rec_id);
        $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quoteProductRec['quote_id']);
        $companyRec = $fn->getRecordRowByID('company', 'company_id', $quoteRec['company_id']);
        /*
        $quoteProductChk = $fn->getRecordByCondition('quote_product',
                                                     "product_id = {$product_id} AND
                                                     quote_id = {$quoteRec['quote_id']}");
         */

        //to validate if the product is already added
        $SQLCheck  = "
        SELECT product_id
        FROM quote_product
        WHERE product_id = {$product_id}
        AND quote_id = {$quoteRec['quote_id']}
        ";

        $resultCheck = $db->sql_query($SQLCheck);
        $numRows     = $db->sql_numrows($resultCheck);
        $arr['msg'] = '';

        if($numRows >= 1){
            $arr['msg'] = "Please note the product is already added";
            return $cpUtil->getJsonFromArray($arr);
            exit();
        }
        /*
        if(is_array($quoteProductChk)){
            $arr['msg'] = "Please note the product is already added";
            return $cpUtil->getJsonFromArray($arr);
            exit();
        }
        */

        //APPLIED FOR GENERAL TRADING -------------------------------------------------
        //for general trading we need to get discount from the company
        $discountMainRec = $fn->getRecordRowByID('discount', 'company_id', $quoteRec['company_id']);
        //TO CHECK IF MARGIN RECORD IS PRESENT IN DISCOUNT TABLE
        if($discountMainRec['company_id'] > 0){
            $discountValue = 0;
            //TO CHECK IF CATEGORY RECORD IS PRESENT IN DISCOUNT TABLE, IF YES FOLLOWING CODE WILL BE EXECUTED
            if($row['category_id'] != '' || $row['category_id']  != NULL || $row['category_id']  > 0){
                    $discountRecCat = $fn->getRecordByCondition('discount',
                                                     "product_group_id = {$row['product_group_id']} AND company_id = {$quoteRec['company_id']} AND category_id = {$row['category_id']}");

                //Discount %
                if ($discountRecCat['discount_percent'] > 0 || $discountRecCat['discount_percent'] != NULL){
                    $discountValue       = $discountRecCat['discount_percent'];
                    $cost_price_discount =  ($row['price'] *  $discountRecCat['discount_percent'])/100;
                }

                //Mark up % from Discount table
                if ($discountRecCat['margin'] > 0 || $discountRecCat['margin'] != NULL    || $discountRecCat['margin'] != ''){
                    $marginValue = $discountRecCat['margin'];
                    //$cost_price_margin =  ($row['price'] *  $discountRecCat['margin'])/100;
                    $cost_price_margin   =  $discountRecCat['margin'];
                    $arr['margin']       =  $discountRecCat['margin'];
                } else {
                    $marginValue = 0;
                }
            } else {
                 //IF NO CATEGORY RECORD IN DISCOUNT TABLE, FOLLOWING CODE WILL BE EXECUTED
                $discountRec = $fn->getRecordByCondition('discount',
                                                    "product_group_id = {$row['product_group_id']} AND company_id = {$quoteRec['company_id']} AND category_id IS NULL");
                if($discountRec['discount_percent'] > 0 || $discountRec['discount_percent'] != NULL){
                    $discountValue       = $discountRec['discount_percent'];
                    $cost_price_discount =  ($row['price'] *  $discountRec['discount_percent'])/100;
                }
                else{
                    $discountValue = 0;
                }
                //To Set Margin from Discount table
                if($discountRec['margin'] == NULL || $discountRec['margin'] == ''){
                    $arr['margin'] = 0;
                    $marginValue = 0;
                }
                else{
                    $marginValue = $discountRec['margin'];
                    //$cost_price_margin = ($row['price'] *  $discountRec['margin'])/100;
                    $cost_price_margin   =  $discountRec['margin'];
                    $arr['margin']       =  $discountRec['margin'];
                }
            }
        }
        else{
            //GET MARGIN VALUE FROM COMPANY TABLE
            if($companyRec['mark_up_percentage'] > 0){
                $marginValue = $companyRec['mark_up_percentage'];
                //$cost_price_margin =  ($row['price'] *  $companyRec['mark_up_percentage'])/100;
                $cost_price_margin =  $companyRec['mark_up_percentage'];
                $arr['margin'] =  $companyRec['mark_up_percentage'];
                $discountValue = 0;
                $cost_price_discount = 0;
            }
            else{
                $arr['margin'] = 0;
                $discountValue = 0;
                $cost_price_discount = 0;
            }
        }

        $selling_price = $cost_price_margin + $row['price'];
        $selling_price = number_format($selling_price,2);

        if($row['price'] == ''){
            $row['price'] = 0;
        }
        $SQLUpdate    = "
        UPDATE quote_product
        set cost_price = '{$row['price']}'
        ,product_id = '{$product_id}'
        ,discount_percentage = '{$discountValue}'
        ,mark_up = '{$marginValue}'
        ,selling_price = '{$selling_price}'
        ,client_id = '{$row['company_id']}'
        WHERE quote_product_id = {$rec_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        $SQLTotal = "
        SELECT SUM(round((qp.selling_price * qp.qty),2)) AS total_selling_price_sum
        FROM quote_product qp WHERE qp.quote_id = {$quoteRec['quote_id']}
        ";
        $resultTotal = $db->sql_query($SQLTotal);
        $rowTotal = $db->sql_fetchrow($resultTotal);
        $company_name = substr($row['company_name'],0,5);

        $viewHistory = '';

        if ($product_id != ''){
            $SQLQP ="
            SELECT qphist.cost_price
            FROM quote_product qphist
            LEFT JOIN quote qt ON ( qt.quote_id = qphist.quote_id )
            WHERE qt.company_id = {$row['company_id']}
            AND qphist.product_id = {$product_id}
            AND qphist.product_id > 0
            AND qphist.quote_product_id != {$rec_id}
            ORDER BY qphist.quote_id
            ";
            $resultQP = $db->sql_query($SQLQP);
            $numRowsQP  = $db->sql_numrows($resultQP);

            $SQLPP ="
            SELECT poprd.po_product_id
            FROM po_product poprd
            LEFT JOIN (company c) ON (poprd.supplier_id = c.company_id)
            WHERE poprd.product_id = {$product_id}
            ORDER BY c.company_name
            ";
            $resultPP = $db->sql_query($SQLPP);
            $numRowsPP  = $db->sql_numrows($resultPP);
            if ($numRowsQP > 0 || $numRowsPP > 0){
                $viewHistory = "
                <a href='index.php?_topRm=order&module=tradingsg_quote&_spAction=previousOrderForClient&showHTML=0&quote_product_id={$rec_id}'  class='productViewHistory'> View</a>
                ";
            }
        }

        $arr['price'] = $row['price'];
        $arr['title'] = $row['title'];
        $arr['sellingPrice'] = $selling_price;
        $arr['discount']   = $cost_price_discount .'('. $discountValue .'%)';
        $arr['itemCode'] =  $row['item_code'];
        $arr['unit']     =  $row['unit'];
        $arr['viewHistory'] = $viewHistory;
        $arr['total_selling_price_sum'] = $rowTotal['total_selling_price_sum'];
        $arr['clientId']  =  $company_name;
        $arr['mark_up_type']  =  '%';

        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getUpdateSellingLineItems() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $rec_id     = $fn->getReqParam('rec_id');
        $qty        = $fn->getReqParam('qty');
        $cost_price = $fn->getReqParam('costPrice');
        $mark_up    = $fn->getReqParam('mark_up');
        $markUpType = $fn->getReqParam('mark_up_type');

        $selling_price_per_discount = '';
        $discount_value =  '';
        $mark_up_value  =  0;
        $mark_up_for_one_qty  =  '';
        $discount_value_for_one_qty  = '';
        //$arr = array('sellingPrice' => 0, 'profit' => 0);
        //to update quantity in quote product
        if($qty > 0){
            $SQLUpdate    = "
            UPDATE quote_product
            set qty = {$qty}
            WHERE quote_product_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($cost_price > 0){
            $SQLUpdate    = "
            UPDATE quote_product
            set cost_price = {$cost_price}
            WHERE quote_product_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        if($mark_up == ''){
            $mark_up = 0;
        }

        $SQLUpdate    = "
        UPDATE quote_product
        set mark_up = {$mark_up}
        WHERE quote_product_id = {$rec_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        if($markUpType != ''){
            $SQLUpdate    = "
            UPDATE quote_product
            set mark_up_type = '{$markUpType}'
            WHERE quote_product_id = {$rec_id}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        $SQL    = "
        SELECT cost_price
              ,selling_price
              ,mark_up
              ,qty
              ,quote_id
              ,product_id
              ,discount_percentage
              ,mark_up_type
        FROM quote_product
        WHERE quote_product_id = {$rec_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        //to update quantity in po_product
        $expPoRec = array('condn' => " AND product_id = {$row['product_id']}");
        $poRec    = $fn->getRecordRowByID('po_product', 'quote_id', $row['quote_id'], $expPoRec);
        //to update records in purchase order
        if($poRec['purchase_order_id'] != ''){
            if($qty > 0){
                $SQLUpdate    = "
                UPDATE po_product
                set qty = {$qty}
                WHERE po_product_id = {$poRec['po_product_id']}
                ";
                $resultUpdate = $db->sql_query($SQLUpdate);
            }
            if($cost_price > 0){
                $SQLUpdate    = "
                UPDATE po_product
                set price = {$cost_price}
                WHERE po_product_id = {$poRec['po_product_id']}
                ";
                $resultUpdate = $db->sql_query($SQLUpdate);
            }
        }
        if($row['qty'] < 1){
            $qty = 1;
        } else {
            $qty = $row['qty'];
        }

        if($row['mark_up'] != 0 && $row['mark_up'] != ''){
            if($row['mark_up_type'] == '%' || $row['mark_up_type'] == ''){
                $mark_up_value        =  $qty  * $row['cost_price'] * ($row['mark_up']/100);
                $mark_up_for_one_qty  =  $row['cost_price'] * ($row['mark_up']/100);
            }
            else if($row['mark_up_type'] == 'Value'){
                $mark_up_value        =  $qty  * $row['mark_up'];
                $mark_up_for_one_qty  =  $row['mark_up'];
            }
        }

        if($row['discount_percentage'] != '' && $row['discount_percentage'] != 0){
            $discount_value  = $qty * $row['cost_price'] * ($row['discount_percentage']/100);
            $discount_value_for_one_qty  = $row['cost_price'] * ($row['discount_percentage']/100);
        }

        $selling_price = $mark_up_for_one_qty + $row['cost_price'] ;

        $SQLUpdate    = "
        UPDATE quote_product
        set selling_price = {$selling_price}
        WHERE quote_product_id = {$rec_id}
        ";
        //selling prioe need not be updated here.
        $resultUpdate = $db->sql_query($SQLUpdate);

        if($row['qty'] == 0){
            $qty = 1;
        } else {
            $qty = $row['qty'];
        }

        $totalSellingPrice  = round(($selling_price * $qty)-($selling_price * $qty)*($row['discount_percentage']/100),2);
        $totalCostPrice     = round($row['cost_price'] * $qty,2);

        //------------------ FOR GENERAL TRADING
        //TO CHECK IF THE SUM OF MARK UP TYPE(%) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForPercentSum = "
        SELECT SUM(round(((qp.cost_price * qp.mark_up)/100)* qp.qty,2)) as mark_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$row['quote_id']}
            AND (qp.mark_up_type = '%'
            || qp.mark_up_type = ''
            || qp.mark_up_type is null)
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['mark_sum'] > 0){
            $subSqlForPercentSum = "
            SELECT SUM(round(((qp.cost_price * qp.mark_up)/100)* qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$row['quote_id']}
                AND (qp.mark_up_type = '%'
                || qp.mark_up_type = ''
                || qp.mark_up_type is null)
            ";
        }
        else{
            $subSqlForPercentSum = 0;
        }


        //TO CHECK IF THE SUM OF MARK UP TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForValueSum ="
        SELECT SUM(round(qp.mark_up * qp.qty,2)) as mark_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$row['quote_id']}
            AND qp.mark_up_type = 'Value'
        ";
        $resultSubSql = $db->sql_query($subSqlForValueSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['mark_sum'] > 0){
            $subSqlForValueSum ="
            SELECT SUM(round(qp.mark_up * qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$row['quote_id']}
                AND qp.mark_up_type = 'Value'
            ";
        }
        else{
            $subSqlForValueSum = 0;
        }

        $SQL ="SELECT

              (SELECT SUM(round(qp.cost_price * qp.qty,2))
               FROM quote_product qp WHERE qp.quote_id = {$row['quote_id']}
               )
               AS total_cost_price_sum

              ,(SELECT SUM(round(((qp.selling_price * qp.discount_percentage)/100) * qp.qty,2))
              FROM quote_product qp WHERE qp.quote_id = {$row['quote_id']}
              )
              AS discount_percentage_amount_sum

              ,(SELECT
              ($subSqlForPercentSum)
               +
              ($subSqlForValueSum)
               )
               as mark_up_amount_sum

              ,(SELECT SUM(round((qp.selling_price * qp.qty) -(qp.selling_price  * qp.qty)*(qp.discount_percentage/100),2))
              FROM quote_product qp WHERE qp.quote_id = {$row['quote_id']}
              )
              AS total_selling_price_sum
        ";
        $resultUpdate = $db->sql_query($SQL);
        $row          = $db->sql_fetchrow($resultUpdate);

        $arr['total_cost_price_sum']           = $row['total_cost_price_sum'];
        $arr['discount_percentage_amount_sum'] = $row['discount_percentage_amount_sum'];
        $arr['mark_up_amount_sum']             = $row['mark_up_amount_sum'];
        $arr['total_selling_price_sum']        = $row['total_selling_price_sum'];

        $arr['mark_up_value']     = round($mark_up_value,2);
        //$arr['discount_value']    = round($discount_value,2);
        $arr['selling_price']     = $selling_price;
        $arr['totalCostPrice']    = $totalCostPrice;
        $arr['totalSellingPrice'] = $totalSellingPrice;

        return $cpUtil->getJsonFromArray($arr);
    }

    /**
     *
     */
    function getUpdateClientId() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rec_id    = $fn->getReqParam('rec_id');
        $client_id = $fn->getReqParam('client_id');

        $SQLClientUpdate    = "
        UPDATE quote_product
        set client_id = {$client_id}
        WHERE quote_product_id = {$rec_id}
        ";
        $resultClientUpdate = $db->sql_query($SQLClientUpdate);

        $SQL    = "
        SELECT quote_id
              ,product_id
        FROM quote_product
        WHERE quote_product_id = {$rec_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        //to update client_id in po_product
        $expPopRec = array('condn' => " AND product_id = {$row['product_id']}");
        $popRec    = $fn->getRecordRowByID('po_product', 'quote_id', $row['quote_id'], $expPopRec);
        //to check if the po product record exists or not
        if($popRec['purchase_order_id'] != ''){

            $expPoRec = array('condn' => " AND company_id_supplier = {$client_id}");
            $poRec    = $fn->getRecordRowByID('purchase_order', 'quote_id', $row['quote_id'], $expPoRec);
            //to check if the po record exists or not
            if($poRec['purchase_order_id'] != ''){
                $purchase_order_id = $poRec['purchase_order_id'];
            } else {
                $fa = array();
                $fa['quote_id']            = $row['quote_id'];
                $fa['company_id_supplier'] = $client_id;
                $fa['creation_date']       = date('Y-m-d');
                $fa['po_code']             = $this->getUpdatePOCode();

                $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'purchase_order');
                $resultInsert = $db->sql_query($SQLInsert);
                $purchase_order_id = $db->sql_nextid();
            }

            $SQLUpdate    = "
            UPDATE po_product
            set supplier_id = {$client_id}, purchase_order_id = {$purchase_order_id}
            WHERE po_product_id = {$popRec['po_product_id']}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

            //DELETE THE RECORDS FROM po WHICH DO NOT EXIST IN po_product

            $deleteSql = "
            DELETE FROM purchase_order
            WHERE purchase_order_id NOT IN
            (SELECT purchase_order_id FROM po_product WHERE quote_id = {$row['quote_id']})
            AND quote_id = {$row['quote_id']}
            ";
            $resultDelete = $db->sql_query($deleteSql);
    }

    /**
     *
     */
    function getUpdateProfit() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $profitDiscount = '';
        $rec_id = $fn->getReqParam('rec_id');

        $arr = array('profit' => 0);

        $SQL    = "
        SELECT cost_price
              ,mark_up
              ,qty
              ,quote_id
              ,product_id
              ,selling_price
              ,discount_percentage
        FROM quote_product
        WHERE quote_product_id = {$rec_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $profit = ($row['selling_price'] - $row['cost_price']) * $row['qty'];
        if($row['discount_percentage'] == 0){
            $profitDiscount =  $profit;
        } else {
            $profitDiscount =  ($profit *  $row['discount_percentage'])/100;
        }
        $profit = $profit -  $profitDiscount;

        $arr['profit'] = $profit;

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

        $quote_id  = $fn->getReqParam('id');
        //$this->getMatchSupplierRecords($quote_id);
        //print 'aaaaaa';
        $SQL = "
        SELECT qp.*
              ,p.title AS product_title
              ,p.unit
              ,q.quote_code
              ,q.quote_date
              ,c.company_name
        FROM quote_product qp
        LEFT JOIN product p ON (p.product_id = qp.product_id)
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        LEFT JOIN company c ON (c.company_id = qp.client_id)
        WHERE qp.quote_id = {$quote_id}
          AND qp.client_id != ''
          AND qp.product_id > 0
        GROUP BY c.company_name
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            //To check if the po is already created or not, if not create a purchase order
            $purchaseOrderRec = $fn->getRecordByCondition('purchase_order',
                                                      "company_id_supplier = '{$row['client_id']}' AND quote_id = {$quote_id}");

            if(is_array($purchaseOrderRec)){
                $purchase_order_id = $purchaseOrderRec['purchase_order_id'];
            } else {
                //Getting max code to create po
                $fa = array();
                $fa['quote_id'] = $quote_id;
                //$fa['status'] = 'new';
                $fa['company_id_supplier'] = $row['client_id'];
                $fa['creation_date'] = date('Y-m-d');
                $fa['po_code'] = $this->getUpdatePOCode();

                $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'purchase_order');
                $resultInsert = $db->sql_query($SQLInsert);
                $purchase_order_id = $db->sql_nextid();
            }

            //This sql is used to get the values from quote_product. Below code will create the record in
            //po and po_product history table .If the product record already exist it will not create.
            $SQLSelect = "
            SELECT qp.*
                  ,p.title AS product_title
                  ,p.unit
                  ,p.product_id
                  ,q.quote_code
                  ,q.quote_date
                  ,c.company_name
            FROM quote_product qp
            LEFT JOIN product p ON (p.product_id = qp.product_id)
            LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
            LEFT JOIN company c ON (c.company_id = qp.client_id)
            WHERE qp.quote_id = {$quote_id}
              AND qp.client_id = {$row['client_id']}
              AND qp.product_id > 0
              ORDER BY p.title
            ";
            $resultSelect = $db->sql_query($SQLSelect);

            while ($rowPP = $db->sql_fetchrow($resultSelect)) {
                $fa1 = array();
                $fa1['product_id'] = $rowPP['product_id'];
                $fa1['price']      = $rowPP['cost_price'];
                $fa1['qty']        = $rowPP['qty'];
                $fa1['quote_id']   = $quote_id;
                $fa1['supplier_id']= $row['client_id'];
                $fa1['creation_date'] = date('Y-m-d');
                $fa1['purchase_order_id'] = $purchase_order_id;
                //Checking if the product exists in po product
                /* OPTION 1 */
                $poProductRec = $fn->getRecordByCondition('po_product',
                                                          "product_id = '{$rowPP['product_id']}' AND supplier_id = {$row['client_id']} AND quote_id = {$quote_id}");
                if(is_array($poProductRec)){
                    $whereCondition = "WHERE po_product_id = {$poProductRec['po_product_id']}";
                    $sqlPoUpdate = $dbUtil->getUpdateSQLStringFromArray($fa1, "po_product", $whereCondition);
                    $resultPoUpdate      = $db->sql_query($sqlPoUpdate);
                } else {
                    $SQLPo = $dbUtil->getInsertSQLStringFromArray($fa1, 'po_product');
                    $resultPo = $db->sql_query($SQLPo);
                }
                $deleteSql1 = "
                DELETE FROM po_product
                WHERE quote_id = {$quote_id}
                     AND product_id = {$rowPP['product_id']}
                     AND supplier_id != {$row['client_id']}
                ";
                $resultDelete = $db->sql_query($deleteSql1);

                /* OPTION 2 DO NOT DELETE */
                //po and po_product history table .If the product record already exist it will not create.
                /*$SQLPOHist = "
                SELECT pohist.*
                FROM po_product pohist
                WHERE pohist.quote_id = {$quote_id}
                  AND pohist.product_id = {$rowPP['product_id']}
                ";
                $resultPOhist = $db->sql_query($SQLPOHist);
                $numRows = $db->sql_numrows($resultPOhist);

                if($numRows == 0){
                    $SQLPo = $dbUtil->getInsertSQLStringFromArray($fa1, 'po_product');
                    $resultPo = $db->sql_query($SQLPo);
                }

                while ($rowPOhist = $db->sql_fetchrow($resultPOhist)) {
                    if($rowPOhist['supplier_id'] == $row['client_id']){
                        $whereCondition = "WHERE po_product_id = {$rowPOhist['po_product_id']}";
                        $sqlPoUpdate = $dbUtil->getUpdateSQLStringFromArray($fa1, "po_product", $whereCondition);
                        $resultPoUpdate      = $db->sql_query($sqlPoUpdate);
                    //}
                    //else{
                        if($rowPOhist['product_id'] == 1203){
                            $deleteSql1 = "
                            DELETE FROM po_product
                            WHERE quote_id = {$quote_id}
                                 AND product_id = {$rowPP['product_id']}
                                 AND supplier_id != {$row['client_id']}
                            ";
                            $resultDelete = $db->sql_query($deleteSql1);
                        }
                    }
                }*/

            }
            //DELETE THE PRODUCT RECORDS FROM po_product WHICH DO NOT EXIST IN quote_product

            $deleteSql = "
            DELETE FROM po_product
            WHERE quote_id = {$quote_id} AND product_id NOT IN
            (SELECT product_id FROM quote_product WHERE quote_id = {$quote_id} AND product_id > 0)
            ";
            $resultDelete = $db->sql_query($deleteSql);

            //DELETE THE PRODUCT RECORDS FROM po_product WHICH DO NOT EXIST IN quote_product

            $deleteSql = "
            DELETE FROM purchase_order
            WHERE quote_id = {$quote_id} AND company_id_supplier NOT IN
            (SELECT client_id FROM quote_product WHERE quote_id = {$quote_id} AND product_id > 0)
            ";
            $resultDelete = $db->sql_query($deleteSql);
        }
    }

    /**
     *
     */
    function getMatchSupplierRecords($quote_id) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT qp.*
              ,p.title AS product_title
              ,p.unit
              ,q.quote_code
              ,q.quote_date
              ,c.company_name
        FROM quote_product qp
        LEFT JOIN product p ON (p.product_id = qp.product_id)
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        LEFT JOIN company c ON (c.company_id = qp.client_id)
        WHERE qp.quote_id = {$quote_id}
          AND qp.client_id != ''
          AND qp.product_id > 0
          ORDER BY quote_product_id
        ";
        $result = $db->sql_query($SQL);
        while ($row = $db->sql_fetchrow($result)) {
            $mark_up_for_one_qty  ='';
            $discount_value_for_one_qty  ='';

            $productHistRec = $fn->getRecordByCondition('product_company',
                                                      "product_id = '{$row['product_id']}'");
            $UpdateSQL    = "UPDATE quote_product SET client_id = {$productHistRec['company_id']}
            WHERE quote_product_id = {$row['quote_product_id']}";
            $resultUpdate = $db->sql_query($UpdateSQL);

            if($row['qty'] < 1){
                $qty = 1;
            } else {
                $qty = $row['qty'];
            }

            if($row['mark_up'] != 0 && $row['mark_up'] != ''){
                $mark_up_value        =  $qty  * $row['cost_price'] * ($row['mark_up']/100);
                $mark_up_for_one_qty  =  $row['cost_price'] * ($row['mark_up']/100);
            }

            if($row['discount_percentage'] != '' && $row['discount_percentage'] != 0){
                $discount_value  = $qty * $row['cost_price'] * ($row['discount_percentage']/100);
                $discount_value_for_one_qty  = $row['cost_price'] * ($row['discount_percentage']/100);
            }

            //we use the below condition to get the selling price for trading mass
            $selling_price = $mark_up_for_one_qty  + $row['cost_price'] + $discount_value_for_one_qty;

            $SQLUpdate    = "
            UPDATE quote_product
            set selling_price = {$selling_price}
            WHERE quote_product_id = {$rec_id}
            ";
            //selling prioe need not be updated here.
            $resultUpdate = $db->sql_query($SQLUpdate);
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
    function getRaiseInvoice() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');

        $quote_id  = $fn->getReqParam('id');
        $discountRecCat = '';

        $fa = array();
        $fa['quote_id']         = $quote_id;
        $fa['order_date']       = date('Y-m-d');
        $fa['creation_date']    = date('Y-m-d-H-i-s');
        $fa['order_status']     = 'Due';
        //$fa['delivery_to_text'] = 'THE MASTER & OWNER OF THE VESSEL';

        $quoteRow = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        $fa['company_id'] = $quoteRow['company_id'];

        $companyRow = $fn->getRecordRowByID('company', 'company_id', $quoteRow['company_id']);
        $fa['shipping_first_name']      = $companyRow['company_name'];
        $fa['shipping_address1']        = $companyRow['address_flat'];
        $fa['shipping_address2']        = $companyRow['address_street'];
        $fa['shipping_address_city']    = $companyRow['address_town'];
        $fa['shipping_address_state']   = $companyRow['address_state'];
        $fa['shipping_address_country'] = $companyRow['address_country'];

        $fa['cust_first_name']          = $companyRow['company_name'];
        $fa['cust_address1']            = $companyRow['billing_address_flat'];
        $fa['cust_address2']            = $companyRow['billing_address_street'];
        $fa['cust_address_city']        = $companyRow['billing_address_town'];
        $fa['cust_address_state']       = $companyRow['billing_address_state'];
        $fa['cust_address_country_code']= $companyRow['billing_address_country'];
        $fa['cust_phone']               = $companyRow['phone'];

        $orderRec = $fn->getRecordByCondition('order', "quote_id = '{$quote_id}'");

        //check if the order record already exist or not
        if(is_array($orderRec)){
            $whereCondition = "WHERE order_id = {$orderRec['order_id']}";
            $sqlUpdate = $dbUtil->getUpdateSQLStringFromArray($fa, "order", $whereCondition);
            $resultUpdate      = $db->sql_query($sqlUpdate);
            $order_id = $orderRec['order_id'];
        } else {
            $SQLInsert = $dbUtil->getInsertSQLStringFromArray($fa, 'order');
            $resultInsert = $db->sql_query($SQLInsert);
            $order_id = $db->sql_nextid();
        }

        $SQLSelect = "
        SELECT qp.*
              ,p.title AS product_title
              ,q.quote_date
        FROM quote_product qp
        LEFT JOIN product p ON (p.product_id = qp.product_id)
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        WHERE q.quote_id = {$quote_id}
        AND qp.product_id > 0
        ORDER BY qp.quote_product_id
        ";
        $resultSelect = $db->sql_query($SQLSelect);

        while ($row = $db->sql_fetchrow($resultSelect)) {
            $fa1 = array();
            $fa1['record_id']           = $row['product_id'];
            $fa1['unit_price']          = $row['selling_price'];
            $fa1['cost_price']          = $row['cost_price'];
            $fa1['qty']                 = $row['qty'];
            $fa1['order_id']            = $order_id;
            $fa1['supplier_id']         = $row['client_id'];
            $fa1['item_title']          = $row['product_title'];
            $fa1['discount_percentage'] = $row['discount_percentage'];
            $fa1['mark_up']             = $row['mark_up'];

            $orderItemRec = $fn->getRecordByCondition('order_item',
                                                      "record_id = '{$row['product_id']}' AND order_id = {$order_id}");

            if(is_array($orderItemRec)){
                $whereCondition = "WHERE order_item_id = {$orderItemRec['order_item_id']}";
                $sqlOiUpdate = $dbUtil->getUpdateSQLStringFromArray($fa1, "order_item", $whereCondition);
                $resultOiUpdate      = $db->sql_query($sqlOiUpdate);
            } else {
                $SQLOI = $dbUtil->getInsertSQLStringFromArray($fa1, 'order_item');
                $resultOI = $db->sql_query($SQLOI);
            }

            $productRow = $fn->getRecordRowByID('product', 'product_id', $row['product_id']);
            if($productRow['category_id']){
                $discountRecCat = $fn->getRecordByCondition('discount',
                                                         "product_group_id = {$productRow['product_group_id']} AND company_id = {$quoteRow['company_id']} AND category_id = {$productRow['category_id']}");
            }

            $discountRec = $fn->getRecordByCondition('discount',
                                                    "product_group_id = {$productRow['product_group_id']} AND company_id = {$quoteRow['company_id']} AND category_id IS NULL");

            $fa2 = array();
            if (is_array($discountRecCat)){
                $fa2['product_group_id']   = $discountRecCat['product_group_id'];
                $fa2['order_id']           = $order_id;
                $fa2['company_id']         = $discountRecCat['company_id'];
                $fa2['category_id']        = $discountRecCat['category_id'];
                $fa2['discount_percent']   = $discountRecCat['discount_percent'];
                $fa2['margin']             = $discountRecCat['margin'];
                $fa2['customer_type']      = $discountRecCat['customer_type'];

                $discountFinanceRec = $fn->getRecordByCondition('discount_finance',
                                                          "product_group_id = '{$productRow['product_group_id']}' AND order_id = {$order_id} AND category_id = {$productRow['category_id']}");

                if(is_array($discountFinanceRec)){
                } else {
                    $SQLDF = $dbUtil->getInsertSQLStringFromArray($fa2, 'discount_finance');
                    $resultDF = $db->sql_query($SQLDF);
                }
            } else {
                $fa2['product_group_id']   = $discountRec['product_group_id'];
                $fa2['order_id']           = $order_id;
                $fa2['company_id']         = $discountRec['company_id'];
                $fa2['discount_percent']   = $discountRec['discount_percent'];
                $fa2['margin']             = $discountRec['margin'];
                $fa2['customer_type']      = $discountRec['customer_type'];

                $discountFinanceRec = $fn->getRecordByCondition('discount_finance',
                                                          "product_group_id = '{$productRow['product_group_id']}' AND order_id = {$order_id} AND category_id IS NULL");

                if(is_array($discountFinanceRec)){
                } else {
                    $SQLDF = $dbUtil->getInsertSQLStringFromArray($fa2, 'discount_finance');
                    $resultDF = $db->sql_query($SQLDF);
                }
            }

        }
        //DELETE THE PRODUCT RECORDS FROM order_item WHICH DO NOT EXIST IN quote_product

        $deleteSql = "
        DELETE FROM order_item
        WHERE order_id = {$order_id} AND record_id NOT IN
        (SELECT product_id FROM quote_product
        WHERE quote_id = {$quote_id} AND product_id > 0)
        ";
        $resultDelete = $db->sql_query($deleteSql);
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
              ,CONCAT_WS(' **** ', p.title, p.price, p.unit, c.company_name,pg.title) AS label
        	  ,p.product_id AS id
        FROM product p
        LEFT JOIN product_company pc ON (pc.product_id = p.product_id)
        LEFT JOIN company c ON (c.company_id = pc.company_id)
        LEFT JOIN product_group pg ON (p.product_group_id = pg.product_group_id)
        WHERE p.title LIKE '%{$productTitle}%'
        AND p.published = 1
        ORDER BY pg.sort_order, p.title
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);
        $arr = json_encode($dataArray);
        return $arr;
    }

    /**
     *
     */
    function getProfitTypeValue() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $json  = array();
        $markUpTypeArr = $cpCfg['m.trading.quote.markUpTypeArr'];

        $count = 0;
        foreach ($markUpTypeArr as $row){
            $title = $markUpTypeArr[$count];
            $json[] = array("value" => $title, "caption" => $title);
            $count++;
        }

        return json_encode($json);
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

        if($cpCfg['m.tradingsg.quote.hasProductLinkForSplCase'] == 1){
            if ($product_id == ''){
                $json[] = array('value' => '', 'caption' => 'Please Select');
                return json_encode($json);
            }
        }

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
              ,substr(c.company_name,1,5) as company_name
        FROM product_company pc
        LEFT JOIN company c ON (c.company_id = pc.company_id)
        {$append}
        ORDER BY c.company_name
        ";
        return $sql;
    }

    /**
     *
     */
    function getUpdateMarkupByGroupFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getUpdateMarkupByGroupFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_group_id   = $fn->getPostParam('product_group_id');
        $category_id     = $fn->getPostParam('category_id');
        $profit_percent     = $fn->getPostParam('profit_percent');
        $quote_id           = $fn->getPostParam('quote_id');

        if ($category_id != ''){
            $SQL    = "
            SELECT qp.*
            FROM quote_product qp
            LEFT JOIN product p ON (p.product_id = qp.product_id)
            WHERE p.product_group_id = {$product_group_id}
              AND p.category_id = {$category_id}
              AND qp.quote_id = {$quote_id}
            ";
        }else if ($product_group_id != ''){
            $SQL    = "
            SELECT qp.*
            FROM quote_product qp
            LEFT JOIN product p ON (p.product_id = qp.product_id)
            WHERE p.product_group_id = {$product_group_id}
              AND qp.quote_id = {$quote_id}
            ";
        } else {
            $SQL    = "
            SELECT qp.*
            FROM quote_product qp
            LEFT JOIN product p ON (p.product_id = qp.product_id)
            WHERE qp.quote_id = {$quote_id}
            ";
        }
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $discount_price = 0;
            if($row['discount_percentage'] > 0){
                if($row['discount_type'] == '%'){
                    $discount_price  =  $row['cost_price'] * ($row['discount_percentage']/100);
                }
                else if($row['discount_type']  == 'Value'){
                    $discount_price  =  $row['discount_percentage'];
                }
            }
            $mark_up =  ($row['cost_price'] *  $profit_percent)/100;
            //inserted the below code for general trading
            $selling_price = $row['cost_price'] + $mark_up - $discount_price ;
            $sqlUpdate = "
            UPDATE quote_product SET selling_price = {$selling_price}
            WHERE quote_product_id = {$row['quote_product_id']}
            ";
            $resultUpdate = $db->sql_query($sqlUpdate);
        }

        if ($product_group_id != '' && $category_id == ''){
            $sqlUpdate = "
            UPDATE quote_product SET mark_up = '{$profit_percent}', mark_up_type = '%'
            WHERE product_id IN (select p.product_id from product p where p.product_group_id = {$product_group_id}) AND quote_id = {$quote_id}
            ";
        }
        else if ($category_id != '' && $product_group_id == ''){
            $sqlUpdate = "
            UPDATE quote_product SET mark_up = '{$profit_percent}', mark_up_type = '%'
            WHERE product_id IN (select p.product_id from product p where p.category_id = {$category_id}) AND quote_id = {$quote_id}
            ";
        }
        else if ($category_id != '' && $product_group_id != ''){
            $sqlUpdate = "
            UPDATE quote_product SET mark_up = '{$profit_percent}', mark_up_type = '%'
            WHERE product_id IN (select p.product_id from product p where p.product_group_id = {$product_group_id} AND p.category_id = {$category_id})
              AND quote_id = {$quote_id}
            ";
        }
        else {
            $sqlUpdate = "
            UPDATE quote_product SET mark_up = '{$profit_percent}', mark_up_type = '%'
            WHERE quote_id = {$quote_id} AND product_id > 0
            ";
        }

        $resultUpdate = $db->sql_query($sqlUpdate);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getUpdateDiscountByGroupFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getUpdateDiscountByGroupFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $product_group_id   = $fn->getPostParam('product_group_id');
        $category_id     = $fn->getPostParam('category_id');
        $discount_percentage= $fn->getPostParam('discount_percentage');
        $quote_id           = $fn->getPostParam('quote_id');

        if ($category_id != ''){
            $SQL    = "
            SELECT qp.*
            FROM quote_product qp
            LEFT JOIN product p ON (p.product_id = qp.product_id)
            WHERE p.product_group_id = {$product_group_id}
              AND p.category_id = {$category_id}
              AND qp.quote_id = {$quote_id}
            ";
        } else {
            $SQL    = "
            SELECT qp.*
            FROM quote_product qp
            LEFT JOIN product p ON (p.product_id = qp.product_id)
            WHERE p.product_group_id = {$product_group_id}
              AND qp.quote_id = {$quote_id}
            ";
        }
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $discount_price =  ($row['cost_price'] *  $discount_percentage)/100;
            $mark_up =  ($row['cost_price'] *  $row['mark_up'])/100;
            $selling_price = $row['cost_price'] + $mark_up + $discount_price;
            $sqlUpdate = "
            UPDATE quote_product SET selling_price = '{$selling_price}'
            WHERE quote_product_id = {$row['quote_product_id']}
            ";
            $resultUpdate = $db->sql_query($sqlUpdate);
        }

        if ($category_id == ''){
            $sqlUpdate = "
            UPDATE quote_product SET discount_percentage = '{$discount_percentage}'
            WHERE product_id IN (select p.product_id from product p where p.product_group_id = {$product_group_id})
              AND quote_id = {$quote_id}
            ";
        } else {
            $sqlUpdate = "
            UPDATE quote_product SET discount_percentage = '{$discount_percentage}'
            WHERE product_id IN (select p.product_id from product p where p.product_group_id = {$product_group_id} AND p.category_id = {$category_id})
              AND quote_id = {$quote_id}
            ";
        }
        $resultUpdate = $db->sql_query($sqlUpdate);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getUpdateDiscountFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getUpdateDiscountFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $discount_percentage= $fn->getPostParam('discount_percentage');
        $quote_id           = $fn->getPostParam('quote_id');

        $sqlUpdate = "
        UPDATE quote_product SET discount_percentage = '{$discount_percentage}', discount_type = '%'
        WHERE quote_id = {$quote_id}
        ";
        $resultUpdate = $db->sql_query($sqlUpdate);

        $SQL    = "
        SELECT cost_price
              ,selling_price
              ,qty
              ,quote_id
              ,quote_product_id
              ,product_id
              ,discount_percentage
              ,discount_type
              ,mark_up
              ,mark_up_type
        FROM quote_product
        WHERE quote_id = {$quote_id}
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $discount_value              = $row['discount_percentage'];
            $discount_value_for_one_qty  =  $row['cost_price'] * ($row['discount_percentage']/100);

            $mark_up_value_for_one_qty = 0;

            if($row['mark_up'] > 0){
                if($row['mark_up_type'] == '%'){
                    $mark_up_value              = $row['mark_up'];
                    $mark_up_value_for_one_qty  =  $row['cost_price'] * ($row['mark_up']/100);
                }
                else if($row['mark_up_type']  == 'Value'){
                    $mark_up_value              = $row['mark_up'];
                    $mark_up_value_for_one_qty  =  $row['mark_up'];
                }
            }

            $selling_price = $row['cost_price'] + $mark_up_value_for_one_qty - $discount_value_for_one_qty;

            $SQLUpdate    = "
            UPDATE quote_product
            set selling_price = {$selling_price}
            WHERE quote_product_id = {$row['quote_product_id']}
            ";
            $resultUpdate = $db->sql_query($SQLUpdate);
        }

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getUpdateMarkupByGroupFormValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        //$validate->validateData('product_group_id', 'Please select the product group');
        $validate->validateData('profit_percent', 'Please enter profit percent');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getUpdateDiscountByGroupFormValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('product_group_id', 'Please select the product group');
        $validate->validateData('discount_percentage', 'Please enter discount percent');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getUpdateDiscountFormValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('discount_percentage', 'Please enter discount percent');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     *
     */
    function getUpdateMarkupByCategoryFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        if (!$this->getUpdateMarkupByCategoryFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $category_id    = $fn->getPostParam('category_id');
        $profit_percent = $fn->getPostParam('profit_percent');
        $quote_id       = $fn->getPostParam('quote_id');

        $sqlUpdate = "
        UPDATE quote_product SET mark_up = '{$profit_percent}'
        WHERE product_id IN (select p.product_id from product p where p.category_id = {$category_id})
          AND quote_id = {$quote_id}
        ";
        $resultUpdate = $db->sql_query($sqlUpdate);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getUpdateMarkupByCategoryFormValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('category_id', 'Please select the category');
        $validate->validateData('profit_percent', 'Please enter profit percent');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getRaiseGeneralQuotation() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $quote_id = $fn->getReqParam('quote_id');

        $sql = "
        SELECT * FROM product
        WHERE general_quotation = 1
        ORDER BY product_group_id ASC
        ";
        $result = $db->sql_query($sql);

        while ($row = $db->sql_fetchrow($result)) {

            $rowClient = $fn->getRecordRowByID('product_company', 'product_id', $row['product_id']);
            $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
            $companyRec = $fn->getRecordRowByID('company', 'company_id', $quoteRec['company_id']);
            $discountRec = $fn->getRecordByCondition('discount',
                                                     "product_group_id = {$row['product_group_id']} AND company_id = {$quoteRec['company_id']}");
            $discountRecCat = $fn->getRecordByCondition('discount',
                                                     "product_group_id = {$row['product_group_id']} AND company_id = {$quoteRec['company_id']} AND category_id = {$row['category_id']}");
            if ($discountRecCat['discount_percent'] > 0){
                $discountValue       = $discountRecCat['discount_percent'];
            } else {
                if($discountRec['discount_percent'] > 0){
                    $discountValue       = $discountRec['discount_percent'];
                }
                else{
                    $discountValue = 0;
                }
            }

            if ($discountRecCat['margin'] > 0){
                $marginValue = $discountRecCat['margin'];
            } else {
                if($discountRec['margin'] == '' || $discountRec['margin'] == null || $row['category_id'] == 17){
                    $marginValue = 0;
                }
                else{
                    $marginValue = $discountRec['margin'];
                }
            }

            $fa = array();
            $fa['quote_id']     = $quote_id;
            $fa['product_id']   = $row['product_id'];
            $fa['cost_price']   = $row['price'];
            $fa['client_id']    = $rowClient['company_id'];
            $fa['mark_up']      = $marginValue;
            $fa['discount_percentage'] = $discountValue;
            $fa['qty']          = 1;
            //$fa['selling_price'] = 1;

            $sqlSerial = "
            SELECT max(serial_no) AS serial_no
            FROM quote_product
            WHERE quote_id = {$quote_id}
            ";
            $resultSerial = $db->sql_query($sqlSerial);
            $rowSerial = $db->sql_fetchrow($resultSerial);
            $numRows  = $db->sql_numrows($resultSerial);

            if ($numRows > 0){
                $serial_no = $rowSerial['serial_no'] + 1;
            } else {
                $serial_no = 1;
            }

            $fa['status']      = 0;
            $fa['serial_no']   = $serial_no;

            $quote_product_id = $fn->addRecord($fa, 'quote_product');
        }
    }

    /**
     *
     */
    function getDeleteProductsLinked() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $quote_id = $fn->getReqParam('quote_id');

        $sql = "
        DELETE FROM quote_product
        WHERE quote_id = {$quote_id}
        ";
        $result = $db->sql_query($sql);

        $sql = "
        DELETE FROM purchase_order
        WHERE quote_id = {$quote_id}
        ";
        $result = $db->sql_query($sql);

        $sql = "
        DELETE FROM po_product
        WHERE quote_id = {$quote_id}
        ";
        $result = $db->sql_query($sql);

        $orderRec = $fn->getRecordRowById('order', 'quote_id', $quote_id);

        $sql = "
        DELETE FROM `order_item`
        WHERE order_id = {$orderRec['order_id']}
        ";
        $result = $db->sql_query($sql);

        $sql = "
        DELETE FROM `order`
        WHERE quote_id = {$quote_id}
        ";
        $result = $db->sql_query($sql);

    }

    /**
     *
     */
    function getGenerateBulkProduct() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');

        $quote_id= $fn->getReqParam('id');

        $formAction = "index.php?module=tradingsg_quote&_spAction=generateProductFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='' method='post' action='{$formAction}'>
            <fieldset>
                <div class='floatbox'>
                    <div class='float_left'>
                     {$formObj->getTBRow('Number of Records', 'no_of_records')}
                    </div>
                </div>
                <input type='hidden' name='quote_id' value='{$quote_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getGenerateProductFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');

        if (!$this->getGenerateProductValidate()){
            return $validate->getErrorMessageXML();
        }

        $quote_id= $fn->getReqParam('quote_id');
        $no_of_records = $fn->getReqParam('no_of_records');

        for ($i = 1; $i <= $no_of_records ; $i++){
            $fa = array();
            $fa['qty']    = 0;
            $fa['quote_id'] = $quote_id;
            $id = $fn->addRecord($fa, 'quote_product');
        }

        return $validate->getSuccessMessageXML();

    }

    /**
     *
     */
    function getGenerateProductValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        $validate->validateData("no_of_records" , 'Please enter the No. of records');
        $no_of_records     = $fn->getPostParam('no_of_records', '', true);

        if ($no_of_records > 100) {
            $validate->errorArray['no_of_records']['name'] = "no_of_records";
            $validate->errorArray['no_of_records']['msg'] = 'Please enter the value below 100';
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getCheckboxForDeleteProductsLinked() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rec_id = $fn->getReqParam('rec_id');
        $checkedVal = $fn->getReqParam('checkedVal');

        if($checkedVal == 1){
            $_SESSION['selectedQuoteProductIds'][] = $rec_id;
        }
        else if($checkedVal == 0){
            $s = &$_SESSION['selectedQuoteProductIds'];
            $key = array_search($rec_id, $s);
            if($key != false){
                unset($s[$key]);
            }
        }
    }

    /**
     *
     */
    function getDeleteCheckedProductsLinked() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $quote_id = $fn->getReqParam('quote_id');

        $selectedQuoteProductIds = join(',', $_SESSION['selectedQuoteProductIds']);

        //DELETE THE PRODUCT RECORDS FROM quote_product

        $sqlDelete = "
        DELETE FROM quote_product
        WHERE quote_product_id IN ({$selectedQuoteProductIds})
          AND quote_id = {$quote_id}
        ";
        $result = $db->sql_query($sqlDelete);

        //DELETE THE PRODUCT RECORDS FROM po_product WHICH DO NOT EXIST IN quote_product

        $deleteSql = "
        DELETE FROM po_product
        WHERE quote_id = {$quote_id} AND product_id NOT IN
        (SELECT product_id FROM quote_product WHERE quote_id = {$quote_id} AND product_id > 0)
        ";
        $resultDelete = $db->sql_query($deleteSql);

        //DELETE THE PRODUCT RECORDS FROM purchase_order WHICH DO NOT EXIST IN quote_product

        $deleteSql = "
        DELETE FROM purchase_order
        WHERE quote_id = {$quote_id} AND company_id_supplier NOT IN
        (SELECT client_id FROM quote_product WHERE quote_id = {$quote_id} AND product_id > 0)
        ";
        $resultDelete = $db->sql_query($deleteSql);

        //DELETE THE PRODUCT RECORDS FROM order WHICH DO NOT EXIST IN quote_product

        $deleteSql = "
        DELETE FROM order
        WHERE quote_id = {$quote_id} AND company_id NOT IN
        (SELECT client_id FROM quote_product WHERE quote_id = {$quote_id} AND product_id > 0)
        ";
        $resultDelete = $db->sql_query($deleteSql);

        //DELETE THE PRODUCT RECORDS FROM order_item WHICH DO NOT EXIST IN quote_product
        $orderRec = $fn->getRecordByCondition('order', "quote_id = '{$row['quote_id']}'");

        $deleteSql = "
        DELETE FROM order_item
        WHERE order_id = {$orderRec['order_id']} AND product_id NOT IN
        (SELECT product_id FROM quote_product WHERE quote_id = {$quote_id} AND product_id > 0)
        ";
        $resultDelete = $db->sql_query($deleteSql);
    }

    /**
     *
     */
    function getUpdateGeneralQuotation() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $quote_id  = $fn->getReqParam('quote_id');

        $SQL    = "UPDATE quote SET quote_type = 'Requirement from Client' WHERE quote_id = {$quote_id}";
        $result = $db->sql_query($SQL);

        return $SQL;
    }

    /**
     *
     */
    function getPrintExportAsPdf() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html2pdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        //$pdf = new MYPDF();
        $pdf = new PDF_HTML();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',11);

        $quote_id = $fn->getReqParam('id');

        $subSqlForPercentSum = "
        SELECT SUM(round(((qp.cost_price * qp.discount_percentage )/100)* qp.qty,2)) as discount_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$quote_id}
            AND qp.discount_type = '%'
        ";
        $resultSubSql = $db->sql_query($subSqlForPercentSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['discount_sum'] > 0){
            $subSqlForPercentSum = "
            SELECT SUM(round(((qp.cost_price * qp.discount_percentage )/100)* qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$quote_id}
                AND qp.discount_type = '%'
            ";
        }
        else{
            $subSqlForPercentSum = 0;
        }


        //TO CHECK IF THE SUM OF DISCOUNT TYPE(VALUE) HAS VALUE OR NOT AND ASSIGN THE QUERY IF IT HAS VALUE ELSE ASSIGN ZERO
        $subSqlForValueSum ="
        SELECT SUM(round(qp.discount_percentage  * qp.qty,2)) as discount_sum
        FROM quote_product qp
        WHERE qp.quote_id = {$quote_id}
            AND qp.discount_type = 'Value'
        ";
        $resultSubSql = $db->sql_query($subSqlForValueSum);
        $rowSql       = $db->sql_fetchrow($resultSubSql);
        if($rowSql['discount_sum'] > 0){
            $subSqlForValueSum ="
            SELECT SUM(round(qp.discount_percentage  * qp.qty,2))
            FROM quote_product qp
            WHERE qp.quote_id = {$quote_id}
                AND qp.discount_type = 'Value'
            ";
        }
        else{
            $subSqlForValueSum = 0;
        }

        $SQL = "
        SELECT qp.*
              ,p.title AS product_title
              ,p.description_short
              ,p.unit
              ,p.item_code
              ,p.part_number
              ,q.quote_code
              ,q.payment_terms
              ,q.delivery_terms
              ,q.note
              ,q.currency
              ,q.quote_date
              ,c.company_name
              ,c.address_flat
              ,c.address_street
              ,c.address_town
              ,c.address_state
              , (SELECT gc.name FROM geo_country gc
                 WHERE gc.country_code = c.address_country)
                AS address_country
              ,c.billing_address_flat
              ,c.billing_address_street
              ,c.billing_address_town
              ,c.billing_address_state
              , (SELECT gc.name FROM geo_country gc
                 WHERE gc.country_code = c.billing_address_country)
                AS billing_address_country
              ,c.customer_type
              ,qp.selling_price
              ,qp.cost_price
              ,(SELECT
              ($subSqlForPercentSum)
               +
              ($subSqlForValueSum)) as discount_percentage_amount_sum
              ,(SELECT SUM(qph.qty * qph.cost_price) FROM  quote_product qph
               WHERE qph.quote_id = qp.quote_id) AS sub_total
              ,(SELECT SUM(qph.qty * qph.selling_price) FROM  quote_product qph
               WHERE qph.quote_id = qp.quote_id) AS total
        FROM quote_product qp
        LEFT JOIN product p ON (p.product_id = qp.product_id)
        LEFT JOIN quote q ON (q.quote_id = qp.quote_id)
        LEFT JOIN company c ON (c.company_id = q.company_id)
        WHERE q.quote_id = {$quote_id}
        ";
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);

        $today = date("Y-m-d");

		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
			$pdf->Output();
			return;
		}

        $count = 0;
        $total = 0;
        $discount_price = 0;
        $rows = "";
        $lineItemNumber = 1;  // To increment the line item in receipt
		$totaltsp = '';
		$selling_price = '';

        //============================================================================= //
        $pdf->SetFont('Arial','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                $pdf->Image('images/logo-print.gif',10,5,45);
                $pdf->SetXY(10,10);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, 'Authorized Distributor of:');
                $pdf->SetXY(10,25);
                $pdf->Image('images/parker.jpg',10,28, 25);
                //$pdf->Image('images/gse.png',42,25, 25);

                $pdf->SetXY(130,0);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.companyName']);
                $pdf->Ln(5);
                $pdf->SetXY(130,5);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf1']);
                $pdf->Ln(5);
                $pdf->SetXY(130,10);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf2']);
                $pdf->Ln(5);
                $pdf->SetXY(130,15);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf3']);
                $pdf->Ln(5);
                $pdf->SetXY(130, 20);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf4']);
                $pdf->Ln(5);
                $pdf->SetXY(130,25);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf7']);
                $pdf->Ln(5);
                $pdf->SetXY(130,30);
                $pdf->Cell(50, 20, $cpCfg['cp.addressPdf6']);
                $pdf->Ln(5);
                $pdf->SetXY(130,35);
                $pdf->Cell(50, 20, $cpCfg['printEmailAddress']);

                /* Header */
                $pdf->SetFont('Courier','BU',11);
                $pdf->SetXY(90, 35);
                $pdf->Cell(21, 20, "QUOTATION", 0, 0, 'C');
                $pdf->Ln(20);

			    $quoteCode = $row['quote_code'];
				$formatedQC = explode("-", $quoteCode);

				$billingAddressFlat = '';
				$billingAddressStreet = '';
				$billingAddressTown = '';
				$billingAddressState = '';
				$billingAddressCountry = '';

				if ($row['billing_address_flat'] != ''
				 || $row['billing_address_street'] != ''
				 || $row['billing_address_town'] != ''
				 || $row['billing_address_state'] != ''
				 || $row['billing_address_country'] != '')
			    {
					$billingAddressFlat     = $row['billing_address_flat'];
					$billingAddressStreet   = $row['billing_address_street'];
					$billingAddressTown     = $row['billing_address_town'];
					$billingAddressState    = $row['billing_address_state'];
					$billingAddressCountry  = $row['billing_address_country'];
			    } else {
					$billingAddressFlat     = $row['address_flat'];
					$billingAddressStreet   = $row['address_street'];
					$billingAddressTown     = $row['address_town'];
					$billingAddressState    = $row['address_state'];
					$billingAddressCountry  = $row['address_country'];
				}

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(95,8,"TO",1,0, 'L', 1);
                $pdf->Cell(95,8,"",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(95, 8, $row['company_name'], 'LR', 0, 'L', 1);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(95, 8, "QUOTE CODE : {$quoteCode}", 'LR', 0, 'L', 1);
                $pdf->Ln();
	            $pdf->Cell(95, 5, $billingAddressFlat, 'LR', 0, 'L', 1);
	            $pdf->Cell(95, 5, "DATE : {$fn->getCPDate($row['quote_date'], 'd-m-Y')}", 'LR', 0, 'L', 1);
                $pdf->Ln();
	            $pdf->Cell(95, 5, $billingAddressStreet, 'LR', 0, 'L', 1);
	            $pdf->Cell(95, 5, '', 'LR', 0, 'L', 1);
                $pdf->Ln();
	            $pdf->Cell(95, 5, $billingAddressTown, 'LR', 0, 'L', 1);
	            $pdf->Cell(95, 5, '', 'LR', 0, 'L', 1);
                $pdf->Ln();
	            $pdf->Cell(95, 5, $billingAddressCountry . ' - ' . $billingAddressState, 'BLR', 0, 'L', 1);
	            $pdf->Cell(95, 5, '', 'BLR', 0, 'L', 1);
                $pdf->Ln();
                //$pdf->MultiCell(190,5,$row['company_name'] ."\n". $row['address_flat'] ."\n". $row['address_street'] ."\n". $row['address_town'] ."\n". $row['address_country']  ." - ". $row['address_state'],1,'L');
                $pdf->Ln(10);

                /* List of order items header */
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(254,203,156);
                $pdf->Cell(10,8,"S.NO",1,0, 'C', 1);
                $pdf->Cell(72,8,"NAME OF THE ITEM",1,0, 'C', 1);
                $pdf->Cell(35,8,"PART No",1,0, 'C', 1);
                $pdf->Cell(10,8,"QTY",1,0, 'C', 1);
                $pdf->Cell(10,8,"UOM",1,0, 'C', 1);
                $pdf->Cell(22,8,"SP",1,0, 'C', 1);
                $pdf->Cell(31,8,"TOTAL(" . $row['currency'] . ")",1,0, 'C', 1);
                $pdf->Ln();
            }

            //===================================MAIN TABLE============================= //

            if($row['mark_up_type'] == '%'){
                $selling_price = $row['cost_price'] + ($row['cost_price'] * ($row['mark_up']/100));
            }
            else if($row['mark_up_type'] == 'Value'){
                $selling_price = $row['cost_price']  + $row['mark_up'];
            } else {
				$selling_price = $row['cost_price'];
			}

			$tsp = $row['qty'] * $selling_price;
			$tsp = number_format($tsp,2);
			$titledescrip = $row['product_title'] . ' ' . $row['description_short'];

            $pdf->SetFont('Courier','B',11);
            $pdf->SetFillColor(255,255,255);
            $pdf->Cell(10, 8, $lineItemNumber, 1, 0, 'L', 1);
            //$pdf->Cell(145, 8, substr($row['product_title'], 0, 61), 1, 0, 'L', 1);
//            $pdf->drawTextBox($titledescrip, 72, 55, 'L', 'T', 1);
            $pdf->Cell(72, 8, $row['product_title'], 1, 0, 'L', 1);
            //$pdf->drawTextBox($row['product_title'] .' : ' .  $row['description_short'], 75, 5, 'L', 'T', 1);
            $pdf->Cell(35, 8, $row['part_number'], 1, 0, 'R', 1);
            $pdf->Cell(10, 8, $row['qty'], 1, 0, 'R', 1);
            $pdf->Cell(10, 8, $row['unit'], 1, 0, 'R', 1);
            $pdf->Cell(22, 8, $selling_price, 1, 0, 'R', 1);
            $pdf->Cell(31, 8, $tsp, 1, 0, 'R', 1);
            $pdf->Ln();

            $count++;
            $lineItemNumber++;

			$total = $row['total'];
            $discount = $row['discount_percentage_amount_sum'];
            $notes = $row['note'];
            $terms = $row['payment_terms'];
            $delivery_terms = $row['delivery_terms'];
			$sub_total = $total + $discount;
        }

			$totaldiscount = $sub_total - $discount;
			$discountPercent = $discount * 100 / $sub_total;
            $totaldiscount = number_format($totaldiscount,2);
            $sub_total = number_format($sub_total,2);
            $discount = number_format($discount,2);
            $discountPercent = number_format($discountPercent,2);


			if ($discount <= 0){
	            $pdf->SetFont('Courier','B',11);
	            $pdf->Cell(159,8,"TOTAL",1,0, 'R', 1);
	            $pdf->SetFont('Courier','B',11);
	            $pdf->Cell(31,8,$totaldiscount,1,0, 'R', 1);
				$pdf->Ln(20);
			} else {
	            $pdf->SetFont('Courier','B',11);
	            $pdf->Cell(159,8,"SUB-TOTAL",1,0, 'R', 1);
	            $pdf->SetFont('Courier','B',11);
	            $pdf->Cell(31,8,$sub_total,1,0, 'R', 1);
				$pdf->Ln();
	            $pdf->SetFont('Courier','B',11);
	            $pdf->Cell(159,8,"LESS : DISCOUNT (" . $discountPercent . "%)",1,0, 'R', 1);
	            $pdf->SetFont('Courier','B',11);
	            $pdf->Cell(31,8,$discount,1,0, 'R', 1);
				$pdf->Ln();
	            $pdf->SetFont('Courier','B',11);
	            $pdf->Cell(159,8,"TOTAL",1,0, 'R', 1);
	            $pdf->SetFont('Courier','B',11);
	            $pdf->Cell(31,8,$totaldiscount,1,0, 'R', 1);
				$pdf->Ln(20);
			}

            $pdf->SetFont('Courier','B',11);
            $pdf->SetFillColor(254,203,156);
            $pdf->Cell(190,8,"Payment Terms", 1,0, 'L', 1);
            $pdf->Ln(10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetFont('Courier','B',11);
            //$pdf->Cell(190, 8, $terms, 1, 0, 'L', 1);
            $pdf->drawTextBox($terms, 180, 55, 'L', 'T', 0);
            $pdf->Ln(10);
            $pdf->SetFillColor(254,203,156);
            $pdf->Cell(190,8,"Delivery Terms",1,0, 'L', 1);
            $pdf->Ln(10);
            $pdf->drawTextBox($delivery_terms, 180, 55, 'L', 'T', 0);
            $pdf->Ln(10);

            $pdf->SetFont('Courier','B',11);
            $pdf->SetFillColor(254,203,156);
            $pdf->Cell(190, 8, 'NOTE: ', 1, 0, 'L', 1);
            $pdf->Ln(10);

            $pdf->SetFont('Courier','B',11);
            //$pdf->Cell(900, 8, $notes);
            $pdf->drawTextBox($notes, 180, 55, 'L', 'T', 0);
            $pdf->Ln(5);
	        /* Creation of media record of the invoice */
	        $file_name = 'Refund_REF_' . date('Y-m-d') .'.pdf';
	        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

	        $outputFileName = $outputPath . '/' . $file_name;
	        //$pdf->Output($outputFileName , "F");
			$pdf->Output();

    }

    /**
     *
     */
    function getAddNoteFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $purchase_order_id  = $fn->getPostParam('purchase_order_id');
        $notes         = $fn->getPostParam('notes');

        if (!$this->getAddNoteFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();
        $fa['notes']     = $notes;

        $SQLUpdate    = "
        UPDATE purchase_order
        set notes = '{$notes}'
        WHERE purchase_order_id = {$purchase_order_id}
        ";
        $resultUpdate = $db->sql_query($SQLUpdate);

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddNoteFormValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getAddProduct() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $quote_id= $fn->getReqParam('quote_id');

        $fa = array();
        $fa['qty'] = 0;
        $fa['quote_id'] = $quote_id;
        $id = $fn->addRecord($fa, 'quote_product');
    }
}
