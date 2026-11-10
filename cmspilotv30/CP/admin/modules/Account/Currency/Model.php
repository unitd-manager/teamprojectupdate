<?
class CP_Admin_Modules_Account_Currency_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL = "
        SELECT c.*
        FROM currency c
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "c.currency_id  = '{$tv['record_id']}'";

        } else {

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.title LIKE '%{$tv['keyword']}%'
                    OR c.code LIKE '%{$tv['keyword']}%'
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
        $validate->validateData('title', 'Please enter the currency name');

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
        $validate->validateData('title', 'Please enter the currency name');

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
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'stock');
        $fa = $fn->addToFieldsArray($fa, 'stock_rate');
        $fa = $fn->addToFieldsArray($fa, 'main_currency');

        return $fa;
    }

    /**
     *
     */
    function getCurrencySQL() {
    	$SQL = "
    	SELECT currency_id
              ,CONCAT_WS(' - ', code, title) AS title
    	FROM currency
    	ORDER BY code
    	";
        return $SQL;
    }

    /**
     *
     */
    function getIdByCurrencyCode($code) {
        $fn = Zend_Registry::get('fn');

    	$SQL = "
    	SELECT currency_id
    	FROM currency
        WHERE code = '{$code}'
    	";
        $row = $fn->getRecordBySQL($SQL, MYSQL_ASSOC);

        return $row['currency_id'];
    }

    /**
     *
     * @param string $action (values buy / sell)
     * @param integer $journal_id
     */
    function updateAvgBuyRate($action, $journal_id) {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $rowJ = $fn->getRecordRowByID('journal', 'journal_id', $journal_id);

        $journal_master_id = $rowJ['journal_master_id'];
        $currency_id = $rowJ['currency_id'];
        $acc_head_id = $rowJ['acc_head_id'];
        $rowJM = $fn->getRecordRowByID('journal_master', 'journal_master_id', $journal_master_id);
        $rowCurr = $fn->getRecordRowByID('currency', 'currency_id', $currency_id);
        //$rowAH = $fn->getRecordRowByID('acc_head', 'acc_head_id', $acc_head_id);

        $avg_buy_rate = 0;
        $stock = 0;
        if ($action == 'buy') {
            //update stock in currency
            $stock = $rowCurr['stock'] + $rowJ['debit_base'];
            $avg_buy_rate = ( ($rowCurr['stock'] * $rowCurr['avg_buy_rate']) +
                              ($rowJ['debit_base'] * $rowJ['exch_rate_to_base']) ) /
                              ($rowCurr['stock'] + $rowJ['debit_base']);

            $fa = array();
            $fa['stock']        = $stock;
            $fa['avg_buy_rate'] = $avg_buy_rate;
            $whereCondition = "WHERE currency_id = {$currency_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'currency', $whereCondition);
            $db->sql_query($SQL);

            //update stock in journal
            $fa = array();
            $fa['avg_buy_rate'] = $avg_buy_rate;
            $whereCondition = "WHERE journal_id = {$journal_id}";
            $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'journal', $whereCondition);
            $db->sql_query($SQL);

        } else if ($action == 'sell') {
            $stock = $rowCurr['stock'] - $rowJ['credit_base'];
            $margin = ($rowJ['exch_rate_to_base'] - $rowCurr['avg_buy_rate']) * $rowJ['credit_base'];

            $avg_buy_rate = $rowCurr['avg_buy_rate'];
            // if ($stock <= 0) {
            //     $avg_buy_rate = 0;
            //     $margin = ($rowJ['exch_rate_to_base'] - $rowCurr['avg_buy_rate']) * $stock;
            // }

            //update stock in currency
            $fa = array();
            $fa['stock']        = $stock;
            $fa['avg_buy_rate'] = $avg_buy_rate;
            $whereCondition = "WHERE currency_id = {$currency_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'currency', $whereCondition);
            $db->sql_query($SQL);

            //update margin in journal
            $fa = array();
            $fa['margin'] = $margin;
            $whereCondition = "WHERE journal_id = {$journal_id}";
            $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'journal', $whereCondition);
            $db->sql_query($SQL);
        }

    }

    /**
     *
     * @param string $action (values buy / sell)
     * @param integer $journal_id
     */
    function updateAvgStockRate($action, $journal_id) {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');

        $rowJ = $fn->getRecordRowByID('journal', 'journal_id', $journal_id);

        $journal_master_id = $rowJ['journal_master_id'];
        $currency_id = $rowJ['currency_id'];
        $acc_head_id = $rowJ['acc_head_id'];
        $rowJM = $fn->getRecordRowByID('journal_master', 'journal_master_id', $journal_master_id);
        $rowCurr = $fn->getRecordRowByID('currency', 'currency_id', $currency_id);
        //$rowAH = $fn->getRecordRowByID('acc_head', 'acc_head_id', $acc_head_id);

        $avg_stock_rate = 0;
        $stock = 0;
        if ($action == 'buy') {
            //update stock in currency
            $stock = $rowCurr['stock'] + $rowJ['debit_base'];
            $avg_stock_rate = ( ($rowCurr['stock'] * $rowCurr['avg_stock_rate']) +
                                ($rowJ['debit_base'] * $rowJ['exch_rate_to_base']) ) /
                                ($rowCurr['stock'] + $rowJ['debit_base']);

            $fa = array();
            $fa['stock']          = $stock;
            $fa['avg_stock_rate'] = $avg_stock_rate;
            $whereCondition = "WHERE currency_id = {$currency_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'currency', $whereCondition);
            $db->sql_query($SQL);

            //update stock in journal
            $fa = array();
            $fa['avg_stock_rate'] = $avg_stock_rate;
            $whereCondition = "WHERE journal_id = {$journal_id}";
            $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'journal', $whereCondition);
            $db->sql_query($SQL);

        } else if ($action == 'sell') {
            $stock = $rowCurr['stock'] - $rowJ['credit_base'];
            $avg_stock_rate = ( ($rowCurr['stock'] * $rowCurr['avg_stock_rate']) -
                                ($rowJ['credit_base'] * $rowJ['exch_rate_to_base']) ) /
                                ($rowCurr['stock'] - $rowJ['credit_base']);

            // if ($stock <= 0) {
            //     $avg_stock_rate = 0;
            //     $margin = ($rowJ['exch_rate_to_base'] - $avg_stock_rate) * $stock;
            // }

            //update stock in currency
            $fa = array();
            $fa['stock']          = $stock;
            $fa['avg_stock_rate'] = $avg_stock_rate;
            $whereCondition = "WHERE currency_id = {$currency_id}";
            $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'currency', $whereCondition);
            $db->sql_query($SQL);

            // //update margin in journal
            // $fa = array();
            // $fa['margin'] = $margin;
            // $whereCondition = "WHERE journal_id = {$journal_id}";
            // $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, 'journal', $whereCondition);
            // $db->sql_query($SQL);
        }

    }
}
