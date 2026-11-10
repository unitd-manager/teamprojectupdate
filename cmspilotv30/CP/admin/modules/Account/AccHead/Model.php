<?
class CP_Admin_Modules_Account_AccHead_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT ah.*
        	  ,ah.opening_balance_credit - ah.opening_balance_debit AS opening_balance
        	  ,ac.title AS category_title
              ,CONCAT_WS(' - ', c.code, c.title) AS currency_title
        	  ,cs.title AS counter_title
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
              ,comp.company_name
        FROM acc_head ah
        LEFT JOIN acc_category ac ON ah.acc_category_id = ac.acc_category_id
        LEFT JOIN currency c ON ah.currency_id = c.currency_id
        LEFT JOIN counter_setup cs ON cs.counter_setup_id = ah.counter_setup_id
        LEFT JOIN (contact cont) ON (ah.contact_id = cont.contact_id)
        LEFT JOIN (company comp) ON (ah.company_id = comp.company_id)
        ";

        return $SQL;
    }

    function getAccountHeadsAsArray() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $term = $fn->getReqParam('term');
        $acc_company_id = $fn->getSessionParam('acc_company_id');
        $rowAccComp = $fn->getRecordRowByID('acc_company', 'acc_company_id', $acc_company_id);

        $termArr = explode(' ',$term);
        $where = '';
        if (count($termArr) == 1) {
            $where = "ah.title LIKE '%{$term}%'";
        } else {
            $termArr = $cpUtil->getArrayPermutations($termArr);
            $arr2 = array();
            foreach ($termArr as $arr) {
                $arr2[] = "ah.title LIKE '" . join("% %", $arr) . "";
            }
            $where = join("%' OR \n", $arr2) . "%'";
        }

        $SQL = "
        SELECT DISTINCT
               ah.acc_head_id AS id
        	  ,ah.title AS value
        	  ,ah.title AS label
        	  ,ah.currency_id
        	  ,c.code AS currency
        	  ,TRIM(
        	      TRAILING '.' FROM (TRIM(TRAILING '0' FROM cc.exch_rate_sell))
        	   ) AS exch_rate
        FROM acc_head ah
        JOIN currency c ON ah.currency_id = c.currency_id
        JOIN currency_convert cc
		  ON (cc.from_currency_id = c.currency_id AND
		      cc.to_currency_id = {$rowAccComp['base_currency_id']})
        WHERE {$where}
        ";
        $result = $db->sql_query($SQL);

        $dataArray = $dbUtil->getResultsetAsArray($result);

        return $dataArray;
    }

    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $acc_category_id  = $fn->getReqParam('acc_category_id');
        $currency_id  = $fn->getReqParam('currency_id');

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "ah.acc_head_id  = '{$tv['record_id']}'";

        } else {
            if ($acc_category_id != '') {
                $searchVar->sqlSearchVar[] = "ah.acc_category_id = {$acc_category_id}";
            }

            if ($currency_id != '') {
                $searchVar->sqlSearchVar[] = "ah.currency_id = {$currency_id}";
            }

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       ah.title LIKE '%{$tv['keyword']}%'
                    OR ah.opening_balance_credit LIKE '%{$tv['keyword']}%'
                    OR ah.opening_balance_debit LIKE '%{$tv['keyword']}%'
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
        $validate->validateData('title', 'Please enter the title');
        $validate->validateData('acc_category_id', 'Please choose category');

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
        $fa['code'] = $this->getAutoCodeByAccCatId($fa['acc_category_id']);
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getAutoCodeByAccCatId($acc_category_id){
        $fn  = Zend_Registry::get('fn');
        $rec = $fn->getRecordRowByID('acc_category', 'acc_category_id', $acc_category_id);
        $code = $rec['code'];
        $count = $fn->getRecordCount('acc_head', "acc_category_id = {$acc_category_id}") + 1;
        $count = ($count < 10) ? "0{$count}" : $count;

        $hyphen = strpos($code, '-') === false ? '-' : '';
        $code = $rec['code'] . $hyphen . $count;
        return $code;
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter the title');

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

        $opBalVal = $fn->getPostParam('opening_balance');
        $opBalValBase = $fn->getPostParam('opening_balance_base');
        $opBalType = $fn->getPostParam('op_balance_type');

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'acc_category_id');

        if ($opBalType == 'Credit') {
            $fa['opening_balance_credit'] = $opBalVal;
            $fa['opening_balance_debit'] = 0;
            $fa['opening_balance_credit_base'] = $opBalValBase;
            $fa['opening_balance_debit_base'] = 0;
        } else if ($opBalType == 'Debit') {
            $fa['opening_balance_debit'] = $opBalVal;
            $fa['opening_balance_credit'] = 0;
            $fa['opening_balance_debit_base'] = $opBalValBase;
            $fa['opening_balance_credit_base'] = 0;
        }

        $fa = $fn->addToFieldsArray($fa, 'currency_id');
        $fa = $fn->addToFieldsArray($fa, 'company_id');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'counter_setup_id');

        return $fa;
    }

    /**
     *
     * returns the acc head info array with debit sum, credit sum, debit base sum, credit base sum,
     * opening balance, ledger balance and available balance
     */
    function getAccHeadRow($acc_head_id = '', $entry_date_from = '', $entry_date_to = '') {
        $fn = Zend_Registry::get('fn');

        if ($acc_head_id == ''){
            $acc_head_id = $fn->getReqParam('acc_head_id');
            $entry_date_from = $fn->getReqParam('entry_date_from');
            $entry_date_to = $fn->getReqParam('entry_date_to');
        }

        $broughtForwardSQL = '';

        $whereCond = '';
        $whereCondBF = '';
        $whereCondAvailBal = "
        AND jm.ledger_authorized = 1
        AND (j.pending = 0 OR j.pending IS NULL)
        ";

        if ($entry_date_from != '' && $entry_date_to != '') {
            $whereCond = "
                jm.entry_date >= '{$entry_date_from}'
            AND jm.entry_date <= '{$entry_date_to}'
            ";
            $whereCondBF = "
                jm.entry_date < '{$entry_date_from}'
            ";
        } else if ($entry_date_from != '') {
            $whereCond = "
            jm.entry_date >= '{$entry_date_from}'
            ";
            $whereCondBF = "
                jm.entry_date < '{$entry_date_from}'
            ";
        }
        $whereCond   = $whereCond != '' ? 'AND ' . $whereCond : '';
        $whereCondBF = $whereCondBF != '' ? 'AND ' . $whereCondBF : '';

        if ($entry_date_from != '' || $entry_date_to != '') {
            $broughtForwardSQL = "
            +

            IFNULL(
               (SELECT SUM(j.credit)
                FROM journal j
                JOIN journal_master jm ON jm.journal_master_id = j.journal_master_id
                WHERE j.acc_head_id = ah.acc_head_id
                {$whereCondBF}),
                0
            ) -

            IFNULL(
               (SELECT SUM(j.debit)
                FROM journal j
                JOIN journal_master jm ON jm.journal_master_id = j.journal_master_id
                WHERE j.acc_head_id = ah.acc_head_id
                {$whereCondBF}),
                0
            )
            ";
        }

        $decimal = "DECIMAL(20,2)";
        $SQL = "
        SELECT ah.*
        	  ,ah.title AS account
        	  ,c.code AS currency_code
              ,IFNULL(
                   CASE
                   WHEN (ah.opening_balance_debit > 0) THEN CAST(-ah.opening_balance_debit AS {$decimal})
                   WHEN (ah.opening_balance_credit > 0) THEN ah.opening_balance_credit
                   END,
                   0
               ) AS opening_balance

              ,IFNULL(
                   CASE
                   WHEN (ah.opening_balance_debit > 0) THEN CAST(-ah.opening_balance_debit AS {$decimal})
                   WHEN (ah.opening_balance_credit > 0) THEN ah.opening_balance_credit
                   END,
                   0
               )
               {$broughtForwardSQL} AS brought_forward

              ,CAST(
                 -(SELECT SUM(j.debit)
                   FROM journal j
                   JOIN journal_master jm ON jm.journal_master_id = j.journal_master_id
                   WHERE j.acc_head_id = ah.acc_head_id
                   {$whereCond})
               AS DECIMAL(20,2) ) AS debit_sum

              ,(SELECT SUM(j.credit)
                FROM journal j
                JOIN journal_master jm ON jm.journal_master_id = j.journal_master_id
                WHERE j.acc_head_id = ah.acc_head_id
                {$whereCond}) AS credit_sum

              ,CAST(
                 -(SELECT SUM(j.debit)
                   FROM journal j
                   JOIN journal_master jm ON jm.journal_master_id = j.journal_master_id
                   WHERE j.acc_head_id = ah.acc_head_id
                   {$whereCond}
                   {$whereCondAvailBal})
               AS {$decimal}) AS debit_sum_avail_bal

              ,(SELECT SUM(j.credit)
                FROM journal j
                JOIN journal_master jm ON jm.journal_master_id = j.journal_master_id
                WHERE j.acc_head_id = ah.acc_head_id
                {$whereCond}
                {$whereCondAvailBal}) AS credit_sum_avail_bal

              ,CAST(
                 -(SELECT SUM(j.debit_base)
                   FROM journal j
                   JOIN journal_master jm ON jm.journal_master_id = j.journal_master_id
                   WHERE j.acc_head_id = ah.acc_head_id
                   {$whereCond})
               AS {$decimal}) AS debit_base_sum

              ,(SELECT SUM(j.credit_base)
                FROM journal j
                JOIN journal_master jm ON jm.journal_master_id = j.journal_master_id
                WHERE j.acc_head_id = ah.acc_head_id
                {$whereCond}) AS credit_base_sum

        FROM acc_head ah
        LEFT JOIN currency c ON ah.currency_id = c.currency_id
        WHERE ah.acc_head_id = {$acc_head_id}
        ";
        //print $SQL;

        $rowAccHead = $fn->getRecordBySQL($SQL, MYSQL_ASSOC);

        $rowAccHead['ledger_balance'] = $rowAccHead['debit_sum']
                                      + $rowAccHead['credit_sum']
                                      + $rowAccHead['brought_forward'];
        $rowAccHead['available_balance'] = $rowAccHead['debit_sum_avail_bal']
                                         + $rowAccHead['credit_sum_avail_bal']
                                         + $rowAccHead['brought_forward'];
        $rowAccHead['ledger_balance_base'] = $rowAccHead['debit_base_sum']
                                      + $rowAccHead['credit_base_sum'];

        return $rowAccHead;
    }

    function getCounterCurrencySQL($showBaseCurr = false) {
        $fn = Zend_Registry::get('fn');

        $whereCondExtra = '';
        //hide base currency
        if (!$showBaseCurr) {
            $acc_head_id = $this->getBaseCurrencyAccHeadIdCounter();
            $whereCondExtra = "AND ah.acc_head_id != {$acc_head_id}";
        }
        $SQL = "
        SELECT DISTINCT ah.acc_head_id
              ,c.code
        FROM acc_head ah
        JOIN acc_category ac ON ah.acc_category_id = ac.acc_category_id
        JOIN currency c ON ah.currency_id = c.currency_id
        WHERE ac.category_type = 'Cash Account'
        {$whereCondExtra}
        ORDER BY c.code
        ";
        return $SQL;
    }

    function getCashCurrencySQL() {
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT DISTINCT ah.acc_head_id
              ,c.code
        FROM acc_head ah
        JOIN acc_category ac ON ah.acc_category_id = ac.acc_category_id
        JOIN currency c ON ah.currency_id = c.currency_id
        WHERE ac.category_type = 'Cash Account'
        ORDER BY c.code
        ";
        return $SQL;
    }

    function getSundryCreditDebitAcHeadsSQL($showBaseCurr = false) {
        $fn = Zend_Registry::get('fn');

        $whereCondExtra = '';
        //hide base currency
        if (!$showBaseCurr) {
            $acc_head_id = $this->getBaseCurrencyAccHeadIdCounter();
            $whereCondExtra = "AND ah.acc_head_id != {$acc_head_id}";
        }
        $SQL = "
        SELECT DISTINCT ah.acc_head_id
              ,ah.title
        FROM acc_head ah
        JOIN acc_category ac ON ah.acc_category_id = ac.acc_category_id
        JOIN currency c ON ah.currency_id = c.currency_id
        WHERE ac.category_type = 'Sundry Creditor / Debtor'
        {$whereCondExtra}
        ORDER BY ah.title
        ";
        return $SQL;
    }

    function getBaseCurrencyAccHeadIdCounter() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $acc_company_id = $fn->getSessionParam('acc_company_id');

        $SQL = "
        SELECT ah.acc_head_id
        FROM acc_head ah
        JOIN acc_company ac ON ac.base_currency_id = ah.currency_id
        JOIN acc_category acat ON acat.acc_category_id = ah.acc_category_id
        WHERE acat.category_type = 'Cash Account'
          AND ac.acc_company_id = {$acc_company_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        return $row['acc_head_id'];
    }

    function getCurrencyAccHeadIdCounter($currency_code) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT ah.acc_head_id
        FROM acc_head ah
        JOIN acc_category acat ON acat.acc_category_id = ah.acc_category_id
        JOIN currency c ON c.currency_id = ah.currency_id
        WHERE acat.category_type = 'Cash Account'
          AND c.code = '{$currency_code}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        return $row['acc_head_id'];
    }

    function getCurrencyCodeByAccHeadId($acc_head_id = '') {
        $fn = Zend_Registry::get('fn');
        $dbz = Zend_Registry::get('dbz');

        $code = '';

        $SQL = "
        SELECT c.code
        FROM acc_head ah
        JOIN currency c ON ah.currency_id = c.currency_id
        WHERE ah.acc_head_id = ?
        ";
        $stmt = $dbz->query($SQL, array($acc_head_id));

        if ($stmt) {
            $row = $stmt->fetch();
            $code = $row['code'];
        }
        return $code;
    }

    function getAccHeadDetails($acc_head_id = '') {
        $fn = Zend_Registry::get('fn');
        $dbz = Zend_Registry::get('dbz');

        if ($acc_head_id == '') {
            $acc_head_id = $fn->getReqParam('acc_head_id');
        }
        $rateFor = $fn->getReqParam('rateFor');

        $code = '';

        $rowAccHead  = $fn->getRecordRowByID('acc_head', 'acc_head_id', $acc_head_id);
        $exch_rate = getCPModuleObj('account_currencyConvert')
                     ->model->getExchangeRate($rowAccHead['currency_id'], $rateFor);

        $SQL = "
        SELECT c.code
        FROM acc_head ah
        JOIN currency c ON ah.currency_id = c.currency_id
        WHERE ah.acc_head_id = ?
        ";
        $stmt = $dbz->query($SQL, $acc_head_id);

        if ($stmt) {
            $row = $stmt->fetch();
            $code = $row['code'];
        }

        $arr = array();
        $arr['exch_rate']          = 1 / $exch_rate;
        $arr['exch_rate_lc_to_fc'] = $exch_rate;
        $arr['currency_code']      = $code;

        return $arr;

    }
}
