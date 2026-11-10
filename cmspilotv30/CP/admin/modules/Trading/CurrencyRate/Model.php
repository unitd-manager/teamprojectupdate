<?
class CP_Admin_Modules_Trading_CurrencyRate_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $SQL = "
        SELECT cr.*
        FROM currency_rate cr
        ";

        return $SQL;
    }

    /**
     *
     */
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $currency_from = $fn->getReqParam('currency_from');
        $currency_to   = $fn->getReqParam('currency_to');

        $SQL = "
        SELECT COUNT(*) AS count
        FROM currency_rate cr
        WHERE cr.currency_to   = '{$currency_to}'
          AND cr.currency_from = '{$currency_from}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        if ($row['count'] > 0) {
            $validate->errorArray['currency_from']['name'] = 'currency_from';
            $validate->errorArray['currency_from']['msg']   = 'Duplicate currency combination.';
            $validate->errorArray['currency_to']['name'] = 'currency_to';
            $validate->errorArray['currency_to']['msg']   = '';
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
    function getAdd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id, 'detail');
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
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $currency_rate_id = $fn->getReqParam('currency_rate_id');
        $rowCurrRate = $fn->getRecordRowByID('currency_rate', 'currency_rate_id', $currency_rate_id);

        $currency_from = $fn->getReqParam('currency_from');
        $currency_to   = $fn->getReqParam('currency_to');

        if ($rowCurrRate['currency_from'] != $currency_from || $rowCurrRate['currency_to'] != $currency_to) {
            $SQL = "
            SELECT COUNT(*) AS count
            FROM currency_rate cr
            WHERE cr.currency_to   = '{$currency_to}'
              AND cr.currency_from = '{$currency_from}'
            ";
            $result = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
            if ($row['count'] > 0) {
                $validate->errorArray['currency_from']['name'] = 'currency_from';
                $validate->errorArray['currency_from']['msg']  = 'Duplicate currency combination.';
                $validate->errorArray['currency_to']['name'] = 'currency_to';
                $validate->errorArray['currency_to']['msg']  = '';
            }
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
    function getSave($tableName = '', $keyField = '', $keyFieldVal = ''){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id, 'detail');
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
        $fa = $fn->addToFieldsArray($fa, 'currency_from');
        $fa = $fn->addToFieldsArray($fa, 'currency_to');
        $fa = $fn->addToFieldsArray($fa, 'exchange_rate');

        return $fa;
    }

    /**
     *
     */
    function getCurrencyExchageRate($currency_from = '', $currency_to = '') {
        global $db, $fn, $utilCommon;
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        if ($currency_from == $currency_to) {
            return 1;
        }
        if ($currency_from == '' || $currency_to == '') {
            return 0;
        }

        $SQL = "
        SELECT exchange_rate
        FROM currency_rate
        WHERE currency_from = '{$currency_from}'
          AND currency_to   = '{$currency_to}'
        ";
        $result = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        if ($numRows == 0) {
            return 0;
        }

        $row = $db->sql_fetchrow($result);
        if ($row['exchange_rate'] <= 0) {
            return 1;
        }

        return $row['exchange_rate'];
    }

    /**
     *
     */
    function getConvertedCurrencyValue($currency_from, $currency_to, $amount) {
        global $db, $fn, $utilCommon;
        $exchange_rate = $this->getCurrencyExchageRate($currency_from, $currency_to);
        $converted_amount = $amount * $exchange_rate;

        return $converted_amount;
    }

    /**
     *
     */
    function getCurrencyExchageRateFromWeb() {
        global $db, $fn, $cpUtil;
        $currency_from = $fn->getReqParam('currency_from');
        $currency_to   = $fn->getReqParam('currency_to');

        $exchRate = $this->getCurrencyExchageRateFromWeb($currency_from, $currency_to);
        return $cpUtil->getJsonFromArray(array('exchange_rate' => $exchRate));
    }

    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "cr.currency_rate_id = {$tv['record_id']}";
        } else {
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       cr.currency_from  LIKE '%{$tv['keyword']}%'
                    OR cr.currency_to LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $searchVar->sortOrder = "
         cr.currency_from
        ,cr.currency_to
        ";
    }
}
