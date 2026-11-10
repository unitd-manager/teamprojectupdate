<?
class CP_Admin_Modules_Account_CurrencyConvert_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT cc.*
        	  ,c.title AS from_currency
        	  ,c.code AS from_currency_code
        	  ,c2.title AS to_currency
        	  ,c2.code AS to_currency_code
        FROM currency_convert cc
        LEFT JOIN currency c ON c.currency_id = cc.from_currency_id
        LEFT JOIN currency c2 ON c2.currency_id = cc.to_currency_id
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        if ($tv['record_id'] != '' ) {
            $searchVar->sqlSearchVar[] = "cc.currency_convert_id  = '{$tv['record_id']}'";

        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'cc.currency_convert_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.title LIKE '%{$tv['keyword']}%'
                    OR c.code LIKE '%{$tv['keyword']}%'
                    OR c2.title LIKE '%{$tv['keyword']}%'
                    OR c2.code LIKE '%{$tv['keyword']}%'
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
        $validate->validateData('from_currency_id', 'Please enter the from value');
        $validate->validateData('to_currency_id', 'Please enter the to value');

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

        $validate->validateData('from_currency_id', 'Please enter the from value');
        $validate->validateData('to_currency_id', 'Please enter the to value');

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

        $fa = $fn->addToFieldsArray($fa, 'from_currency_id');
        $fa = $fn->addToFieldsArray($fa, 'to_currency_id');
        $fa = $fn->addToFieldsArray($fa, 'exch_rate_sell');
        $fa = $fn->addToFieldsArray($fa, 'exch_rate_buy');
        $fa = $fn->addToFieldsArray($fa, 'exch_rate_sell_evening');
        $fa = $fn->addToFieldsArray($fa, 'exch_rate_buy_evening');

        return $fa;
    }

    //==================================================================//
    function getExportData($dataArray){
        $db = Zend_Registry::get('db');

        $phpExcel = includeCPClass('Lib', 'PhpExcelWrapper', 'PhpExcelWrapper');
        $objPHPExcel = $phpExcel->excelObj;
        $file_name = "Currency_" . date("d-m-Y") . ".xls";
        $phpExcel->getExportHeader($file_name);

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;
        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'ID');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'From Currency');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'To Currency');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Exchange Rate');

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        //============================================================================= //
        foreach ($dataArray as $row){
            $colc = 0;
            $rowc++;

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['currency_convert_id']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['from_currency']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['to_currency']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['exch_rate_sell']);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    //==================================================================//
    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExcel = includeCPClass('Lib', 'PhpExcelWrapper', 'PhpExcelWrapper');

        if ($phpExcel->getImportHeader()){
            for ($curRow = 2; $curRow <= $phpExcel->countRows; $curRow++) {
                $fieldsArray = array();
                $fa = array();
                $currency_convert_id     = $phpExcel->getExcelFieldValue("ID", $curRow);
                $fa['exch_rate_sell']    = $phpExcel->getExcelFieldValue("Exchange Rate", $curRow);
                $fa['modification_date'] = date("Y-m-d H:i:s");

                $SQL = "
                SELECT *
                FROM currency_convert
                WHERE currency_convert_id = '{$currency_convert_id}'
                ";
                $result= $db->sql_query($SQL);
                $numRows       = $db->sql_numrows($result);

                if($numRows == 0){
                    //$SQL        = $dbUtil->getInsertSQLStringFromArray($fa, "currency_convert");
                    //$result     = $db->sql_query($SQL);
                    //$currency_id = $db->sql_nextid();
                } else {
                    $row = $db->sql_fetchrow($result);
	   	            $whereCondition = "WHERE currency_convert_id = {$row['currency_convert_id']}";
                    $SQL    = $dbUtil->getUpdateSQLStringFromArray($fa, "currency_convert", $whereCondition);
                    $result = $db->sql_query($SQL);
                    $currency_convert_id = $row['currency_convert_id'];
                }
            }
        }

        $text = "
        <script>
           window.opener.location = window.opener.location;
        </script>
        <h3>Import Complete. Please close this window.</h3>
        ";

        return $text;
    }

    /**
     *
     * @param type $id : The value can be currency_code or currency_id
     * @param type $convertType : baseToCurr/currToBase 
     * 
     */
    function getExchangeRate($id, $rateFor = 'sell', $convertType = 'currToBase') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        if (!is_numeric($id)) { //currency code: ex: EUR or RMB etc
            $id = getCPModelObj('account_currency')->getIdByCurrencyCode($id);
        }

        $acc_company_id = $fn->getSessionParam('acc_company_id');
        $rowAccComp = $fn->getRecordRowByID('acc_company', 'acc_company_id', $acc_company_id);

        $from_currency_id = '';
        $to_currency_id = '';
        if ($convertType == 'currToBase') {
            $from_currency_id = $id;
            $to_currency_id = $rowAccComp['base_currency_id'];
        } else {
            $from_currency_id = $rowAccComp['base_currency_id'];
            $to_currency_id = $id;
        }

        $exchRateFld = '';
        if ($rateFor == 'sell') {
            $exchRateFld = 'exch_rate_sell';
        } else {
            $exchRateFld = 'exch_rate_buy';
        }
        $SQL = "
        SELECT TRIM(
        	      TRAILING '.' FROM (TRIM(TRAILING '0' FROM cc.{$exchRateFld}))
        	   ) AS exch_rate
        FROM currency_convert cc
        WHERE cc.from_currency_id = {$from_currency_id}
          AND cc.to_currency_id = {$to_currency_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        return $row['exch_rate'];
    }
    
    function getUpdateEveningRate() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        
        if ($fn->getSettingsValueByKey('m.account.currencyConvert.rateboardShowEveningRate') == 1) {
            $fn->setSettingsValueByKey('m.account.currencyConvert.rateboardShowEveningRate', 0);
        } else {
            $fn->setSettingsValueByKey('m.account.currencyConvert.rateboardShowEveningRate', 1);
        }
    }
}
