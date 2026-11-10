<?
class CP_Www_Themes_Quest_Hooks_ModuleEcommerceBasket
{
    /**
     *
     */
    function getConfirmOrder($modelObj) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        $fa = $this->getFields();
        $memberType = $fn->getSessionParam('cpLoginTypeWWW', 'pms_contact');

        $fa = $fn->addCreationDetailsToFieldsArray($fa, 'order');
        $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'order');
        $result = $db->sql_query($SQL);
        $order_id = $db->sql_nextid();


        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

        if ($memberType == 'pms_company'){
            $traineeData = @$_SESSION['shippingDetails']['traineeData'];

            if (is_array($traineeData)){
                foreach ($traineeData AS $traineeRow){
                    $contactRec = $fn->getRecordByCondition('contact', "email = '{$traineeRow['email']}'");
                    $traineeRow['company_id'] = $_SESSION['cpContactId'];
                    
                    $fa = array();
                    $fa['first_name'] = $traineeRow['first_name'];
                    $fa['last_name'] = $traineeRow['last_name'];
                    $fa['gender'] = $traineeRow['gender'];
                    $fa['marital_status'] = $traineeRow['marital_status'];
                    $fa['id_card_no'] = $traineeRow['id_card_no'];
                    $fa['date_of_birth'] = $traineeRow['date_of_birth'];
                    $fa['nationality'] = $traineeRow['nationality'];
                    $fa['race'] = $traineeRow['race'];
                    $fa['phone'] = $traineeRow['phone'];
                    $fa['fax'] = $traineeRow['fax'];
                    $fa['mobile'] = $traineeRow['mobile'];
                    $fa['email'] = $traineeRow['email'];
                    $fa['address_flat'] = $traineeRow['address1'];
                    $fa['address_street'] = $traineeRow['address2'];
                    $fa['school_highest_qual'] = $traineeRow['school_highest_qual'];
                    $fa['position'] = $traineeRow['position'];
                    $fa['salary_range'] = $traineeRow['salary_range'];
                    $fa['nature_of_business'] = $traineeRow['nature_of_business'];
                    $fa['company_id'] = $traineeRow['company_id'];

                    if (is_array($contactRec)){
                        $id = $fn->saveRecord($fa, 'contact', 'contact_id', $contactRec['contact_id']);
                    } else {
                        $id = $fn->addRecord($fa, 'contact');
                    }

                    $fa = array();
                    $fa['course_id']     = $traineeRow['course_id'];
                    $fa['contact_id']    = $id;
                    $fa['order_id']      = $order_id;
                    $fa['language']      = $traineeRow['course_language'];
                    $fa['applying_for_sdf']  = $traineeRow['applying_for_sdf'];
                    $fa['reference_no']  = $traineeRow['reference_no'];
                    $fa['training_date'] = $traineeRow['course_training_date'];
                    $fa['company_id']    = $_SESSION['cpContactId'];
                    $fa = $fn->addCreationDetailsToFieldsArray($fa, 'course_contact');
                    $fn->addRecord($fa, 'course_contact');
                    
                    // create order item for for the course chosen 
                    $course_id = $traineeRow['course_id'];
                    $language  = $traineeRow['course_language'];
                    $training_date  = $traineeRow['course_training_date'];
            
                    $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
            
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'pms_course';
                    $fa['record_id']  = $course_id;
                    $fa['qty']        =  1;
                    $fa['item_title'] = $courseRec['title'];
                    $fa['unit_price'] = $courseRec['price'];
            
                    $fa = $fn->addCreationDetailsToFieldsArray($fa, 'order_item');
                    $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'order_item');
                    $db->sql_query($SQL);
                }
            }

        } else {
            // create order item for for the course chosen 
            $course_id = $_SESSION['shippingDetails']['course_id'];
            $language  = $_SESSION['shippingDetails']['course_language'];
            $training_date  = $_SESSION['shippingDetails']['course_training_date'];
    
            $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
    
            $fa = array();
            $fa['order_id']   = $order_id;
            $fa['module']     = 'pms_course';
            $fa['record_id']  = $course_id;
            $fa['qty']        = 1;
            $fa['item_title'] = $courseRec['title'];
            $fa['unit_price'] = $courseRec['price'];
    
            $fa = $fn->addCreationDetailsToFieldsArray($fa, 'order_item');
            $SQL = $dbUtil->getInsertSQLStringFromArray($fa, 'order_item');
            $db->sql_query($SQL);
            
            $fa = array();
            $fa['course_id']     = $course_id;
            $fa['contact_id']    = $_SESSION['cpContactId'];
            $fa['order_id']      = $order_id;
            $fa['language']      = $language;
            $fa['training_date'] = $training_date;

            $histRec = $fn->getRecordByCondition('course_contact', "contact_id = '{$fa['contact_id']}'
                                                  AND course_id = '{$course_id}'
                                                  AND training_date = '{$training_date}'
                                                  ");

            if (is_array($histRec)){
                $fa = $fn->addModificationDetailsToFieldsArray($fa, 'course_contact');
                $id = $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $histRec['course_contact_id']);
            } else {
                $fa = $fn->addCreationDetailsToFieldsArray($fa, 'course_contact');
                $id = $fn->addRecord($fa, 'course_contact');
            }
        }
        /********************************************/
        $plObj = getCPPluginObj('paymentMethods_' . $orderRec['payment_method']);
        $plObj->model->proceedToGateway($order_id);
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $fa = array();
        foreach ($_SESSION['shippingDetails'] as $fldName => $fldValue) {
            $fld = "shipping_{$fldName}";
            if($dbUtil->getColumnExists('order', $fld)){
                $fa[$fld] = $fldValue;
            }
        }

        $memberType = $fn->getSessionParam('cpLoginTypeWWW', 'pms_contact');
        if ($memberType == 'pms_company'){
            $s = $_SESSION['shippingDetails'];
            $fa2 = array();
            $fa2 = $fn->addToFldsArrBySrcArr($s, $fa2, 'title');
            $fa2 = $fn->addToFldsArrBySrcArr($s, $fa2, 'reg_number');
            $fa2 = $fn->addToFldsArrBySrcArr($s, $fa2, 'address1');
            $fa2 = $fn->addToFldsArrBySrcArr($s, $fa2, 'address2');
            $fa2 = $fn->addToFldsArrBySrcArr($s, $fa2, 'address_po_code');
            $fa2 = $fn->addToFldsArrBySrcArr($s, $fa2, 'address_country_code');
            $fa2 = $fn->addToFldsArrBySrcArr($s, $fa2, 'phone');
            $fa2 = $fn->addToFldsArrBySrcArr($s, $fa2, 'fax');
            $fa2 = $fn->addToFldsArrBySrcArr($s, $fa2, 'nature_of_business');
            $fa['company_id'] = $fn->saveRecord($fa2, 'company', 'company_id', $_SESSION['cpContactId']);

        } else {
            $fa2 = $this->getFieldsContact();
            $fa['contact_id'] = $fn->saveRecord($fa2, 'contact', 'contact_id', $_SESSION['cpContactId']);
        }

        $fa['payment_method']  = $_SESSION['shippingDetails']['payment_method'];
        $fa['module']          = $_SESSION['shippingDetails']['modName'];
        $fa['order_status']    = 'New';
        $fa['shipping_charge'] = getCPWidgetObj('ecommerce_basket')->model->getShippingCharge();
        $fa['contact_module']  = $_SESSION['shippingDetails']['contactModName'];

        return $fa;
    }

    /**
     *
     */
    function getFieldsContact() {
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $modules = Zend_Registry::get('modules');
        $fa = array();

        $s = $_SESSION['shippingDetails'];

        if (isset($s['address_area'])){
            $fa['address_area'] = $s['address_area'];
        }

        $fa['first_name']           = isset($s['first_name'])               ?  $s['first_name']                 : '';
        $fa['last_name']            = isset($s['last_name'])                ?  $s['last_name']                  : '';
        $fa['gender']               = isset($s['gender'])                   ?  $s['gender']                     : '';
        $fa['marital_status']       = isset($s['marital_status'])           ?  $s['marital_status']             : '';
        $fa['id_card_no']           = isset($s['id_card_no'])               ?  $s['id_card_no']                 : '';
        $fa['nationality']          = isset($s['nationality'])              ?  $s['nationality']                : '';
        $fa['race']                 = isset($s['race'])                     ?  $s['race']                       : '';
        $fa['date_of_birth']        = isset($s['date_of_birth'])            ?  $s['date_of_birth']              : '';
        $fa['phone']                = isset($s['phone'])                    ?  $s['phone']                      : '';
        $fa['mobile']               = isset($s['mobile'])                   ?  $s['mobile']                     : '';
        $fa['email']                = isset($s['email'])                    ?  $s['email']                      : '';

        $fa['emergency_contact_name']       = isset($s['emergency_contact_name'])       ?  $s['emergency_contact_name']     : '';
        $fa['emergency_contact_mobile']     = isset($s['emergency_contact_mobile'])     ?  $s['emergency_contact_mobile']   : '';
        $fa['emergency_contact_office_no']  = isset($s['emergency_contact_office_no'])  ?  $s['emergency_contact_office_no']: '';

        $fa['address_flat']         = isset($s['address_flat'])             ?  $s['address_flat']               : '';
        $fa['address_street']       = isset($s['address_street'])           ?  $s['address_street']             : '';
        $fa['address_city']         = isset($s['address_city'])             ?  $s['address_city']               : '';
        $fa['address_po_code']      = isset($s['address_po_code'])          ?  $s['address_po_code']            : '';
        $fa['address_country']      = isset($s['address_country'])          ?  $s['address_country']            : '';

        $fa['school_name']          = isset($s['school_name'])              ?  $s['school_name']                : '';
        $fa['school_country']       = isset($s['school_country'])           ?  $s['school_country']             : '';
        $fa['school_from']          = isset($s['school_from'])              ?  $s['school_from']                : '';
        $fa['school_to']            = isset($s['school_to'])                ?  $s['school_to']                  : '';
        $fa['school_highest_qual']  = isset($s['school_highest_qual'])      ?  $s['school_highest_qual']        : '';

        $fa['company_name']         = isset($s['company_name'])             ?  $s['company_name']               : '';
        $fa['company_roc_no']       = isset($s['company_roc_no'])           ?  $s['company_roc_no']             : '';
        $fa['company_address']      = isset($s['company_address'])          ?  $s['company_address']            : '';
        $fa['company_po_code']      = isset($s['company_po_code'])          ?  $s['company_po_code']            : '';
        $fa['company_phone']        = isset($s['company_phone'])            ?  $s['company_phone']              : '';
        $fa['company_fax']          = isset($s['company_fax'])              ?  $s['company_fax']                : '';
        $fa['position']             = isset($s['position'])                 ?  $s['position']                   : '';
        $fa['yr_of_exp']            = isset($s['yr_of_exp'])                ?  $s['yr_of_exp']                  : '';
        $fa['salary_range']         = isset($s['salary_range'])             ?  $s['salary_range']               : '';
        $fa['apply_for_sdf']        = isset($s['apply_for_sdf'])            ?  $s['apply_for_sdf']              : '';

        foreach ($_SESSION['shippingDetails'] as $fldName => $fldValue) {
            if($dbUtil->getColumnExists('contact', $fldName)){
                $fa[$fldName] = $fldValue;
            }
        }
        
        return $fa;
    }

    /**
     *
     */
    function getSendOrderConfirmationEmails($order_id, $modelObj) {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        //-----------------------------------------------------------------//
        $currentDate  = date("d-M-Y l h:i:s A");

        $order_rec = $fn->getRecordRowByID('`order`', 'order_id', $order_id);

        $memberType = $fn->getSessionParam('cpLoginTypeWWW', 'pms_contact');
        if ($memberType == 'pms_company'){
            $row = $fn->getRecordRowByID('`company`', 'company_id', $order_rec['company_id']);
        } else {
            $row = $fn->getRecordRowByID('`contact`', 'contact_id', $order_rec['contact_id']);
        }

        if(!is_array($row)){
            return;
        }

        $basketArr = $cpCfg['cp.basketArray'][$order_rec['module']];
        $message = $ln->gd($basketArr['emailToAdminBody']);

        $course_id = $_SESSION['shippingDetails']['course_id'];
        $language  = $_SESSION['shippingDetails']['course_language'];
        $training_date  = $_SESSION['shippingDetails']['course_training_date'];

        $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);

        $message = str_replace("[[course_title]]"   , $courseRec["title"]           , $message );
        $message = str_replace("[[language]]"       , $language                     , $message );
        $message = str_replace("[[training_date]]"  , $training_date                , $message );

        if ($memberType == 'pms_company'){
            $message = str_replace("[[company_name]]"    , $row["title"]                , $message );
            $message = str_replace("[[reg_number]]"      , $row["reg_number"]           , $message );
            $message = str_replace("[[phone]]"           , $row["phone"]                , $message );
            $message = str_replace("[[fax]]"             , $row["fax"]                  , $message );
            $message = str_replace("[[email]]"           , $row["email"]                , $message );

            $message = str_replace("[[address1]]"        , $row["address1"]             , $message );
            $message = str_replace("[[address2]]"        , $row["address2"]             , $message );
            $message = str_replace("[[address_city]]"    , $row["address_city"]         , $message );
            $message = str_replace("[[address_po_code]]" , $row["address_po_code"]      , $message );
            $message = str_replace("[[address_country]]" , $row["address_country_code"] , $message );

            $message = str_replace("[[first_name]]"     , $row["first_name"]            , $message );
            $message = str_replace("[[last_name]]"      , $row["last_name"]             , $message );
            $message = str_replace("[[nature_of_business]]" , $row["nature_of_business"] , $message );

            $traineeData = @$_SESSION['shippingDetails']['traineeData'];
            $trainees = '';

            if (is_array($traineeData)){
                foreach ($traineeData AS $traineeRow){
                    $contactRec = $fn->getRecordByCondition('contact', "email = '{$traineeRow['email']}'");
                    $traineeRow['company_id'] = $_SESSION['cpContactId'];
                    if (is_array($contactRec)){
                        $id = $fn->saveRecord($traineeRow, 'contact', 'contact_id', $contactRec['contact_id']);
                    } else {
                        $id = $fn->addRecord($traineeRow, 'contact');
                    }

                    $course_idT = $traineeRow['course_id'];
                    $courseRecT = $fn->getRecordRowByID('course', 'course_id', $course_idT);

                    $trainees .= "
                    <table style='border-bottom:1px solid #000;'>
                        <tr>
                            <td>Course :</td>
                            <td>{$courseRecT['title']}</td>
                        </tr>
                        <tr>
                            <td>Course Date :</td>
                            <td>{$traineeRow['course_training_date']}</td>
                        </tr>
                        <tr>
                            <td>Language :</td>
                            <td>{$traineeRow['course_language']}</td>
                        </tr>
                        <tr>
                            <td>Applying for SDF :</td>
                            <td>{$traineeRow['applying_for_sdf']}</td>
                        </tr>
                        <tr>
                            <td>Reference Number :</td>
                            <td>{$traineeRow['reference_no']}</td>
                        </tr>

                        <tr>
                            <td>First Name :</td>
                            <td>{$traineeRow['first_name']}</td>
                        </tr>
                        <tr>
                            <td>Last Name :</td>
                            <td>{$traineeRow['last_name']}</td>
                        </tr>
                        <tr>
                            <td>Gender :</td>
                            <td>{$traineeRow['gender']}</td>
                        </tr>
                        <tr>
                            <td>NRIC / FIN / Passport :</td>
                            <td>{$traineeRow['id_card_no']}</td>
                        </tr>
                        <tr>
                            <td>Nationality :</td>
                            <td>{$traineeRow['nationality']}</td>
                        </tr>
                        <tr>
                            <td>Race :</td>
                            <td>{$traineeRow['race']}</td>
                        </tr>
                        <tr>
                            <td>Date of Birth :</td>
                            <td>{$traineeRow['date_of_birth']}</td>
                        </tr>
                        <tr>
                            <td>Highest Qualification :</td>
                            <td>{$traineeRow['school_highest_qual']}</td>
                        </tr>
                        <tr>
                            <td>Designation :</td>
                            <td>{$traineeRow['position']}</td>
                        </tr>
                        <tr>
                            <td>Salary Code :</td>
                            <td>{$traineeRow['salary_range']}</td>
                        </tr>
                    </table>
                    ";
                }
            }

            $message = str_replace("[[trainees]]", $trainees, $message );

            $fromName = $row["title"];
        } else {
            $message = str_replace("[[first_name]]"     , $row["first_name"]            , $message );
            $message = str_replace("[[last_name]]"      , $row["last_name"]             , $message );
            $message = str_replace("[[gender]]"         , $row["gender"]                , $message );
            //$message = str_replace("[[marital_status]]" , $row["marital_status"]        , $message );
            $message = str_replace("[[id_card_no]]"     , $row["id_card_no"]            , $message );
            $message = str_replace("[[nationality]]"    , $row["nationality"]           , $message );
            $message = str_replace("[[race]]"           , $row["race"]                  , $message );
            $message = str_replace("[[date_of_birth]]"  , $row["date_of_birth"]         , $message );
            $message = str_replace("[[phone]]"          , $row["phone"]                 , $message );
            $message = str_replace("[[mobile]]"         , $row["mobile"]                , $message );
            $message = str_replace("[[email]]"          , $row["email"]                 , $message );

            //$message = str_replace("[[emergency_contact_name]]"         , $row["emergency_contact_name"]      , $message );
            //$message = str_replace("[[emergency_contact_mobile]]"       , $row["emergency_contact_mobile"]    , $message );
            //$message = str_replace("[[emergency_contact_office_no]]"    , $row["emergency_contact_office_no"] , $message );

            $message = str_replace("[[address_flat]]"       , $row["address_flat"]      , $message );
            $message = str_replace("[[address_street]]"     , $row["address_street"]    , $message );
            //$message = str_replace("[[address_city]]"       , $row["address_city"]      , $message );
            $message = str_replace("[[address_po_code]]"    , $row["address_po_code"]   , $message );
            $message = str_replace("[[address_country]]"    , $row["address_country_code"]   , $message );
            $message = str_replace("[[school_highest_qual]]", $row["school_highest_qual"], $message );

            $message = str_replace("[[company_name]]"       , $row["company_name"]      , $message );
            $message = str_replace("[[position]]"           , $row["position"]          , $message );
            $message = str_replace("[[salary_range]]"       , $row["salary_range"]      , $message );

            /*$message = str_replace("[[school_name]]"        , $row["school_name"]       , $message );
            $message = str_replace("[[school_country]]"     , $row["school_country"]    , $message );
            $message = str_replace("[[school_from]]"        , $row["school_from"]       , $message );
            $message = str_replace("[[school_to]]"          , $row["school_to"]         , $message );
            $message = str_replace("[[company_roc_no]]"     , $row["company_roc_no"]    , $message );
            $message = str_replace("[[company_address]]"    , $row["company_address"]   , $message );
            $message = str_replace("[[company_po_code]]"    , $row["company_po_code"]   , $message );
            $message = str_replace("[[company_phone]]"      , $row["company_phone"]     , $message );
            $message = str_replace("[[company_fax]]"        , $row["company_fax"]       , $message );
            $message = str_replace("[[yr_of_exp]]"          , $row["yr_of_exp"]         , $message );
            $message = str_replace("[[apply_for_sdf]]"      , $row["apply_for_sdf"]     , $message );*/
            
            $fromName  = $row["first_name"] . " " . $row["last_name"];
        }

        $message = str_replace("[[currentDate]]", $currentDate, $message );

        $subject   = $ln->gd($basketArr['emailToAdminSubject']);
        $fromEmail = $row["email"];
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

        $args1 = array(
             'toName'    => $fromName
            ,'toEmail'   => $fromEmail
            ,'subject'   => $subject
            ,'message'   => $message
            ,'fromName'  => $cpCfg['cp.companyName']
            ,'fromEmail' => $cpCfg['cp.adminEmail']
        );
        $emailMsg = includeCPClass('Lib', 'EmailTemplate', 'EmailTemplate', true, array('args' => $args1));
        $emailMsg->sendEmail();
    }

    /**
     *
     */
    function getOrderSuccess($order_id, $modelObj) {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUrl = Zend_Registry::get('cpUrl');

        if ($order_id == ''){
            $order_id = $fn->getSessionParam('cpOrderId');
        }

        if ($order_id > 0){
            $row = $fn->getRecordRowByID('order', 'order_id', $order_id);

            if(!is_array($row)){
                return;
            }

            $basketArr = $cpCfg['cp.basketArray'][$row['module']];
            $text = $ln->gd($basketArr['successMsg']);

            $currentDate  = date("d-M-Y l h:i:s A");

            $text = str_replace("[[first_name]]"     , $row["shipping_first_name"]          , $text);
            $text = str_replace("[[last_name]]"      , $row["shipping_last_name"]           , $text);
            $text = str_replace("[[email]]"          , $row["shipping_email"]               , $text);
            $text = str_replace("[[phone]]"          , $row["shipping_phone"]               , $text);
            $text = str_replace("[[address1]]"       , $row["shipping_address1"]            , $text);
            $text = str_replace("[[address2]]"       , $row["shipping_address2"]            , $text);
            $text = str_replace("[[address_area]]"   , $row["shipping_address_area"]        , $text);
            $text = str_replace("[[address_city]]"   , $row["shipping_address_city"]        , $text);
            $text = str_replace("[[address_state]]"  , $row["shipping_address_state"]       , $text);
            $text = str_replace("[[address_country]]", $row["shipping_address_country_code"], $text);
            $text = str_replace("[[payment_method]]" , $row["payment_method"]               , $text);
            $text = str_replace("[[currentDate]]"    , $currentDate                         , $text);
        }

        unset($_SESSION['shippingDetails']);
        $histUrl = "/index.php?_theme=quest&_spAction=trainingHistory&showHTML=0";

        $text = "
        {$text}
        <div class='floatbox shopBtns'>
            <div class='float_right button'>
                <a href='{$cpUrl->getUrlByCatType('Downloads', 'Content')}'>
                    {$ln->gd('resources')}
                </a>
            </div>
            <div class='float_right button'>
                <a href='/'>
                    {$ln->gd('home')}
                </a>
            </div>
            <div class='float_right button'>
                <a href='{$cpUrl->getUrlByCatType('Order Form', 'Content')}'>
                    {$ln->gd('registerNewCourse')}
                </a>
            </div>
            <div class='float_right button'>
                <a href='javascript:void(0);' link='{$histUrl}' class='jqui-dialog'>
                    {$ln->gd('history')}
                </a>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getController($contObj) {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        $shippingSubmitUrl = '/index.php?widget=ecommerce_shippingDetails&_spAction=save&showHTML=0';

        if ($tv['secType'] == 'Basket'){
            $text = '';
            if ($tv['catType'] == 'Shipping Details'){
                unset($_SESSION['cpReturnUrlAfterLogin']);
                //$_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
                checkLoggedIn();
                $wShip = getCPWidgetObj('ecommerce_shippingDetails');
                $memberType = $fn->getSessionParam('cpLoginTypeWWW', 'pms_contact');

                $text  = $wShip->getWidget(array(
                     'modName' => 'pms_course'
                    ,'contactModName' => $memberType
                    ,'wrapInForm' => false
                ));

                $text = "
                <form id='frmShippingDetails' class='yform columnar' method='post' action='{$shippingSubmitUrl}'>
                    {$text}
                </form>
                ";
            } else if ($tv['catType'] == 'Confirm Order'){
                checkLoggedIn();
                $wShip = getCPWidgetObj('ecommerce_confirmOrder');
                $text = $wShip->getWidget(array(
                    'modName' => 'pms_course'
                ));

            } else if ($tv['catType'] == 'Order Success'){
                $text = $contObj->view->getOrderSuccess();

            } else if ($tv['catType'] == 'Order Fail'){
                $text = $contObj->view->getOrderFail();

            } else {
                $wBasket = getCPWidgetObj('ecommerce_basket');
                $text = $wBasket->getWidget();
            }

            return $text;

        } else if ($tv['secType'] == 'Order Form' || $tv['catType'] == 'Order Form'){
            if (isLoggedInWWW()){
                $wShip = getCPWidgetObj('ecommerce_shippingDetails');
                $memberType = $fn->getSessionParam('cpLoginTypeWWW', 'pms_contact');

                $text = $wShip->getWidget(array(
                     'modName' => 'pms_course'
                    ,'contactModName' => $memberType
                    ,'wrapInForm' => false
                ));

                $text = "
                <form id='frmShippingDetails' class='yform columnar' method='post' action='{$shippingSubmitUrl}'>
                    {$text}
                </form>
                ";

                return $text;

            } else {
                unset($_SESSION['shippingDetails']);
                $_SESSION['cpReturnUrlAfterLogin'] = $_SERVER['REQUEST_URI'];
                $wLogin = getCPWidgetObj('member_loginForm');
                return $wLogin->getWidget(array(
                     'hasRegiserInfo' => $cpCfg['m.membership.allowRegistration']
                    ,'loginTypeArr' => array('pms_contact' => 'Individual', 'pms_company' => 'Company')
                    ,'loginType' => 'pms_contact'
                ));
            }
        } else {
            checkLoggedIn();

            $fnName = $fn->getFnNameByAction();
            $text = $contObj->$fnName();
            return $text;
        }
    }
}