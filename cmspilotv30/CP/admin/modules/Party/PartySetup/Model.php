<?
class CP_Admin_Modules_Party_PartySetup_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT ps.*
              ,c.title AS charity_title
              ,car.title AS card_title
              ,CONCAT_WS(' ', ca.first_name, ca.last_name) AS contact_name
        FROM party_setup ps
        LEFT JOIN (charity c) ON (ps.charity_id = c.charity_id)
        LEFT JOIN (card car) ON (ps.card_id = car.card_id)
        LEFT JOIN (contact ca) ON (ps.contact_id = ca.contact_id)
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 'ps';

        $country_id     = $fn->getReqParam('country_id');
        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "ps.party_setup_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'ps.party_setup_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       ps.event_name   LIKE '%{$tv['keyword']}%'
                    OR ps.celebrant_name LIKE '%{$tv['keyword']}%'
                    OR c.title LIKE '%{$tv['keyword']}%'
                    OR ps.gift_chosen LIKE '%{$tv['keyword']}%'
                    OR ps.additional_instruction LIKE '%{$tv['keyword']}%'
                    OR ps.status LIKE '{$tv['keyword']}'
                )";
            }

            if ($start_date != "" && $end_date != "" ) {
                $searchVar->sqlSearchVar[] = "(ps.event_date BETWEEN '{$start_date}' AND '{$end_date}')";
            }
            if ($tv['special_search'] == 'Test Records') {
                $searchVar->sqlSearchVar[] = "ps.is_test = 1";
            } else {
                $searchVar->sqlSearchVar[] = "(ps.is_test != 1 OR ps.is_test IS NULL)";
            }
        }

        $searchVar->sortOrder = "ps.creation_date DESC";
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('event_name', 'Please enter the event name');

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
        $validate->validateData('event_name', 'Please enter the title');

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

        $is_test = $fn->getReqParam('is_test');
        
        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'event_name');
        $fa = $fn->addToFieldsArray($fa, 'celebrant_name');
        $fa = $fn->addToFieldsArray($fa, 'event_date');
        $fa = $fn->addToFieldsArray($fa, 'event_time');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'event_detail');
        $fa = $fn->addToFieldsArray($fa, 'additional_instruction');
        $fa = $fn->addToFieldsArray($fa, 'card_id');
        $fa = $fn->addToFieldsArray($fa, 'charity_id');
        $fa = $fn->addToFieldsArray($fa, 'gift_chosen');
        $fa = $fn->addToFieldsArray($fa, 'percentage_to_donate');
        $fa = $fn->addToFieldsArray($fa, 'allow_guest_to_blind_donation', 0);
        $fa = $fn->addToFieldsArray($fa, 'is_test', 0);

        return $fa;
    }

    /**
     *
     */
    function getPartySetupSQL() {
        return "
        SELECT ps.party_setup_id
              ,ps.event_name
        FROM party_setup ps
        ORDER BY ps.event_name
        ";
    }

    function getPartyPartySetupPartyMessageLinkSQL($id) {
        return $SQL = "
        SELECT m.message_id
              ,m.title
              ,DATE_FORMAT(m.message_date, '%d-%m-%Y') AS message_date
        FROM message m
            ,party_setup ps
        WHERE m.party_setup_id = ps.party_setup_id
          AND ps.party_setup_id = '{$id}'
        ORDER BY m.message_id DESC
        ";
    }

    function getPartyPartySetupPartyGuestLinkSQL($id) {
        $SQL = "
        SELECT gl.guest_list_id
              ,gl.name
              ,gl.email
        FROM guest_list gl
            ,party_setup ps
        WHERE gl.party_setup_id = ps.party_setup_id
          AND ps.party_setup_id = '{$id}'
        ORDER BY gl.name
                ,gl.email
        ";
        return $SQL;
    }

    function getPartyPartySetupEcommerceOrderLinkSQL($id) {
        $fn = Zend_Registry::get('fn');

        $order_status = $fn->getReqParam('order_status');

        $whereSQL = '';
        $whereSQL2 = '';

        if ($order_status != "") {
            $whereSQL .= " AND o.order_status = '{$order_status}'";
            $whereSQL2 .= " AND order_status = '{$order_status}'";
        }

        return $SQL = "
        SELECT o.order_id
              ,o.order_id AS order_code
              ,o.creation_date
              ,gl.name
              ,gl.email

              ,(SELECT (SUM(oi.unit_price * oi.qty))
                FROM order_item oi
                WHERE oi.order_id = o.order_id
               ) AS order_amount

              ,(SELECT (SUM(oi.unit_price * oi.qty))
                FROM order_item oi
                WHERE oi.order_id IN
                    (SELECT order_id
                     FROM `order`
                     WHERE party_setup_id = '{$id}'
                     {$whereSQL2}
                     )
               ) AS order_amount_sum

              ,o.payment_method
              ,o.order_status
        FROM `order` o
        JOIN order_item oi ON oi.order_id = o.order_id
        JOIN guest_list gl ON gl.guest_list_id = oi.guest_list_id
        WHERE o.party_setup_id = '{$id}'
        {$whereSQL}
        ORDER BY o.order_id
        ";
    }

    function getChangeGuestAmount() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $order_id = $fn->getReqParam('order_id');
        $amount   = $fn->getReqParam('amount');

        $rowOrd = $fn->getRecordRowByID('order', 'order_id', $order_id);

        //update order_item table
        $fa = array();
        $fa['unit_price'] = $amount;

        $whereCondition = "
        WHERE order_id = '{$order_id}'
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'order_item', $whereCondition);
        $db->sql_query($SQL);
        
        //update guest_list table
        $fa = array();
        $fa['amount']       = $amount;
        $fa['amount_entry'] = $amount;

        $whereCondition = "
        WHERE guest_list_id = '{$rowOrd['guest_list_id']}'
        ";
        $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'guest_list', $whereCondition);
        $db->sql_query($SQL);
        
        $ret = array('status' => 'success');
        return $ret;
        
    }
}
