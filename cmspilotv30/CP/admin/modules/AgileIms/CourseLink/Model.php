<?
class CP_Admin_Modules_AgileIms_CourseLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        if ($tv['srcRoom'] == 'agileIms_company'){
            $fa = $fn->addToFieldsArray($fa, 'company_id');
        }
        $fa = $fn->addToFieldsArray($fa, 'course_id');
        $fa = $fn->addToFieldsArray($fa, 'batch_id');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');

        return $fa;
    }

    /**
    */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['contact_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa);
    }

    /**
    */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }

    /**
    */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->validateData('course_id', 'Please select the course');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
    */
    function getAddCoursePvtLinkValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->validateData('registration_type', 'Please choose the registration type');
        $validate->validateData('course_type', 'Please select the course type');
        $validate->validateData('course_id', 'Please select the course');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
    */
    function getEditCoursePvtLinkValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->validateData('registration_type', 'Please choose the registration type');
        $validate->validateData('course_type', 'Please select the course type');
        $validate->validateData('course_id', 'Please select the course');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
    */
    function getNewValidateForSubsidy($subsidy_discount_id) {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $validate->resetErrorArray();
        $validate->validateData('course_id', 'Please select the course');
        if($subsidy_discount_id){
            $validate->validateData('subsidy_code', 'Please enter subsidy code');
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
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
      ********************************* PROCESS ************************************
      ACTION: IN STUDENT MODULE - WHEN YOU SUBMIT AN ENROLLMENT
      STEP 1: ADDING ENROLLMENT RECORD IN COURSE CONTACT TABLE
      STEP 2: UPDAING BATCH STATUS TO CLOSED IF MAXIMUM COUNT FOR THE BATCH IS REACHED
      STEP 3: CREATING ORDER RECORD FOR THE ENROLLMENT
      STEP 4: CREATING COURSE RECEORD IN ORDER ITEM FOR THE ENROLLMENT (For course, subsidy or discount and Reg fee, individual order item receords will be created)
      STEP 5: INSERTING SUBSIDY CODE IN SUBSIDY HISTORY TABLE
      STEP 6: CREATING SUBSIDY RECEORD IN ORDER ITEM TABLE FOR THE ENROLLMENT
      STEP 7: CREATING DISCOUNT RECEORD IN ORDER ITEM TABLE FOR THE ENROLLMENT
      STEP 8: CREATING REGISTRATION FEE RECEORD IN ORDER ITEM TABLE FOR THE ENROLLMENT
      STEP 9: UPDATING SUBSIDY PAID HISTORY ID IN COURSE CONTACT TABLE
      STEP 10: CREATING INVOICE AND INVOICE ITEM RECORDS FOR THE ENROLLMENT
      STEP 11: CREATING RECEIPT AND INVOICE RECEIPT HISTORY RECEORDS FOR THE ENROLLMENT
      ******************************* END PROCESS **********************************
    */
    function getAdd() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $subsidy_discount_id        = $fn->getPostParam('subsidy_discount_id');
        $subsidy_code               = $fn->getPostParam('subsidy_code');
        $batch_id                   = $fn->getPostParam('batch_id');
        $is_citizen                 = $fn->getPostParam('is_citizen');
        $add_registration_fee       = $fn->getPostParam('add_registration_fee');
        $auto_generate_invoice      = $fn->getPostParam('auto_generate_invoice');
        $auto_generate_receipt      = $fn->getPostParam('auto_generate_receipt');
        $discount                   = $fn->getPostParam('discount');
        $order_date                 = $fn->getPostParam('order_date');
        
        $subsidyTotal = '';
        $discTotal    = '';

        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        if ($is_citizen == 1 && !$this->getNewValidateForSubsidy($subsidy_discount_id)) {
            return $validate->getErrorMessageXML();
        }

        /********************************** STEP 1 **************************************/
        $fa                         = $this->getFields();
        $fa['add_registration_fee'] = $add_registration_fee;

        if ($subsidy_discount_id != '') {
            $fa['subsidy_discount_type'] = 'Subsidy';
            $fa['subsidy_discount_id']   = $subsidy_discount_id;
        } else if ($discount != '') {
            $fa['subsidy_discount_type'] = 'Discount';
            $fa['subsidy_discount_id']   = $discount;
        }

        $id                         = $fn->addRecord($fa);
        /********************************** STEP 1 ENDS HERE ****************************/
        /********************************** STEP 2 **************************************/
        if ($batch_id) {
            $SQLCC = "
            SELECT COUNT(course_contact_id) AS actual_count
            FROM course_contact
            WHERE batch_id = {$batch_id}
            ";
            $resultCC  = $db->sql_query($SQLCC);
            $numRows   = $db->sql_numrows($resultCC);
            $rowCC     = $db->sql_fetchrow($resultCC);

            $batchRec = $fn->getRecordRowByID('batch', 'batch_id', $batch_id);

            /* Updating batch status to CLOSED if it has reached maximum count */
            if($rowCC['actual_count'] == $batchRec['max_enroll_count']){
                $sqlUpdate = "
                UPDATE batch
                SET status = 'Closed'
                WHERE batch_id = {$batch_id}
                ";
                $resultUpdate = $db->sql_query($sqlUpdate);
            }
        }
        /********************************** STEP 2 ENDS HERE ****************************/

        $subsidy_paid_history_id = '';
        $course_contact_id       = $id ;

        if ($fa['course_id'] > 0) {
            // to check invoice raised or not;
            // To check in course_contact - order_id has value or not.
            $ccRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);

            if ($ccRec['order_id'] == ''){
                /********************************** STEP 3 **************************************/
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $ccRec['course_id']);
                $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $ccRec['contact_id']);

                $fa = array();
                $fa['contact_id']               = $ccRec['contact_id'];
                $fa['payment_method']           = '';
                $fa['module']                   = 'agileIms_course';
                $fa['order_status']             = 'Due';
                $fa['order_date']               = $order_date;
                $fa['contact_module']           = 'agileIms_contact';
                $fa['cust_first_name']          = $contactRec['first_name'];
                $fa['cust_email']               = $contactRec['email'];
                $fa['cust_phone']               = $contactRec['phone'];
                $fa['cust_address1']            = $contactRec['address_flat'];
                $fa['cust_address2']            = $contactRec['address_street'];
                $fa['cust_address_po_code']     = $contactRec['address_po_code'];
                $fa['cust_address_country_code']= $contactRec['address_country'];
                $fa['contact_reg_no']           = $contactRec['registration_no'];
                $fa['add_registration_fee']     = $add_registration_fee;
                $order_id = $fn->addRecord($fa, 'order');
                /********************************** STEP 3 ENDS HERE ****************************/
                /********************************** STEP 4 **************************************/
                $fa = array();
                $fa['order_id']          = $order_id;
                $fa['contact_id']        = $ccRec['contact_id'];
                $fa['module']            = 'agileIms_course';
                $fa['record_id']         = $ccRec['course_id'];
                $fa['qty']               = 1;
                $fa['item_title']        = $courseRec['title'];
                $fa['unit_price']        = $courseRec['price'];
                $fa['course_start_date'] = $courseRec['valid_date_from'];
                $fa['course_end_date']   = $courseRec['valid_date_to'];
                $fa['course_code']       = $courseRec['course_code'];
                $fn->addRecord($fa, 'order_item');
                /********************************** STEP 4 ENDS HERE ****************************/
                if ($ccRec['subsidy_discount_type'] == 'Subsidy') {
                    /********************************** STEP 5 **************************************/
                    if ($subsidy_code) {
                        $fa = array();
                        $fa['order_id']         = $order_id;
                        $fa['subsidy_code']     = $subsidy_code;
                        $fa['status']           = 'Due';
                        $fa['creation_date']    = date("Y-m-d H:i:s");
                        $subsidy_paid_history_id = $fn->addRecord($fa, 'subsidy_paid_history');
                    }
                    /********************************** STEP 5 ENDS HERE ****************************/
                    /********************************** STEP 6 **************************************/
                    $sqlSubsidy = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    WHERE sd.subsidy_discount_id = {$ccRec['subsidy_discount_id']}
                    ";
                    $resultSubsidy  = $db->sql_query($sqlSubsidy);
                    $rowSubsidy     = $db->sql_fetchrow($resultSubsidy);

                    if ($rowSubsidy['mode_of_calculation'] == 'Value') {
                        $subsidyTotal = $rowSubsidy['value'];
                    } else {
                        $subsidyTotal = ($courseRec['price']*$rowSubsidy['value'])/100;
                    }

                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'agileIms_subsidy';
                    $fa['record_id']  = $ccRec['course_id'];
                    $fa['qty']        = 1;
                    $fa['item_title'] = $rowSubsidy['title'];
                    $fa['unit_price'] = -$subsidyTotal;
                    $fa['contact_id'] = $ccRec['contact_id'];
                    $fn->addRecord($fa, 'order_item');
                    /********************************** STEP 6 ENDS HERE ****************************/
                }
                /********************************** STEP 7 **************************************/
                if ($ccRec['subsidy_discount_type'] == 'Discount') {
                    $sqlDiscount = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    WHERE sd.subsidy_discount_id = {$ccRec['subsidy_discount_id']}
                    ";
                    $resultDiscount  = $db->sql_query($sqlDiscount);
                    $rowDiscount     = $db->sql_fetchrow($resultDiscount);

                    if ($rowDiscount['mode_of_calculation'] == 'Value') {
                        $discTotal = $rowDiscount['value'];
                    } else {
                        $discTotal = ($courseRec['price']*$rowDiscount['value'])/100;
                    }

                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'agileIms_discount';
                    $fa['record_id']  = $ccRec['course_id'];
                    $fa['qty']        = 1;
                    $fa['item_title'] = $rowDiscount['title'];
                    $fa['unit_price'] = -$discTotal;
                    $fa['contact_id'] = $ccRec['contact_id'];
                    $fn->addRecord($fa, 'order_item');
                }
                /********************************** STEP 7 ENDS HERE ****************************/
                /********************************** STEP 8 **************************************/
                if ($ccRec['add_registration_fee'] > 0) {
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'agileIms_registration';
                    $fa['record_id']  = $ccRec['course_id'];
                    $fa['qty']        = 1;
                    $fa['item_title'] = 'Registration Fee';
                    $fa['unit_price'] = $fn->getSettingsValueByKey("registrationFee");
                    $fa['contact_id'] = $ccRec['contact_id'];
                    $fn->addRecord($fa, 'order_item');
                }
                /********************************** STEP 8 ENDS HERE ****************************/
                /********************************** STEP 9 **************************************/
                $cshRec = $fn->getRecordRowByID('subsidy_discount', 'subsidy_discount_id', $discount);
                $fa = array();
                $fa['order_id']   = $order_id;
                $fa['discount']   = $cshRec['subsidy_discount_id'];
                $fa['subsidy_paid_history_id'] = $subsidy_paid_history_id;
                $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id );
                /********************************** STEP 9 ENDS HERE ****************************/
            }
        }

        /********************************** STEP 10 **************************************/
        if ($auto_generate_invoice == 1) {
            /* Total amount from Order Item */
            $modObj = getCPModuleObj('agileIms_order');
            $total_amount_payable = $modObj->model->getTotalAmountFromOrderItem($order_id);

            $modObj = getCPModuleObj('agileIms_invoice');
            $invoice_code = $modObj->model->getFetchInvoiceCode();

            /* Creating a new invoice */
            $faInv = array();
            $faInv['invoice_date']     = date('Y-m-d');
            $faInv['invoice_due_date'] = date('Y-m-d', strtotime("+7 days"));
            $faInv['status']           = 'Due';
            $faInv['invoice_amount']   = $total_amount_payable;
            $faInv['created_by']       = $fn->getSessionParam('userName');
            $faInv['creation_date']    = date('Y-m-d H:i:s');
            $faInv['invoice_code']     = $invoice_code;
            $faInv['inv_currency']     = 'SGD';
            $faInv['order_id']         = $order_id;
            $faInv['cust_first_name']  = $contactRec['first_name'];
            $faInv['contact_reg_no']   = $contactRec['registration_no'];
            $invoice_id                = $fn->addRecord($faInv, 'invoice');

            /* Increment of Invoice Code */
            $SQLUpdate    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
            $resultUpdate = $db->sql_query($SQLUpdate);

            /* Creating a invoice item records */
            $sqlOi = "
            SELECT * FROM order_item
            WHERE order_id = {$order_id}
            ";
            $resultOi  = $db->sql_query($sqlOi);
            while ($rowOi = $db->sql_fetchrow($resultOi)) {
                $faInvItem = array();
                $faInvItem['record_id']         = $rowOi['record_id'];
                $faInvItem['qty']               = $rowOi['qty'];
                $faInvItem['unit_price']        = $rowOi['unit_price'];
                $faInvItem['item_title']        = $rowOi['item_title'];
                $faInvItem['module']            = $rowOi['module'];
                $faInvItem['contact_id']        = $rowOi['contact_id'];
                $faInvItem['subsidy_paid']      = $rowOi['subsidy_paid'];
                $faInvItem['invoice_id']        = $invoice_id;
                $faInvItem['course_start_date'] = $rowOi['course_start_date'];
                $faInvItem['course_end_date']   = $rowOi['course_end_date'];
                $faInvItem['course_code']       = $rowOi['course_code'];
                $invoice_item_id                = $fn->addRecord($faInvItem, 'invoice_item');

                $faOi = array();
                $faOi['invoice_id'] = $invoice_id;
                $fn->saveRecord($faOi, 'order_item', 'order_item_id', $rowOi['order_item_id']);
            }

        }
        /********************************** STEP 10 ENDS HERE ****************************/

        /********************************** STEP 11 **************************************/
        if ($auto_generate_invoice == 1 && $auto_generate_receipt == 1) {
            $modObj = getCPModuleObj('agileIms_receipt');
            $receipt_code = $modObj->model->getFetchReceiptCode();

            /* Creating a new receipt */
            $faRec = array();
            $faRec['receipt_code']     = $receipt_code;
            $faRec['date']             = date('Y-m-d');
            $faRec['amount']           = $total_amount_payable;
            $faRec['mode_of_payment']  = 'Cash';
            $faRec['creation_date']    = date('Y-m-d H:i:s');
            $faRec['created_by']       = $fn->getSessionParam('userName');
            $faRec['issued_by']        = $fn->getSessionParam('userName');
            $faRec['order_id']         = $order_id;
            $faRec['receipt_status']   = 'Paid';
            $receipt_id                = $fn->addRecord($faRec, 'receipt');

            /* Increment of Receipt Code */
            $SQLUpdate    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
            $resultUpdate = $db->sql_query($SQLUpdate);

            /* Creating a invoice receipt history record */
            $faInvRecHist = array();
            $faInvRecHist['invoice_id']          = $invoice_id;
            $faInvRecHist['receipt_id']          = $receipt_id;
            $faInvRecHist['creation_date']       = date('Y-m-d H:i:s');
            $faInvRecHist['created_by']          = $fn->getSessionParam('userName');
            $faInvRecHist['amount']              = $total_amount_payable;
            $inve_rec_hist_id                    = $fn->addRecord($faInvRecHist, 'invoice_receipt_history');

            $faInv = array();
            $faInv['status']            = 'Paid';
            $faInv['modification_date'] = date('Y-m-d H:i:s');
            $faInv['modified_by']       = $fn->getSessionParam('userName');
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

            $faOrd = array();
            $faOrd['order_status'] = 'Paid';
            $fn->saveRecord($faOrd, 'order', 'order_id', $order_id);
        }
        /********************************** STEP 11 ENDS HERE ****************************/

        return $validate->getSuccessMessageXML();
    }

    /**
      ********************************* PROCESS ************************************
      ACTION: IN STUDENT MODULE - WHEN YOU EDIT AND SUBMIT AN ENROLLMENT
      STEP 1: UPDATING BATCH STATUS
      STEP 2: UPDATING ENROLLMENT RECORD IN COURSE CONTACT TABLE AND ORDER DATE IN ORDER TABLE
      STEP 3: FINDING WHETHER A DISCOUNT IS ALREADY SAVED IN ORDER ITEM TABLE.
              IF YES, UPDATE THE DISCOUNT AMOUNT ELSE CREATE A NEW ORDER ITEM RECORD FOR THE DISCOUNT.
      STEP 4: FINDING WHETHER SUBSIDY IS ALREADY SAVED IN ORDER ITEM TABLE.
              IF YES, UPDATE SUBSIDY AMOUNT ELSE CREATE A NEW ORDER ITEM RECORD FOR SUBSIDY.
      STEP 5: UPDATING SUBSIDY CODE IN SUBSIDY PAID HISTORY TABLE
      STEP 6: CHECKING WHETHER INVOICE IS ALREADY GENERATED .IF YES, CANCEL THE INVOICE.
      STEP 7: CREATE INVOICE AND INVOICE ITEM RECORDS AND UPDATE NEW INVOICE ID IN ORDER ITEM TABLE
      STEP 8: AUTO GENERATION OF RECEIPT
      ******************************* END PROCESS **********************************
    */
    function getSave() {
        $fn       = Zend_Registry::get('fn');
        $tv       = Zend_Registry::get('tv');
        $db       = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        
        $subsidy_discount_id   = $fn->getPostParam('subsidy_discount_id');
        $subsidy_code          = $fn->getPostParam('subsidy_code');
        $order_date            = $fn->getPostParam('order_date');
        $batch_id              = $fn->getPostParam('batch_id');
        $discount              = $fn->getPostParam('discount');
        $course_contact_id     = $fn->getReqParam('course_contact_id');
        $auto_generate_receipt = $fn->getPostParam('auto_generate_receipt');

        $cCRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);

        if (!$this->getEditValidate()) {
            return $validate->getErrorMessageXML();
        }

        /********************************** STEP 1 **************************************/
        if ($batch_id != $cCRec['batch_id']) {
            $SQLCC = "
            SELECT COUNT(course_contact_id) AS actual_count
            FROM course_contact
            WHERE batch_id = {$batch_id}
            ";
            $resultCC  = $db->sql_query($SQLCC);
            $numRows   = $db->sql_numrows($resultCC);
            $rowCC     = $db->sql_fetchrow($resultCC);

            $batchRec = $fn->getRecordRowByID('batch', 'batch_id', $batch_id);

            /* Adding 1 value to course contact as there is a change in no of count in enrollment */
            $total_batch_rec_including_this_enrollment = $rowCC['actual_count'] + 1;

            /* Updating batch status to CLOSED if it has reached maximum count */
            if($total_batch_rec_including_this_enrollment == $batchRec['max_enroll_count']){
                $sqlUpdate = "
                UPDATE batch
                SET status = 'Closed'
                WHERE batch_id = {$batch_id}
                ";
                $resultUpdate = $db->sql_query($sqlUpdate);
            } else {
                $sqlUpdate = "
                UPDATE batch
                SET status = 'Open'
                WHERE batch_id = {$cCRec['batch_id']}
                ";
                $resultUpdate = $db->sql_query($sqlUpdate);
            }
        }
        /********************************** STEP 1 ENDS HERE ****************************/
        /********************************** STEP 2 **************************************/
        $faCc = array();
        $faCc['batch_id']            = $batch_id;

        if ($cCRec['subsidy_discount_type'] == 'Discount' || $discount != '') {
            $faCc['subsidy_discount_type'] = 'Discount';
            $faCc['subsidy_discount_id']   = $discount;
        } else {
            $faCc['subsidy_discount_type'] = 'Subsidy';
            $faCc['subsidy_discount_id']   = $subsidy_discount_id;
        }
        $fn->saveRecord($faCc, 'course_contact', 'course_contact_id', $course_contact_id);

        $courseContactRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);        
        $orderRec         = $fn->getRecordRowByID('order', 'order_id', $courseContactRec['order_id']);
        if ($orderRec['order_date'] != $order_date) {
            $faOrder = array();
            $faOrder['order_date']    = $order_date;
            $faOrder['created_by']    = $fn->getSessionParam('userName');
            $faOrder['creation_date'] = date('Y-m-d H:i:s');
            $fn->saveRecord($faOrder, 'order', 'order_id', $orderRec['order_id']);
        }
        /********************************** STEP 2 ENDS HERE ****************************/
        /********************************** STEP 3 **************************************/
        if ($discount != '' && $cCRec['subsidy_discount_id'] != $courseContactRec['subsidy_discount_id']) {
            $recCount = $fn->getRecordCount('order_item', "order_id = '{$courseContactRec['order_id']}' AND module = 'agileIms_discount'");
            
            $rowDiscount = $fn->getRecordRowByID('subsidy_discount', 'subsidy_discount_id', $discount);
            $courseRec   = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);
            
            if ($rowDiscount['mode_of_calculation'] == 'Value') {
                $discTotal = $rowDiscount['value'];
            } else {
                $discTotal = ($courseRec['price']*$rowDiscount['value'])/100;
            }
            
            $discount_amt = '-' . $discTotal;

            if ($recCount) {
                $sqlOiUpdate = "
                UPDATE `order_item`
                SET unit_price = {$discount_amt}
                   ,item_title = '{$rowDiscount['title']}'
                WHERE order_id = {$courseContactRec['order_id']}
                  AND module = 'agileIms_discount'
                  AND contact_id = {$courseContactRec['contact_id']}
                ";
                $resultOiUpdate = $db->sql_query($sqlOiUpdate);
            } else {
                $faOi = array();
                $faOi['order_id']   = $courseContactRec['order_id'];
                $faOi['module']     = 'agileIms_discount';
                $faOi['record_id']  = $courseContactRec['course_id'];
                $faOi['qty']        = 1;
                $faOi['item_title'] = $rowDiscount['title'];
                $faOi['unit_price'] = $discount_amt;
                $faOi['contact_id'] = $courseContactRec['contact_id'];
                $fn->addRecord($faOi, 'order_item');
            }
        } else if ($discount == '' && $cCRec['subsidy_discount_id'] != '') {
            /* Deleting Order item record for discount if first time discount is given and now discount is not given */
            $sqlOiDelete = "
            DELETE FROM `order_item`
            WHERE order_id = {$courseContactRec['order_id']}
              AND module = 'agileIms_discount'
              AND contact_id = {$courseContactRec['contact_id']}
            ";
            $resultOiDelete = $db->sql_query($sqlOiDelete);
        } else if ($discount != '' && $cCRec['subsidy_discount_id'] == '') {
            $rowDiscount = $fn->getRecordRowByID('subsidy_discount', 'subsidy_discount_id', $discount);
            $courseRec   = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);
            
            if ($rowDiscount['mode_of_calculation'] == 'Value') {
                $discTotal = $rowDiscount['value'];
            } else {
                $discTotal = ($courseRec['price']*$rowDiscount['value'])/100;
            }
            
            $discount_amt = '-' . $discTotal;

            /* Creating Order item record for discount if first time discount is not given and now discount is given */
            $faOi = array();
            $faOi['order_id']   = $courseContactRec['order_id'];
            $faOi['module']     = 'agileIms_discount';
            $faOi['record_id']  = $courseContactRec['course_id'];
            $faOi['qty']        = 1;
            $faOi['item_title'] = $rowDiscount['title'];
            $faOi['unit_price'] = $discount_amt;
            $faOi['contact_id'] = $courseContactRec['contact_id'];
            $fn->addRecord($faOi, 'order_item');
        }
        /********************************** STEP 3 ENDS HERE ****************************/
        /********************************** STEP 4 **************************************/
        if ($subsidy_discount_id != '' && $cCRec['subsidy_discount_id'] != $courseContactRec['subsidy_discount_id']) {
            $recCount = $fn->getRecordCount('order_item', "order_id = '{$courseContactRec['order_id']}' AND module = 'agileIms_subsidy'");
            
            $rowSubsidy  = $fn->getRecordRowByID('subsidy_discount', 'subsidy_discount_id', $subsidy_discount_id);
            $courseRec   = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);
            
            if ($rowSubsidy['mode_of_calculation'] == 'Value') {
                $subsidyTotal = $rowSubsidy['value'];
            } else {
                $subsidyTotal = ($courseRec['price']*$rowSubsidy['value'])/100;
            }
            
            $subsidy_amt = '-' . $subsidyTotal;

            if ($recCount) {
                $sqlOiUpdate = "
                UPDATE `order_item`
                SET unit_price = {$subsidy_amt}
                   ,item_title = '{$rowSubsidy['title']}'
                WHERE order_id = {$courseContactRec['order_id']}
                  AND module = 'agileIms_subsidy'
                  AND contact_id = {$courseContactRec['contact_id']}
                ";
                $resultOiUpdate = $db->sql_query($sqlOiUpdate);
            } else {
                $faOi = array();
                $faOi['order_id']   = $courseContactRec['order_id'];
                $faOi['module']     = 'agileIms_subsidy';
                $faOi['record_id']  = $courseContactRec['course_id'];
                $faOi['qty']        = 1;
                $faOi['item_title'] = $rowSubsidy['title'];
                $faOi['unit_price'] = $subsidy_amt;
                $faOi['contact_id'] = $courseContactRec['contact_id'];
                $fn->addRecord($faOi, 'order_item');
            }
        } else if ($subsidy_discount_id == '' && $cCRec['subsidy_discount_id'] != '') {
            /* Deleting Order item record for discount if first time discount is given and now discount is not given */
            $sqlOiDelete = "
            DELETE FROM `order_item`
            WHERE order_id = {$courseContactRec['order_id']}
              AND module = 'agileIms_subsidy'
              AND contact_id = {$courseContactRec['contact_id']}
            ";
            $resultOiDelete = $db->sql_query($sqlOiDelete);
        } else if ($subsidy_discount_id != '' && $cCRec['subsidy_discount_id'] == '') {

            $rowSubsidy  = $fn->getRecordRowByID('subsidy_discount', 'subsidy_discount_id', $subsidy_discount_id);
            $courseRec   = $fn->getRecordRowByID('course', 'course_id', $courseContactRec['course_id']);
            
            if ($rowSubsidy['mode_of_calculation'] == 'Value') {
                $subsidyTotal = $rowSubsidy['value'];
            } else {
                $subsidyTotal = ($courseRec['price']*$rowSubsidy['value'])/100;
            }
            
            $subsidy_amt = '-' . $subsidyTotal;
            /* Creating Order item record for subsidy if first time subsidy is not given and now subsidy is given */
            $faOi = array();
            $faOi['order_id']   = $courseContactRec['order_id'];
            $faOi['module']     = 'agileIms_subsidy';
            $faOi['record_id']  = $courseContactRec['course_id'];
            $faOi['qty']        = 1;
            $faOi['item_title'] = $rowSubsidy['title'];
            $faOi['unit_price'] = $subsidy_amt;
            $faOi['contact_id'] = $courseContactRec['contact_id'];
            $fn->addRecord($faOi, 'order_item');
        }
        /********************************** STEP 4 ENDS HERE ****************************/
        /********************************** STEP 5 **************************************/
        $rowSubsidyCode = $fn->getRecordRowByID('subsidy_paid_history', 'order_id', $courseContactRec['order_id']);
        if ($subsidy_code != $rowSubsidyCode['subsidy_code']) {
            $current_date = date('Y-m-d H:i:s');
            $sqlOiUpdate = "
            UPDATE subsidy_paid_history
            SET subsidy_code = '{$subsidy_code}'
                ,modified_by = '{$fn->getSessionParam('userName')}'
                ,modification_date = '{$current_date}'
            WHERE order_id = {$courseContactRec['order_id']}
            ";
            $resultOiUpdate = $db->sql_query($sqlOiUpdate);
        }
        /********************************** STEP 5 ENDS HERE ****************************/
        /********************************** STEP 6 **************************************/
        if ($cCRec['subsidy_discount_id'] != $courseContactRec['subsidy_discount_id']) {
            $sqlInv = "
            SELECT DISTINCT i.invoice_id FROM invoice i
            LEFT JOIN (`invoice_item` ii) ON (i.invoice_id = ii.invoice_id)
            WHERE ii.contact_id = {$courseContactRec['contact_id']}
              AND i.order_id = {$courseContactRec['order_id']}
            ";
            $resultInv  = $db->sql_query($sqlInv);
            $numRowsInv = $db->sql_numrows($resultInv);
            
            if ($numRowsInv) {
                /* Cancelling the Invoice */
                $current_date = date('Y-m-d H:i:s');
                $updateInv = "
                UPDATE invoice
                SET status = 'Cancelled'
                   ,modified_by = '{$fn->getSessionParam('userName')}'
                   ,modification_date = '{$current_date}'
                WHERE order_id = {$courseContactRec['order_id']}
                  AND status = 'Due'
                ";
                $resultInv = $db->sql_query($updateInv);
            } 
        }
        /********************************** STEP 6 ENDS HERE ****************************/
        $sqlI = "
        SELECT i.invoice_id FROM invoice i
        WHERE i.order_id = {$courseContactRec['order_id']}
          AND (i.status = 'Due' OR i.status = 'Paid')
        ";
        $resultI  = $db->sql_query($sqlI);
        $numRowsI = $db->sql_numrows($resultI);
        /********************************** STEP 7 **************************************/
        if ($numRowsI == 0 && 
                            (($cCRec['discount'] != $courseContactRec['discount']) || 
                            ($cCRec['subsidy_discount_id'] != $courseContactRec['subsidy_discount_id']) ||
                            ($subsidy_discount_id != '' || $discount != ''))
            ) {
            /* Total amount from Order Item */
            $modObj = getCPModuleObj('agileIms_order');
            $total_amount_payable = $modObj->model->getTotalAmountFromOrderItem($courseContactRec['order_id']);
    
            $modObj = getCPModuleObj('agileIms_invoice');
            $invoice_code = $modObj->model->getFetchInvoiceCode();

            $orderRec = $fn->getRecordRowByID('order', 'order_id', $courseContactRec['order_id']);
    
            /* Creating a new invoice */
            $faInv = array();
            $faInv['invoice_date']     = date('Y-m-d');
            $faInv['invoice_due_date'] = date('Y-m-d', strtotime("+7 days"));
            $faInv['status']           = 'Due';
            $faInv['invoice_amount']   = $total_amount_payable;
            $faInv['created_by']       = $fn->getSessionParam('userName');
            $faInv['creation_date']    = date('Y-m-d H:i:s');
            $faInv['invoice_code']     = $invoice_code;
            $faInv['inv_currency']     = 'SGD';
            $faInv['order_id']         = $courseContactRec['order_id'];
            $faInv['cust_first_name']  = $orderRec['cust_first_name'];
            $faInv['contact_reg_no']   = $orderRec['contact_reg_no'];
            $invoice_id                = $fn->addRecord($faInv, 'invoice');
    
            /* Increment of Invoice Code */
            $SQLUpdate    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
            $resultUpdate = $db->sql_query($SQLUpdate);
    
            /* Creating a invoice item records */
            $sqlOi = "
            SELECT * FROM order_item
            WHERE order_id = {$courseContactRec['order_id']}
            ";
            $resultOi  = $db->sql_query($sqlOi);
            while ($rowOi = $db->sql_fetchrow($resultOi)) {
                $faInvItem = array();
                $faInvItem['record_id']         = $rowOi['record_id'];
                $faInvItem['qty']               = $rowOi['qty'];
                $faInvItem['unit_price']        = $rowOi['unit_price'];
                $faInvItem['item_title']        = $rowOi['item_title'];
                $faInvItem['module']            = $rowOi['module'];
                $faInvItem['contact_id']        = $rowOi['contact_id'];
                $faInvItem['subsidy_paid']      = $rowOi['subsidy_paid'];
                $faInvItem['invoice_id']        = $invoice_id;
                $faInvItem['course_start_date'] = $rowOi['course_start_date'];
                $faInvItem['course_end_date']   = $rowOi['course_end_date'];
                $faInvItem['course_code']       = $rowOi['course_code'];
                $invoice_item_id                = $fn->addRecord($faInvItem, 'invoice_item');
    
                /* Updating Invoice Id to Order Item table */
                $faOi = array();
                $faOi['invoice_id'] = $invoice_id;
                $fn->saveRecord($faOi, 'order_item', 'order_item_id', $rowOi['order_item_id']);
            }
        }
        /********************************** STEP 7 ENDS HERE ****************************/
        $sqlR = "
        SELECT r.receipt_id FROM receipt r
        WHERE r.order_id = {$courseContactRec['order_id']}
          AND r.receipt_status = 'Paid'
        ";
        $resultR  = $db->sql_query($sqlR);
        $numRowsR = $db->sql_numrows($resultR);
        /********************************** STEP 8 **************************************/
        if ($numRowsR == 0 && $cCRec['discount'] == $courseContactRec['discount']) {
            $sqlInv = "
            SELECT invoice_id FROM invoice
            WHERE status = 'Due'
              AND order_id = {$courseContactRec['order_id']}
            ";
            $resultInv  = $db->sql_query($sqlInv);
            $rowInv     = $db->sql_fetchrow($resultInv);
            $invoice_id = $rowInv['invoice_id'];

            $sqlInvItem = "
            SELECT SUM(unit_price) AS total_amount_payable FROM invoice_item
            WHERE invoice_id = {$invoice_id}
            ";
            $resultInvItem = $db->sql_query($sqlInvItem);
            $rowInvItem    = $db->sql_fetchrow($resultInvItem);
            $total_amount_payable = $rowInvItem['total_amount_payable'];
        }
        
        if ($numRowsR == 0 && $auto_generate_receipt == 1) {
            $modObj = getCPModuleObj('agileIms_receipt');
            $receipt_code = $modObj->model->getFetchReceiptCode();

            /* Creating a new receipt */
            $faRec = array();
            $faRec['receipt_code']     = $receipt_code;
            $faRec['date']             = date('Y-m-d');
            $faRec['amount']           = $total_amount_payable;
            $faRec['mode_of_payment']  = 'Cash';
            $faRec['creation_date']    = date('Y-m-d H:i:s');
            $faRec['created_by']       = $fn->getSessionParam('userName');
            $faRec['issued_by']        = $fn->getSessionParam('userName');
            $faRec['order_id']         = $courseContactRec['order_id'];
            $faRec['receipt_status']   = 'Paid';
            $receipt_id                = $fn->addRecord($faRec, 'receipt');

            /* Increment of Receipt Code */
            $SQLUpdate    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
            $resultUpdate = $db->sql_query($SQLUpdate);

            /* Creating a invoice receipt history record */
            $faInvRecHist = array();
            $faInvRecHist['invoice_id']          = $invoice_id;
            $faInvRecHist['receipt_id']          = $receipt_id;
            $faInvRecHist['creation_date']       = date('Y-m-d H:i:s');
            $faInvRecHist['created_by']          = $fn->getSessionParam('userName');
            $faInvRecHist['amount']              = $total_amount_payable;
            $inve_rec_hist_id                    = $fn->addRecord($faInvRecHist, 'invoice_receipt_history');

            /* Updating Invoice receord to Paid */
            $faInv = array();
            $faInv['status']            = 'Paid';
            $faInv['modification_date'] = date('Y-m-d H:i:s');
            $faInv['modified_by']       = $fn->getSessionParam('userName');
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

            /* Updating Order receord to Paid */
            $faOrd = array();
            $faOrd['order_status'] = 'Paid';
            $fn->saveRecord($faOrd, 'order', 'order_id', $courseContactRec['order_id']);
        }
        /********************************** STEP 8 ENDS HERE ****************************/
        return $validate->getSuccessMessageXML();
    }

    /**
    */
    function getFieldsCoursePvtLink(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'course_id');
        $fa = $fn->addToFieldsArray($fa, 'batch_id');
        $fa = $fn->addToFieldsArray($fa, 'discount');
        $fa = $fn->addToFieldsArray($fa, 'contact_id');

        return $fa;
    }

    /**
    */
    function getAddCoursePvtLink() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        if (!$this->getAddCoursePvtLinkValidate()){
            return $validate->getErrorMessageXML();
        }

        $registration_type = $fn->getPostParam('registration_type');
        $installment       = $fn->getPostParam('installment');
        $medical_insurance = $fn->getPostParam('medical_insurance');
        $contract_no       = $fn->getPostParam('contract_no');
        $subject_id_arr    = $_SESSION['selectedSubjectIds'];
        $full_time         = $fn->getPostParam('full_time');
        $no_of_months      = $fn->getPostParam('no_of_months');
        $batch_id          = $fn->getPostParam('batch_id');
        $add_registration_fee = $fn->getPostParam('add_registration_fee');

        //To get the total number of subject
        $count             = count($_SESSION['selectedSubjectIds']);

        $fa = $this->getFieldsCoursePvtLink();
        $fa['registration_type']    = $registration_type;
        $fa['medical_insurance']    = $medical_insurance;
        $fa['add_registration_fee'] = $add_registration_fee;
        $fa['contract_no']          = $contract_no;
        $fa['full_time']            = $full_time;
        $fa['no_of_months']         = $no_of_months;

        if ($fa['course_id'] > 0){
            $course_contact_id = $fn->addRecord($fa);
            $ccRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);

            if ($ccRec['order_id'] == ''){
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $ccRec['course_id']);
                $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $ccRec['contact_id']);

                //To create new order for this enrollment
                $order_id = $this->getCreateOrderForEnrollment($ccRec, $installment, $registration_type, $medical_insurance, $add_registration_fee, $full_time);

                //To create new order item record for this enrollment ( for course)
                $fa = array();
                $fa['order_id']   = $order_id;
                $fa['contact_id'] = $ccRec['contact_id'];
                $fa['module']     = 'agileIms_course';
                $fa['record_id']  = $ccRec['course_id'];
                $fa['qty']        = 1;
                $fa['item_title'] = $courseRec['title'];
                $fa['unit_price'] = $courseRec['price'];
                $fn->addRecord($fa, 'order_item');

                //To create new order item record for the subjects selected
                if ($count != ''){
                    $selectedSubjectIds = join(',', $_SESSION['selectedSubjectIds']);
                    $SQLSubject  = "
                    SELECT s.*
                    FROM subject s
                    WHERE s.subject_id IN ({$selectedSubjectIds})
                    ";
                    $resultSubject  = $db->sql_query($SQLSubject);
                    $numRows = $db->sql_numrows($resultSubject);
                    $subjectTotal = '';
                    while ($rowSubject = $db->sql_fetchrow($resultSubject)) {
                        /*
                        if($full_time == 1){
                            $subjectTotal = $rowSubject['fees'];
                        }
                        else{
                            //$subjectTotal = $rowSubject['fees'] - 255;
                            $subjectTotal = 1125;
                         }
                         */
                        $subjectTotal = $rowSubject['fees'];

                        $fa = array();
                        $fa['order_id']   = $order_id;
                        $fa['module']     = 'agileIms_subject';
                        $fa['record_id']  = $ccRec['course_id'];
                        $fa['qty']        = 1;
                        $fa['item_title'] = $rowSubject['title'];
                        $fa['unit_price'] = $subjectTotal;
                        $fa['contact_id'] = $ccRec['contact_id'];
                        $fn->addRecord($fa, 'order_item');

                        $fa = array();
                        $fa['course_contact_id'] = $course_contact_id;
                        $fa['subject_id']        = $rowSubject['subject_id'];
                        $fn->addRecord($fa, 'course_contact_subject_history');
                    }
                }

                //To create new order item record for the discount
                if ($ccRec['discount'] > 0){
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'agileIms_discount';
                    $fa['record_id']  = $ccRec['course_id'];
                    $fa['qty']        = 1;
                    $fa['item_title'] = 'Discount';
                    $fa['unit_price'] = $ccRec['discount'];
                    $fa['contact_id'] = $ccRec['contact_id'];
                    $fn->addRecord($fa, 'order_item');
                }

                $fa = array();
                $fa['order_id']   = $order_id;
                if($registration_type == 'Registration & Enrollment' &&
                $courseRec['course_type'] == 'Long Term'){
                    $fa['course_status'] = 'Active';
                }
                $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id );
                //To create new order item record for the discount
                if ($batch_id > 0){
                    $fa = array();
                    $fa['batch_id']   = $batch_id;
                    $fa['contact_id'] = $ccRec['contact_id'];
                    $fn->addRecord($fa, 'batch_history');
                }
            }
        }
        return $validate->getSuccessMessageXML();
    }

    /**
    */
    function getCreateOrderForEnrollment($ccRec, $installment, $registration_type, $medical_insurance, $add_registration_fee, $full_time){
        $fn = Zend_Registry::get('fn');

        $courseRec  = $fn->getRecordRowByID('course', 'course_id', $ccRec['course_id']);
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $ccRec['contact_id']);

        $fa = array();
        $fa['contact_id']      = $ccRec['contact_id'];
        $fa['payment_method']  = '';
        $fa['module']          = 'agileIms_course';
        $fa['order_status']    = 'Due';
        $fa['no_of_installment']= $installment;
        $fa['registration_type']= $registration_type;
        $fa['medical_insurance']= $medical_insurance;
        $fa['add_registration_fee']= $add_registration_fee;
        $fa['order_date']      =  date('Y-m-d');
        $fa['contact_module']  = 'agileIms_contact';
        $fa['full_time']       =  $full_time;

        $fa['cust_first_name']          = $contactRec['first_name'];
        $fa['cust_last_name']           = $contactRec['last_name'];
        $fa['cust_email']               = $contactRec['email'];
        $fa['cust_phone']               = $contactRec['phone'];
        $fa['cust_address1']            = $contactRec['address_flat'];
        $fa['cust_address2']            = $contactRec['address_street'];
        $fa['cust_address_area']        = $contactRec['address_area'];
        $fa['cust_address_city']        = $contactRec['address_city'];
        $fa['cust_address_state']       = $contactRec['address_state'];
        $fa['cust_address_po_code']     = $contactRec['address_po_code'];
        $fa['cust_address_country_code']= $contactRec['address_country'];

        $order_id = $fn->addRecord($fa, 'order');
        return $order_id;
    }

    /**
    */
    function getSaveCoursePvtLink() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        if (!$this->getEditCoursePvtLinkValidate()){
            return $validate->getErrorMessageXML();
        }

        $countSubjectArray = '';
        $newCourseRecord   = '';

        $course_contact_id  = $fn->getPostParam('course_contact_id', '', true);
        $batch_id           = $fn->getPostParam('batch_id', '', true);
        $discount           = $fn->getPostParam('discount', '', true);
        $registration_type  = $fn->getPostParam('registration_type');
        $installment        = $fn->getPostParam('installment');
        $course_status      = $fn->getPostParam('course_status');
        $course_id          = $fn->getPostParam('course_id');
        $medical_insurance  = $fn->getPostParam('medical_insurance');
        $add_registration_fee = $fn->getPostParam('add_registration_fee');
        $contract_no        = $fn->getPostParam('contract_no');
        $full_time          = $fn->getPostParam('full_time');
        $no_of_months       = $fn->getPostParam('no_of_months');

        if (isset($_SESSION['selectedSubjectIds'])){
            $countSubjectArray = count($_SESSION['selectedSubjectIds']);
        }

        // To check if the course is changed in edit.
        $existingCCRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);

        // if the course is changed delete all the subjects related to the previous course
        if($existingCCRec['course_id'] != $course_id){
            $newCourseRecord = 1;
            //if subject array is empty then below will delete the subjects from the history table
            $DeleteSQL  = "
                DELETE FROM course_contact_subject_history
                    WHERE course_contact_id = {$course_contact_id}
            ";
            $resultSubject  = $db->sql_query($DeleteSQL);

            //to delete the subjects from the order item table when subjectarray is empty
            $DeleteSQL  = "
                DELETE FROM order_item
                    WHERE order_id = {$existingCCRec['order_id']}
                         AND module = 'agileIms_subject'
            ";
            $resultSubject  = $db->sql_query($DeleteSQL);

            //to save the changed course details in order_item
            $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
            $expOrderItemCourse = array('condn' => " AND module = 'agileIms_course'");
            $orderItemRecCourse = $fn->getRecordRowByID('order_item', 'order_id', $existingCCRec['order_id'], $expOrderItemCourse);
            $fa = array();
            $fa['record_id']  = $course_id;
            $fa['qty']        = 1;
            $fa['item_title'] = $courseRec['title'];
            $fa['unit_price'] = $courseRec['price'];
            $fn->saveRecord($fa, 'order_item', 'order_item_id',
            $orderItemRecCourse['order_item_id']);
        }

        //Update course_cotnact history table for the related fields
        $fa = $this->getFieldsCoursePvtLink();
        $fa['registration_type'] = $registration_type;
        $fa['course_status']     = $course_status;
        $fa['contract_no']       = $contract_no;
        $fa['full_time']         = $full_time;
        $fa['no_of_months']      = $no_of_months;

        $fa['course_termination_date'] = '';
        $fa['remarks'] = '';
        if ($course_status == 'Expelled' || $course_status == 'Terminated' || $course_status == 'Withdrawal') {
            $fa['course_termination_date'] = $fn->getReqParam('course_termination_date');
            $fa['remarks'] = $fn->getReqParam('remarks');
        }

        $fa['medical_insurance'] = $medical_insurance;
        $fa['add_registration_fee'] = $add_registration_fee;
        //update all values in course contact table
        $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id );
        $ccRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);

        //Update order table for the related fields
        $fa = array();
        $fa['no_of_installment']= $installment;
        $fa['registration_type']= $registration_type;
        $fa['medical_insurance'] = $medical_insurance;
        $fa['add_registration_fee'] = $add_registration_fee;
        $fa['add_registration_fee'] = $add_registration_fee;
        $fa['full_time']            = $full_time;
        $fn->saveRecord($fa, 'order', 'order_id', $ccRec['order_id'] );

        if(is_array($ccRec) && $ccRec['order_id'] != ''){
            $expOrderItemSubsidy = array('condn' => " AND module = 'agileIms_subsidy'");
            $orderItemRecSubsidy = $fn->getRecordRowByID('order_item', 'order_id', $ccRec['order_id'], $expOrderItemSubsidy);

            //To create new order item record/subject history record for the subjects selected
            if ($countSubjectArray != ''){
                $selectedSubjectIds = join(',', $_SESSION['selectedSubjectIds']);
                $SQLSubject  = "
                SELECT s.*
                FROM subject s
                WHERE s.subject_id IN ({$selectedSubjectIds})
                AND s.subject_id NOT IN(
                    SELECT crssubj.subject_id
                    FROM course_contact_subject_history crssubj
                        WHERE crssubj.course_contact_id = {$course_contact_id})
                ";
                $resultSubject  = $db->sql_query($SQLSubject);
                $numRows = $db->sql_numrows($resultSubject);

                while ($rowSubject = $db->sql_fetchrow($resultSubject)) {
                    $fa = array();
                    $fa['order_id']   = $ccRec['order_id'];
                    $fa['module']     = 'agileIms_subject';
                    $fa['record_id']  = $ccRec['course_id'];
                    $fa['qty']        = 1;
                    $fa['item_title'] = $rowSubject['title'];
                    $fa['unit_price'] = $rowSubject['fees'];
                    $fa['contact_id'] = $ccRec['contact_id'];
                    $fn->addRecord($fa, 'order_item');

                    $fa = array();
                    $fa['course_contact_id'] = $course_contact_id;
                    $fa['subject_id']        = $rowSubject['subject_id'];
                    $fn->addRecord($fa, 'course_contact_subject_history');
                }
                //to delete the subjects from the history table which are not selected
                $DeleteSQL  = "
                    DELETE FROM course_contact_subject_history
                        WHERE course_contact_id = {$course_contact_id}
                        AND subject_id NOT IN ({$selectedSubjectIds})
                ";
                $resultSubject  = $db->sql_query($DeleteSQL);

                //to delete the subjects from the order item table which are not selected
                $DeleteSQL  = "
                    DELETE FROM order_item
                        WHERE order_id = {$ccRec['order_id']}
                             AND module = 'agileIms_subject'
                        AND item_title NOT IN
                        (SELECT title
                          FROM subject
                         WHERE subject_id IN ({$selectedSubjectIds})
                )
                ";
                $resultSubject  = $db->sql_query($DeleteSQL);
            }
            else{
                //if subject array is empty then below code will delete the subjects from the history table
                $DeleteSQL  = "
                    DELETE FROM course_contact_subject_history
                        WHERE course_contact_id = {$course_contact_id}
                ";
                $resultSubject  = $db->sql_query($DeleteSQL);

                //to delete the subjects from the order item table when subjectarray is empty
                $DeleteSQL  = "
                    DELETE FROM order_item
                        WHERE order_id = {$ccRec['order_id']}
                             AND module = 'agileIms_subject'
                ";
                $resultSubject  = $db->sql_query($DeleteSQL);
            }

            // If discount is not empty add in course contact and order_item
            $expOrderItemDiscount = array('condn' => " AND module = 'agileIms_discount'");
            $orderItemRecDiscount = $fn->getRecordRowByID('order_item', 'order_id', $ccRec['order_id'], $expOrderItemDiscount);
            if ($discount){
                $fa = array();
                $fa['discount'] = $discount;
                $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id );

                if ($orderItemRecDiscount['order_item_id']){
                    $fa = array();
                    $fa['item_title'] = 'Discount';
                    $fa['unit_price'] = $discount;
                    $fn->saveRecord($fa, 'order_item', 'order_item_id', $orderItemRecDiscount['order_item_id']);
                }
                else{
                    $fa = array();
                    $fa['order_id']   = $ccRec['order_id'];
                    $fa['module']     = 'agileIms_discount';
                    $fa['record_id']  = $ccRec['course_id'];
                    $fa['qty']        = 1;
                    $fa['item_title'] = 'Discount';
                    $fa['unit_price'] = $discount;
                    $fa['contact_id'] = $ccRec['contact_id'];
                    $fn->addRecord($fa, 'order_item');
                }
            }
            else{
                $fa = array();
                $fa['item_title'] = 'Discount';
                $fa['unit_price'] = $discount;
                $fn->saveRecord($fa, 'order_item', 'order_item_id', $orderItemRecDiscount['order_item_id']);
            }

            $fa = array();
            $fa['batch_id'] = $batch_id;
            $fa['discount'] = $discount;
            $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id );
        }
        return $validate->getSuccessMessageXML();
        //}
    }

    /**
    */
    function getCourseValueForDropDown() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $courseType   = $fn->getReqParam('courseType');
        $json = array();

        if ($courseType == ''){
            $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
            return json_encode($json);
        }

        $SQL  = "
        SELECT c.course_id
              ,c.title
        FROM course c
        WHERE c.course_type = '{$courseType}'
        ";
        $result = $db->sql_query($SQL);

        $json[] = array('value' => '', 'caption' => $ln->gd('cp.form.lbl.pleaseSelect'));
        while ($row = $db->sql_fetchrow($result)) {
            $json[] = array("value" => $row[0], "caption" => $row[1]);
        }

        return json_encode($json);
    }

    /**
    */
    function getDiscountValueForPvt($onlyTotal = "", $discount="", $course_id = "", $full_time = "" ){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $count = '';

        if($full_time == ''){
            $full_time = $fn->getReqParam('full_time');
        }

        if($discount == ''){
            $discount  = $fn->getReqParam('discount');
        }

        if($course_id == ''){
            $course_id = $fn->getReqParam('course_id');
        }

        $courseRec   = $fn->getRecordRowByID('course', 'course_id', $course_id);
        $discount_amount = 0;
        if ($discount != '') {
            $discountRec = $fn->getRecordByCondition('subsidy_discount', "subsidy_discount_id = {$discount} AND category_type = 'Discount'");
            if ($discountRec['mode_of_calculation'] == '%') {
                $discount_amount = ($courseRec['price'] * $discountRec['value']) / 100;
            } else {
                $discount_amount = $discountRec['value'];
            }
        }

        if ($discount == '') {
            $discount_amount = 0;
        }

        $text = "
        <td>Discount</td>
        <td class='amount txtRight'>{$discount_amount}</td>
        ";

        if ($onlyTotal == 1) {
            $text = $discount_amount;
        }
        return $text;
    }

    /**
    */
    function getInstallmentAmountForPvt(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $instNumber  = $fn->getReqParam('instNumber');
        $course_id   = $fn->getReqParam('course_id');
        $discount    = $fn->getReqParam('discount');
        $medical_ins = $fn->getReqParam('medical_ins');
        $full_time   = $fn->getReqParam('full_time');
        $no_of_months= $fn->getReqParam('no_of_months');

        //$full_time   = 1;
        $subject_total = '';
         $subjectTotal= '';
        $medical_insurance = '';
        //$medical_ins = 75;

        if($medical_ins == 1){
            $medical_insurance = $fn->getSettingsValueByKey("medicalInsuranceFeePvt");
        }

        $courseRec  = $fn->getRecordRowByID('course', 'course_id', $course_id);
        $total      = $courseRec['price'];

        $discount_amount = ($total * $discount) / 100;
        $instAmount = '';
        if ($instNumber == ''){
             $text = "
            <td>Installment</td>
            <td class='amount txtRight'></td>
            ";
            return $text;
        }

        $count = count($_SESSION['selectedSubjectIds']);

        if ($count != ''){
            $discount_amount = '';
            $selectedSubjectIds = join(',', $_SESSION['selectedSubjectIds']);

            $SQL  = "
            SELECT s.*
            FROM subject s
            WHERE s.subject_id IN ({$selectedSubjectIds})
            ";
            $result  = $db->sql_query($SQL);
            $numRows = $db->sql_numrows($result);

            while ($row = $db->sql_fetchrow($result)) {

                /*if($full_time == 1){
                    $subject_total += $row['fees'];
                }
                else{
                    if($row['title'] == 'Science Lab'){
                        $subject_total += 400;
                    }
                    else{
                        if($no_of_months != 9 && $no_of_months != ''){
                            $subject_total += ($row['fees']/9)* $no_of_months;
                        }
                        else{
                            $subject_total += $row['fees'];
                        }
                    }
                }
                */

                $subject_total += $row['fees'];
            }

            $total = $subject_total;
            if($discount){
                $discount_amount = (($subject_total + $medical_insurance) * $discount)/100;
            }
        }
        $total = $total - $discount_amount + $medical_insurance;
        $instAmount = $total / $instNumber;
        $instAmount = round($instAmount, 3);
        //$instAmount = number_format($instAmount, 2);
        $text = "
        <td>Installment Amount</td>
        <td class='amount txtRight'>{$instAmount}</td>
        ";

        return $text;
    }
}