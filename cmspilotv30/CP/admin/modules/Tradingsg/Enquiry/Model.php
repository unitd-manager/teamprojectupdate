<?
class CP_Admin_Modules_Tradingsg_Enquiry_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $countryAppendSQL = "";
        $countryJoinSQL   = "";

        if ($cpCfg['m.webBasic.enquiry.showCountry'] == 1) {
            $countryAppendSQL = ",co.country_name AS country_name";
            $countryJoinSQL   = "LEFT JOIN (country co) ON (e.country_id = co.country_id)";
        }

        $SQL = "
        SELECT e.*
              ,com.company_name AS c_company_name
              ,com.email AS c_company_email
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
              ,gc.name AS country_name
              ,cr.call_registry_code
              {$countryAppendSQL}
        FROM `enquiry` e
        LEFT JOIN staff s ON (s.staff_id = e.staff_id)
        LEFT JOIN (company com) ON (e.company_id = com.company_id)
        LEFT JOIN (geo_country gc) ON (e.address_country = gc.country_code)
        LEFT JOIN (call_registry cr) ON (e.call_registry_id = cr.call_registry_id)
        {$countryJoinSQL}
            ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'e';

        $status         = $fn->getReqParam('status');
        $creation_date1 = $fn->getReqParam('creation_date1');
        $creation_date2 = $fn->getReqParam('creation_date2');
        $followUpDate1 	= $fn->getReqParam('followUpDate1');
        $followUpDate2  = $fn->getReqParam('followUpDate2');
        $staff_id       = $fn->getReqParam('staff_id');
        $client_type    = $fn->getReqParam('client_type');
        $country_id     = $fn->getReqParam('country_id');
        $enquiry_id  	= $fn->getReqParam('enquiry_id');

        if ($country_id != '') {
            $searchVar->sqlSearchVar[] = "e.country_id = {$country_id}";
        }

        if ($enquiry_id != "") {
            $searchVar->sqlSearchVar[] = "e.enquiry_id = '{$enquiry_id}'";
       	} else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "e.enquiry_id = '{$tv['record_id']}'";
        } else {

            if ($status != "") {
                $searchVar->sqlSearchVar[] = "e.status = '{$status}'";
            }

            if ($staff_id != "") {
                $searchVar->sqlSearchVar[] = "e.staff_id = '{$staff_id}'";
            }

            if ($client_type != "") {
                $searchVar->sqlSearchVar[] = "e.client_type = '{$client_type}'";
            }

            if ($creation_date1 != "" && $creation_date2 != "" ) {
                $searchVar->sqlSearchVar[] = "(e.creation_date BETWEEN '{$creation_date1} 00:00:00' AND '{$creation_date2} 23:59:59')";
            }

            if ($followUpDate1 != "" && $followUpDate2 != "") {
                $searchVar->sqlSearchVar[] = "(e.follow_up_date BETWEEN '{$followUpDate1}' AND '{$followUpDate2}')";
            } else if ($followUpDate1 != "" && $followUpDate2 == "") {
                $followUpDate2 = date('Y-m-d');
                $searchVar->sqlSearchVar[] = "(e.follow_up_date BETWEEN '{$followUpDate1}' AND '{$followUpDate2}')";
            } else if ($followUpDate1 == "" && $followUpDate2 != "") {
                $followUpDate1 = date('Y') . '-01-01';
                $searchVar->sqlSearchVar[] = "(e.follow_up_date BETWEEN '{$followUpDate1}' AND '{$followUpDate2}')";
            }

            if ($tv['keyword'] != '') {
                $searchVar->sqlSearchVar[] = "(
                    e.comments   		LIKE '%{$tv['keyword']}%'
                 OR e.first_name 		LIKE '%{$tv['keyword']}%'
                 OR e.last_name  		LIKE '%{$tv['keyword']}%'
                 OR e.email      		LIKE '%{$tv['keyword']}%'
                 OR e.title      		LIKE '%{$tv['keyword']}%'
                 OR com.company_name    LIKE '%{$tv['keyword']}%'
                )";
            }
        }

        $fnModCountry = includeCPClass('ModuleFns', 'common_country');
        $searchVar = $fnModCountry->setCountrySearch($searchVar, 'e');

        $searchVar->sortOrder = "e.creation_date DESC";
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
        $db = Zend_Registry::get('db');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $SQL = "SELECT max(enquiry_code) AS enquiry_code FROM enquiry";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $fa                       = $this->getFields();
        $fa['enquiry_code']       = $this->getUpdateEnquiryCode();
        $fa['staff_id']           = $_SESSION['staff_id'];
        $fa['follow_up_date']     = date('Y-m-d', strtotime(' + 7 days'));
        $fa['status']             = 'In Progress';
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
        $fa = $fn->addToFieldsArray($fa, 'country_code');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'comments');

        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'enquiry_type');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'phone_area_code');
        $fa = $fn->addToFieldsArray($fa, 'follow_up_date');
        $fa = $fn->addToFieldsArray($fa, 'staff_id');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'country_id');
        $fa = $fn->addToFieldsArray($fa, 'company_id');

        return $fa;
    }

    //==================================================================//
    function getExportData($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'first_name'      => $phpExcel->getFldObj('First Name')
             ,'last_name'       => $phpExcel->getFldObj('Last Name')
             ,'email'           => $phpExcel->getFldObj('Email')
             ,'status'          => $phpExcel->getFldObj('Status')
             ,'comments'        => $phpExcel->getFldObj('Comments')
             ,'notes'           => $phpExcel->getFldObj('Admin Comments')
             ,'creation_date'   => $phpExcel->getFldObj('Creation Date')
        );

        $file_name = "Enquiry_" . date("d-m-Y") . ".xls";

        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
    /**
     *
     */
    function getTradingsgEnquiryTradingsgQuoteLinkSQL($id) {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT eq.quote_id
              ,q.quote_code
              ,q.title
              ,(SELECT SUM(qp.selling_price * qp.qty)
                FROM quote_product qp
                WHERE qp.quote_id = eq.quote_id
                GROUP BY qp.quote_id
                ) AS total_quote_amount
        FROM enquiry_quote eq
        LEFT JOIN quote q ON q.quote_id = eq.quote_id
        WHERE q.enquiry_id = eq.enquiry_id
          AND q.enquiry_id = '{$id}'
        ";

        return $SQL;
    }
    /**
     *
     */
    function getRaiseQuote() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $enquiry_id = $fn->getReqParam('enquiry_id');
        $rowQuoteSrc = $fn->getRecordRowByID('enquiry', 'enquiry_id', $enquiry_id);

        $fa = array();
        $fa['status']        = 'Quote Generated';
        $fa                  = $fn->addModificationDetailsToFieldsArray($fa, 'enquiry');
        $whereCondition = "WHERE enquiry_id = {$enquiry_id}";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'enquiry', $whereCondition);
        $db->sql_query($SQL);

        //create header
        $quote_code = $fn->getSettingsValueByKey('quoteCodePrefix') . $fn->getSequenceFromSettings('nextQuoteCode');
        $quote_date    = strtotime(date('Y-m-d'));
        $followup_date = strtotime('+7 days', $quote_date);

        if ($cpCfg['countryForCurrency'] == 'India'){
            $currency = 'INR';
        } else if ($cpCfg['countryForCurrency'] == 'Singapore'){
            $currency = 'SGD';
        } else {
            $currency = '';
        }

        $fa = array();
        $fa['quote_code']              = $quote_code;
        $fa['quote_date']              = date('Y-m-d', $quote_date);
        $fa['follow_up_date']          = date('Y-m-d', $followup_date);
        $fa['status']                  = 'New';
        $fa['priority']                = 'High';
        $fa['currency']                = $currency;
        $fa['creation_date']           = date('Y-m-d');
        $fa['note']                    = $rowQuoteSrc['comments'];
        $fa['title']                   = $rowQuoteSrc['title'];
        $fa['company_id']              = $rowQuoteSrc['company_id'];
        $fa['enquiry_id']              = $enquiry_id;
        $fa['staff_id']                = $rowQuoteSrc['staff_id'];
        $fa['quote_type']              = 'Requirement from Client';

        if ($cpCfg['countryForCurrency'] == 'India'){
            $paymentTerms = nl2br("CST/VAT Applicable \nP & F - 3% \n50% against PO and 50% against Proforma Invoice prior to Dispatch \nOffer valid from 1 month from the Quote date", false);
            $sentence = str_replace('<br>', ' ', $paymentTerms);

            $fa['note']             = '';
            $fa['delivery_terms']   = '4 - 6 weeks from the receipt of PO';
            $fa['payment_terms']    = $sentence;
        }

        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'quote');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'quote');
        $db->sql_query($SQL);
        $quote_id = $db->sql_nextid();

        $fa1 = array();
        $fa1['quote_id']                = $quote_id;
        $fa1['enquiry_id']              = $rowQuoteSrc['enquiry_id'];
        $fa1 = $fn->addCreationDetailsToFieldsArray($fa1, 'enquiry_quote');

        $SQL = $dbUtil->getInsertSQLStringFromArray($fa1, 'enquiry_quote');
        $db->sql_query($SQL);
            //$cpUtil->redirect("index.php?_topRm=opportunity&module=manPower_callRegistry&_action=edit&call_registry_id={$call_registry_id}");

        $url = "index.php?_topRm=order&module=tradingsg_quote".
               "&_action=edit&record_id={$quote_id}";

        $cpUtil->redirect($url);
    }

    function getUpdateEnquiryCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $nextEnquiryCode = $fn->getSettingsValueByKey("nextEnquiryCode");

        if($nextEnquiryCode < 10){
            $enquiryCode = $fn->getSettingsValueByKey('enquiryCodePrefix') . '-' . $nextEnquiryCode;
        }
        else if($nextEnquiryCode < 99){
            $enquiryCode = $fn->getSettingsValueByKey('enquiryCodePrefix') . '-' . $nextEnquiryCode;
        }
        else if($nextEnquiryCode > 99 || $nextOppCode < 999){
            $enquiryCode = $fn->getSettingsValueByKey('enquiryCodePrefix') . '-' . $nextEnquiryCode;
        }
        else{
            $enquiryCode = $fn->getSettingsValueByKey('enquiryCodePrefix') . '-' . $nextEnquiryCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextEnquiryCode'";
        $result = $db->sql_query($SQL);

        return $enquiryCode;
    }

}
