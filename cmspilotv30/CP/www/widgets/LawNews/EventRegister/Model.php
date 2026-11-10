<?
class CP_Www_Widgets_LawNews_EventRegister_Model extends CP_Common_Lib_WidgetModelAbstract
{
    var $newUserCreated = false;
    /**
     *
     */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $cpUrl  = Zend_Registry::get('cpUrl');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $db  = Zend_Registry::get('db');

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $email = $fn->getPostParam('email', '', true);
        $rec = $fn->getRecordByCondition('contact', "email = '{$email}'");

        $contact_id = $this->addSaveContact();

        $event_id = $fn->getPostParam('event_id');
        $eventRec = $fn->getRecordRowByID('event', 'event_id', $event_id);

        if ($eventRec['free'] == 1){
            //$order_id = $this->createEventContactForFreeEvent($event_id, $contact_id);
            $order_id = $this->createOrder($contact_id);
        } else {
            $order_id = $this->createOrder($contact_id);
        }

        $this->sendEventRegisterNotificationToAdmin($order_id);
        $this->sendEventRegisterNotificationToUser($order_id);
        $fn->sessionRegenerate();
        $returnUrl  = $cpUrl->getUrlByCatType('Order Success', 'Basket');
        return $validate->getSuccessMessageXML($returnUrl);
    }

    /**
     *
     */
    function getNewValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();

        /****** check if the same person registered before ******/
        //$email = $fn->getPostParam('email', '', true);
        //$event_id = $fn->getPostParam('event_id', '', true);
        //
        //$SQL = "
        //SELECT count(*)
        //FROM event_contact ec
        //JOIN contact c ON (c.contact_id = ec.contact_id)
        //WHERE ec.event_id = {$event_id}
        //  AND c.email = '{$email}'
        //";
        //$result = $db->sql_query($SQL);
        //$order_id = $db->sql_nextid();
        //$numRows = $db->sql_numrows($result);
        //
        //print $numRows;
        //exit();

        $validate->validateData("salutation"  , $ln->gd("cp.form.fld.salutation.err"));
        $validate->validateData("first_name"  , $ln->gd("cp.form.fld.firstName.err"));
        $validate->validateData("last_name"   , $ln->gd("cp.form.fld.lastName.err"));
        $validate->validateData("company_name", $ln->gd("cp.form.fld.companyName.err"));
        $validate->validateData("company_type", $ln->gd("cp.form.fld.companyType.err"));

        $validate->validateData("address1", $ln->gd("cp.form.fld.address1.err")   );
        $validate->validateData("address_city", $ln->gd("cp.form.fld.addressCity.err")   );
        $validate->validateData("phone", $ln->gd("cp.form.fld.phone.err")   );
        $validate->validateData("fax", $ln->gd("cp.form.fld.fax.err")   );

        $validate->validateData("agree_terms", $ln->gd("cp.form.fld.agreeTerms.err")   );
        $validate->validateData("agree_privacy", $ln->gd("cp.form.fld.agreePrivacy.err")   );

        $showCaptcha = $fn->getReqParam('w-lawNews-eventRegister_showCaptcha');

        if ($showCaptcha){
       	    $captcha_code = $fn->getPostParam('captcha_code');
            require_once (CP_LIBRARY_PATH . 'lib_php/securimage/securimage.php');
            $img = new Securimage;
            if ($img->check($captcha_code) == false) {
                $validate->errorArray['captcha_code']['name'] = "captcha_code";
                $validate->errorArray['captcha_code']['msg']  = $ln->gd("cp.form.fld.captchaCode.err");
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
     * @return type
     */
    function addSaveContact(){
        $fn = Zend_Registry::get('fn');
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        $fa = $this->getFields();
        $fa['published'] = 1;
        $rec = $fn->getRecordByCondition('contact', "email = '{$fa['email']}'");

        if(isLoggedInWWW() && $fa['email'] == $_SESSION['cpEmail']){
            $id = $fn->saveRecord($fa, 'contact', 'contact_id', $_SESSION['cpContactId']);
        } else if(!is_array($rec)){
            if ($cpCfg['cp.hasPasswordSalt']) {
                $actual_pass_word = $cpUtil->getRandomPasswordCS1(8);
                $email = $fa['email'];

                $arr = $cpUtil->getSaltAndPasswordArray($email, $actual_pass_word);
                $fa['salt'] = $arr['salt'];
                $fa['pass_word'] = $arr['pass_word'];
            }

            $id = $fn->addRecord($fa, 'contact');
            $this->newUserCreated = true;
        } else if(is_array($rec)){
            $id = $rec['contact_id'];
        }

        return $id;
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'company_type');
        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address3');
        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'department');
        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'chi_name');
        $fa = $fn->addToFieldsArray($fa, 'chi_position');
        $fa = $fn->addToFieldsArray($fa, 'chi_department');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'dont_contact_by_phone', 0);
        $fa = $fn->addToFieldsArray($fa, 'dont_contact_by_fax', 0);
        $fa = $fn->addToFieldsArray($fa, 'agree_contact_by_third_party', 0);

        return $fa;
    }

    /**
     *
     */
    function getFieldsOrder(){
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fa = array();
        foreach ($this->getFields() as $fldName => $fldValue) {
            $fld = "shipping_{$fldName}";
            if($dbUtil->getColumnExists('order', $fld)){
                $fa[$fld] = $fldValue;
            }
        }

        $fa['payment_method'] = '';
        $fa['module'] = 'event_eventItem';
        $fa['order_status'] = 'New';
        $fa['order_date']   = date('Y-m-d');

        return $fa;
    }

    /**
     *
     * @param type $contact_id
     * @return type
     */
    function createOrder($contact_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fa = $this->getFieldsOrder();
        $fa['contact_id'] = $contact_id;
        $fa['currency']   = $fn->getReqParam('currency');

        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'order');
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'order');
        $result = $db->sql_query($SQL);
        $order_id = $db->sql_nextid();

        $this->createOrderItems($order_id);

        return $order_id;
    }

    /**
     *
     * @param type $order_id
     */
    function createOrderItems($order_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $currency = $fn->getReqParam('currency');
        $event_item_id = $fn->getReqParam('event_item_id');

        $selectMultipeEventItem = $fn->getReqParam('w-event-eventItem_selectMultipeEventItem');

        if ($selectMultipeEventItem && is_array($event_item_id)){
            $event_item_id = join(', ', $event_item_id);
            $SQL = "
            SELECT ei.*
            FROM event_item ei
            WHERE ei.event_item_id IN ({$event_item_id})
            ";
        } else {
            $SQL = "
            SELECT ei.*
            FROM event_item ei
            WHERE ei.event_item_id = {$event_item_id}
            ";
        }

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) {
            $qty = $fn->getReqParam("qty_{$row['event_item_id']}", 1);

            $fa = array();
            $fa['order_id']   = $order_id;
            $fa['module']     = 'event_eventItem';
            $fa['record_id']  = $row['event_item_id'];
            $fa['qty']        = $qty;
            $fa['item_title'] = $row['title'];

            if ($currency != ''){
                $fa['unit_price'] = $row['price_'.$currency];
            }

            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'order_item');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'order_item');
            $db->sql_query($SQL);

            $this->createEventContact($order_id, $row);
        }
    }

    /**
     *
     */
    function createEventContact($order_id, $eventItemRec){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        $fa = array();
        $fa['event_id']      = $eventItemRec['event_id'];
        $fa['event_item_id'] = $eventItemRec['event_item_id'];
        $fa['contact_id']    = $orderRec['contact_id'];
        $fa['order_id']      = $order_id;
        $fa['tag_attended']  = 0;

        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'event_contact');
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'event_contact');
        $db->sql_query($SQL);

    }

    /**
     *
     */
    function createEventContactForFreeEvent($event_id, $contact_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $event_item_id = $fn->getReqParam('event_item_id');

        if (is_array($event_item_id)){
            $event_item_id = join(', ', $event_item_id);
            $SQL = "
            SELECT ei.*
            FROM event_item ei
            WHERE ei.event_item_id IN ({$event_item_id})
            ";

            $result = $db->sql_query($SQL);

            while ($row = $db->sql_fetchrow($result, MYSQL_ASSOC)) {
                $fa = array();
                $fa['event_id']      = $event_id;
                $fa['event_item_id'] = $row['event_item_id'];
                $fa['contact_id']    = $contact_id;
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'event_contact');
                $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'event_contact');
                $db->sql_query($SQL);
            }
        }
    }

    /**
     *
     */
    function sendEventRegisterNotificationToAdmin($order_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");
        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        $infoArr = $this->getDetailsForEmail($order_id);

        $message = $ln->gd('w.lawNews.eventRegister.form.new.email.notifyBody');
        $message = str_replace("[[order_id]]"    , $orderRec["order_id"]               , $message );
        $message = str_replace("[[event]]"       , $infoArr['event_title']             , $message );
        $message = str_replace("[[event_item]]"  , $infoArr['event_items_title']       , $message );
        $message = str_replace("[[first_name]]"  , $orderRec["shipping_first_name"]    , $message );
        $message = str_replace("[[last_name]]"   , $orderRec["shipping_last_name"]     , $message );
        $message = str_replace("[[company_name]]", $orderRec["shipping_company_name"]  , $message );
        $message = str_replace("[[email]]"       , $orderRec["shipping_email"]         , $message );
        $message = str_replace("[[currentDate]]" , $currentDate                        , $message );

        $subject   = $ln->gd('w.lawNews.eventRegister.form.new.email.notifySubject');
        $fromName  = $orderRec['shipping_first_name'] . " " . $orderRec['shipping_last_name'];
        $fromEmail = $orderRec['shipping_email'];
        $toName    = $cpCfg['cp.companyName'];
        $toEmail   = $cpCfg['cp.adminEmail'];

        $args = array(
             'toName'    => $cpCfg['cp.companyName']
            ,'toEmail'   => $cpCfg['cp.adminEmail']
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();
    }
    /**
     *
     */
    function sendEventRegisterNotificationToUser($order_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        //-----------------------------------------------------------------//
        $currentDate = $fn->getCPDate(date("Y-m-d H:i:s"), $cpCfg['cp.dateDisplayFormatLong']);
        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        $infoArr = $this->getDetailsForEmail($order_id);

        $message = $ln->gd('w.lawNews.eventRegister.form.new.email.notifyUserBody');
        $message = str_replace("[[order_id]]"    , $orderRec["order_id"]             , $message );
        $message = str_replace("[[event]]"       , $infoArr['event_title']           , $message );
        $message = str_replace("[[event_item]]"  , $infoArr['event_items_title']     , $message );
        $message = str_replace("[[first_name]]"  , $orderRec["shipping_first_name"]  , $message );
        $message = str_replace("[[last_name]]"   , $orderRec["shipping_last_name"]   , $message );
        $message = str_replace("[[company_name]]", $orderRec["shipping_company_name"], $message );
        $message = str_replace("[[email]]"       , $orderRec["shipping_email"]       , $message );
        $message = str_replace("[[currentDate]]" , $currentDate                      , $message );

        if ($this->newUserCreated){
            $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $orderRec['contact_id']);
            $loginDetails = $ln->gd('w.lawNews.eventRegister.form.new.email.notifyNewUserBody');
            $loginDetails = str_replace("[[email]]", $contactRec["email"], $loginDetails);
            //$loginDetails = str_replace("[[pass_word]]" , $contactRec["pass_word"], $loginDetails);
            $message = str_replace("[[newUserSpecialInfo]]", $loginDetails, $message );
        } else {
            $message = str_replace("[[newUserSpecialInfo]]", '', $message );
        }

        $subject = $ln->gd('w.lawNews.eventRegister.form.new.email.notifyUserSubject');
        $toName  = $orderRec['shipping_first_name'] . " " . $orderRec['shipping_last_name'];
        $toEmail = $orderRec['shipping_email'];
        $fromName  = $cpCfg['cp.companyName'];
        $fromEmail = $cpCfg['cp.adminEmail'];

        $args = array(
             'toName'    => $toName
            ,'toEmail'   => $toEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $fromName
            ,'fromEmail' => $fromEmail
        );

        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args));
        $emailMsg->sendEmail();
    }

    function getDetailsForEmail($order_id) {
        $dbUtil = Zend_Registry::get('dbUtil');

        $SQL = "
        SELECT ei.title AS event_item_title
              ,e.title AS event_title
        FROM order_item oi
        JOIN event_item ei ON (ei.event_item_id = oi.record_id)
        JOIN event e ON (ei.event_id = e.event_id)
        WHERE oi.order_id = {$order_id}
        ";
        $orderItemRecs = $dbUtil->getSQLResultAsArray($SQL);

        $event_title = '';
        $event_items_title = '';

        if (count($orderItemRecs) > 0){
            $event_title = $orderItemRecs[0]['event_title'];
            $arr = array();
            foreach($orderItemRecs AS $rec){
                $arr[] = $rec['event_item_title'];
            }

            $event_items_title = join(', ', $arr);
        }

        $arr['event_title'] = $event_title;
        $arr['event_items_title'] = $event_items_title;

        return $arr;
    }
}