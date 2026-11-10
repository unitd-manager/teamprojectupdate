<?
class CP_Admin_Modules_AgileIms_OrderLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $company_id = $fn->getReqParam('company_id');

        $fa['company_id']       = $company_id;
        $fa['payment_method']   = '';
        $fa['module']           = 'agileIms_course';
        $fa['order_status']     = 'Due';
        $fa['order_date']       =  date('Y-m-d');
        $fa['contact_module']   = 'agileIms_company';

        $companyRec = $fn->getRecordRowByID('company', 'company_id', $company_id);

        $fa['cust_first_name']            = $companyRec['title'];
        $fa['cust_email']                 = $companyRec['email'];
        $fa['cust_phone']                 = $companyRec['phone'];
        $fa['cust_address1']              = $companyRec['address1'];
        $fa['cust_address2']              = $companyRec['address2'];
        $fa['cust_address_po_code']       = $companyRec['address_po_code'];
        $fa['cust_address_country_code']  = $companyRec['address_country_code'];
        $fa['company_contact_salutation'] = $companyRec['salutation'];
        $fa['company_contact_name']       = $companyRec['contact_name'];

        return $fa;
    }

    /**
    */
    function getParentFields(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $parent_id = $fn->getReqParam('parent_id');

        $parentRec = $fn->getRecordRowByID('parent', 'parent_id', $parent_id);

        $fa['cust_first_name']          = $parentRec['first_name'];
        $fa['cust_last_name']           = $parentRec['last_name'];
        $fa['cust_email']               = $parentRec['email'];
        $fa['cust_phone']               = $parentRec['phone'];
        $fa['cust_address1']            = $parentRec['address_flat'];
        $fa['cust_address_city']        = $parentRec['address_city'];
        $fa['cust_address_state']       = $parentRec['address_state'];
        $fa['cust_address_po_code']     = $parentRec['address_po_code'];
        $fa['parent_id']                = $parent_id;
        $fa['payment_method']           = $parentRec['mode_of_payment'];
        $fa['module']                   = 'agileIms_course';
        $fa['order_status']             = 'Due';
        $fa['order_date']               =  date('Y-m-d');
        $fa['contact_module']           = 'agileIms_parent';

        return $fa;
    }

    /**
    */
    function getNewValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $trainee_id_arr  = $_SESSION['selectedContactIds'];
        $course_id_arr   = $fn->getPostParam('course_id', array());

        $validate->resetErrorArray();

        $count = count($trainee_id_arr);

        if ($count == 0) {
            $msg = 'Please add the trainees and then do the enrollment';
            $validate->validateData('error_box', $msg);
        }

        $contactRowErr = '';
        $total_students = 1;

        for ($i= 0; $i < $count; $i++) {
            $trainee_id  = $trainee_id_arr[$i];
            $course_id   = $course_id_arr[$i];
            if ($course_id == '') {
                $contactRec = $fn->getRecordRowById('contact', 'contact_id', $trainee_id);

                $contactRowErr[] = $contactRec['first_name'];
                $total_students++;
            }
        }

        if (is_array($contactRowErr)){
            $contacts = join(', ', $contactRowErr);
            $msg = 'Please choose the course for the student(s): ' . $contacts;
            $validate->validateData('error_box', $msg);
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
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        //$order_id = $fn->getPostParam('order_id', '', true);        
        //$receiptCount = $fn->getRecordCount('receipt', "receipt_status = 'Paid' AND order_id = '{$order_id}'");
        //
        //$validate->resetErrorArray();
        ///* Alert Message to cancel the receipts to do the changes */
        //if ($receiptCount) {
        //    $msg = 'Please cancel the receipt for the enrollment to apply changes';
        //    $validate->validateData('error_box', $msg);
        //}

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * STEP 1: CREATING A ORDER RECORD FOR THE ENROLLMENT (ONE COMPANY ONE ORDER RECEORD)
     * STEP 2: CREATING ENROLLMENT RECORD FOR EACH STUDENT OF THE COMPANY
     * STEP 2A: FINDING THE SELECTED VALUES FOR THE STUDENT IN ENROLLMENT
     * STEP 2B: CREATING A COURSE CONTACT RECORD FOR THE ORDER (ONE COURSE CONTACT RECEORD FOR EACH STUDENT)
     * STEP 2C: CREATING A ORDER ITEM FOR COURSE FOR THE ORDER
     * STEP 2D: CREATING A ORDER ITEM FOR SUBSIDY FOR THE ORDER
     * STEP 2E: CREATING A ORDER ITEM FOR DISCOUNT FOR THE ORDER
     * STEP 2F: CREATING A ORDER ITEM FOR REGISTRATION FEE FOR THE ORDER
     */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $order_id   = $fn->getReqParam('order_id');
        $company_id = $fn->getReqParam('company_id');
        $parent_id  = $fn->getReqParam('parent_id');

        if (!$this->getNewValidate()) {
            return $validate->getErrorMessageXML();
        }

        $company_id = $fn->getReqParam('company_id');
        $parent_id  = $fn->getReqParam('parent_id');

        /********************************** STEP 1 **************************************/
        if ($order_id == '') {
            $fa       = $this->getFields();
            $order_id = $fn->addRecord($fa);
        }

        /********************************** STEP 1 ENDS HERE ****************************/
        $trainee_id_arr  = $_SESSION['selectedContactIds'];
        $course_id_arr   = $fn->getPostParam('course_id', array());
        $batch_id_arr    = $fn->getPostParam('batch_id', array());
        $subsidy_id_arr  = $fn->getPostParam('course_subsidy_history_id', array());
        $discount_id_arr = $fn->getPostParam('discount_id', array());
        $add_reg_fee_arr = $fn->getPostParam('add_reg_fee', array());
        $auto_generate_invoice = $fn->getPostParam('auto_generate_invoice');
        $auto_generate_receipt = $fn->getPostParam('auto_generate_receipt');

        $count = count($trainee_id_arr);
        $recCount = 0;
        /********************************** STEP 2 **************************************/
        for ($i= 0; $i< $count; $i++) {
            /********************************** STEP 2A *********************************/
            $discount    = '';
            $trainee_id  = $trainee_id_arr[$i];
            $course_id   = $course_id_arr[$i];
            $batch_id    = $batch_id_arr[$i];
            $subsidy_id  = $subsidy_id_arr[$i];
            $discount_id = $discount_id_arr[$i];
            /********************************** STEP 2A ENDS HERE ***********************/
            if ($course_id > 0) {
                $this->getInsertEnrollmentRecordsForTrainee($company_id, $trainee_id, $order_id, $course_id, $batch_id, $subsidy_id, $discount_id);
                /********************************** STEP 2F *****************************/
                foreach ($add_reg_fee_arr AS $reg_fee_contact_id) {
                    if ($trainee_id == $reg_fee_contact_id) {
                        $this->getInsertOrderItemRecForRegFees($order_id, $trainee_id, $course_id);
                    }
                }
                /********************************** STEP 2F ENDS HERE *******************/
                $recCount++;
            }
        }
        // Creating Invoice and Invoice Item records for the contact
        if ($auto_generate_invoice == 1) {
            $this->getCreateInvoiceRecords($order_id, $auto_generate_receipt);
        }

        /********************************** STEP 2 ENDS HERE ****************************/

        return $validate->getSuccessMessageXML();
    }

    /**
    */
    function getCreateInvoiceRecords($order_id, $auto_generate_receipt) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

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
        $faInv['creation_date']    = date('Y-m-d H:i:s');
        $faInv['invoice_code']     = $invoice_code;
        $faInv['inv_currency']     = 'SGD';
        $faInv['order_id']         = $order_id;

        $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
        $faInv['company_contact_salutation'] = $orderRec['company_contact_salutation'];
        $faInv['company_contact_name']       = $orderRec['company_contact_name'];
        $faInv['cust_first_name']            = $orderRec['cust_first_name'];
        $faInv['cust_email']                 = $orderRec['cust_email'];
        $faInv['cust_address1']              = $orderRec['cust_address1'];
        $faInv['cust_address2']              = $orderRec['cust_address2'];
        $faInv['cust_address_po_code']       = $orderRec['cust_address_po_code'];
        $faInv['cust_address_country_code']  = $orderRec['cust_address_country_code'];

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

        // Creating Receipt and Invoice Receipt history records for the contact
        if ($auto_generate_receipt == 1) {
            $this->getCreateReceiptRecords($order_id, $invoice_id);
        }
    }

    /**
    */
    function getCreateReceiptRecords($order_id, $invoice_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        /* Total amount from Order Item */
        $modObj = getCPModuleObj('agileIms_order');
        $total_amount_payable = $modObj->model->getTotalAmountFromOrderItem($order_id);

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
        $faInv['status'] = 'Paid';
        $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

        $faOrd = array();
        $faOrd['order_status'] = 'Paid';
        $fn->saveRecord($faOrd, 'order', 'order_id', $order_id);
    }

    /**
    */
    function getSave() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        
        $courseRec = '';
        $course_contact_id = '';
        $recCount = 0;

        if (!$this->getEditValidate()) {
            return $validate->getErrorMessageXML();
        }

        $company_id = $fn->getReqParam('company_id');
        $order_id   = $fn->getPostParam('order_id', '', true);

        $trainee_id_arr  = $_SESSION['selectedContactIds'];
        $batch_id_arr    = $fn->getPostParam('batch_id', array());
        $subsidy_id_arr  = $fn->getPostParam('course_subsidy_history_id', array());
        $discount_id_arr = $fn->getPostParam('discount_id', array());
        $course_id_arr   = $fn->getPostParam('course_id', array());
        $add_reg_fee_arr = $fn->getPostParam('add_reg_fee', array());
        
        $count         = count($trainee_id_arr);
        $countSubsidy  = count($subsidy_id_arr);
        $countDiscount = count($discount_id_arr);
        for ($i= 0; $i< $count; $i++) {
            if (count($trainee_id_arr)) {
                $trainee_id = $trainee_id_arr[$i];
            } else {
                $trainee_id = '';
                continue;
            }
            
            /* Checking whether student is already available in course contact table for the order record */
            $recCount = $fn->getRecordCount('course_contact', "contact_id = '{$trainee_id}' AND order_id = '{$order_id}'");
            if ($recCount == 0) {
                $this->getInsertEnrollmentRecordsForTrainee($company_id, $trainee_id, $order_id, $course_id_arr[$i], $batch_id_arr[$i], $subsidy_id_arr[$i], $discount_id_arr[$i]);
                foreach ($add_reg_fee_arr AS $reg_fee_contact_id) {
                    if ($trainee_id == $reg_fee_contact_id) {
                        $this->getInsertOrderItemRecForRegFees($order_id, $trainee_id, $course_id_arr[$i]);
                    }
                }
                continue;
            }
            
            $expCourseContact = array('condn' => " AND contact_id = $trainee_id");
            $courseContactRec = $fn->getRecordRowByID('course_contact', 'order_id', $order_id, $expCourseContact);
            $batch_id         = $batch_id_arr[$i];
            
            /* Checking whether the batch is changed for paid order */
            if ($courseContactRec['batch_id'] != $batch_id && $batch_id != '' && $countSubsidy == 0 && $countDiscount == 0) {
                $faCc = array();
                $faCc['batch_id'] = $batch_id;
                $fn->saveRecord($faCc, 'course_contact', 'course_contact_id', $courseContactRec['course_contact_id']);
            } else if ($courseContactRec['batch_id'] == $batch_id && $countSubsidy == 0 && $countDiscount == 0) {
                continue;
            } else {
                $subsidy_id  = $subsidy_id_arr[$i];
                $discount_id = $discount_id_arr[$i];
                
                /* Cancelling the invoice if created already - Start */
                $sqlInv = "
                SELECT DISTINCT i.invoice_id
                FROM invoice i
                LEFT JOIN (invoice_item it) ON (i.invoice_id = it.invoice_id)
                WHERE it.contact_id = {$trainee_id}
                  AND i.order_id = {$order_id}
                  AND i.status != 'Cancelled'
                ";
                $resultInv  = $db->sql_query($sqlInv);
                $numRowsInv = $db->sql_numrows($resultInv);                
                if ($numRowsInv) {
                    while ($rowInv = $db->sql_fetchrow($resultInv)) {
                        $faInv = array();
                        $faInv['status'] = 'Cancelled';
                        $fn->saveRecord($faInv, 'invoice', 'invoice_id', $rowInv['invoice_id']);

                        $sqlOi = "
                        UPDATE order_item
                        SET invoice_id = NULL
                        WHERE order_id = {$order_id}
                          AND invoice_id = {$rowInv['invoice_id']}
                        ";
                        $resultOi = $db->sql_query($sqlOi);
                    }
                }
                /* Cancelling the invoice if created already - Stop */
                
                /* Updating Subsidy in Course contact and Order Item table - Start */
                if (($courseContactRec['subsidy_discount_type'] == 'Subsidy' || $courseContactRec['subsidy_discount_type'] == '')
                 && ($subsidy_id != $courseContactRec['subsidy_discount_id'])) {
                    $expCourseContact = array('condn' => "
                    AND contact_id = {$trainee_id} AND order_id = {$order_id}");

                    $faCc = array();
                    $faCc['subsidy_discount_id']   = $subsidy_id;
                    $faCc['subsidy_discount_type'] = 'Subsidy';
                    $fn->saveRecord($faCc, 'course_contact', 'course_contact_id', $courseContactRec['course_contact_id'], $expCourseContact);

                    if ($subsidy_id) {
                        $expCC = array('condn' => "
                        AND contact_id = $trainee_id");
                        $ccRec     = $fn->getRecordRowByID('course_contact', 'order_id', $order_id, $expCC);
                        $courseRec = $fn->getRecordRowByID('course', 'course_id', $ccRec['course_id']);
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

                        $item_title = $rowSubsidy['title'];
                        $unit_price = -$subsidyTotal;
                
                        /* Updating Order Item record or inserting a new order item record */
                        $sqlOi = "
                        SELECT order_item_id
                          FROM order_item
                        WHERE contact_id = {$trainee_id}
                          AND order_id = {$order_id}
                          AND module = 'agileIms_subsidy'
                        ";
                        $resultOi  = $db->sql_query($sqlOi);
                        $numRowsOi = $db->sql_numrows($resultOi);
                        if ($numRowsOi) {
                            $sqlOiUpdate = "
                            UPDATE order_item
                            SET item_title = '{$item_title}'
                               ,unit_price = '{$unit_price}'
                            WHERE contact_id = {$trainee_id}
                              AND order_id = {$order_id}
                              AND module = 'agileIms_subsidy'
                            ";
                            $resultOiUpdate = $db->sql_query($sqlOiUpdate);
                        } else {
                            $fa = array();
                            $fa['order_id']   = $order_id;
                            $fa['module']     = 'agileIms_subsidy';
                            $fa['record_id']  = $ccRec['course_id'];
                            $fa['qty']        = 1;
                            $fa['item_title'] = $item_title;
                            $fa['unit_price'] = $unit_price;
                            $fa['contact_id'] = $trainee_id;
                            $fn->addRecord($fa, 'order_item');
                        }
                    } else {
                        $item_title = '';
                        $unit_price = 0;

                        $sqlOiUpdate = "
                        UPDATE order_item
                        SET item_title = '{$item_title}'
                           ,unit_price = '{$unit_price}'
                        WHERE contact_id = {$trainee_id}
                          AND order_id = {$order_id}
                          AND module = 'agileIms_subsidy'
                        ";
                        $resultOiUpdate = $db->sql_query($sqlOiUpdate);
                    }
                }
                /* Updating Subsidy in Course contact and Order Item table - Stop */
                /* Updating Discount in Course contact and Order Item table - Start */
                if (($courseContactRec['subsidy_discount_type'] == 'Discount' || $courseContactRec['subsidy_discount_type'] == '')
                 && ($discount_id != $courseContactRec['subsidy_discount_id'])) {
                    $expCourseContact = array('condn' => "
                    AND contact_id    = {$trainee_id} AND order_id = {$order_id}");

                    $faCc = array();
                    $faCc['subsidy_discount_id']   = $discount_id;
                    $faCc['subsidy_discount_type'] = 'Discount';
                    $fn->saveRecord($faCc, 'course_contact', 'course_contact_id', $courseContactRec['course_contact_id'], $expCourseContact);

                    if ($discount_id) {
                        $expCC = array('condn' => "
                        AND contact_id    = $trainee_id");
                        $ccRec     = $fn->getRecordRowByID('course_contact', 'order_id', $order_id, $expCC);
                        $courseRec = $fn->getRecordRowByID('course', 'course_id', $ccRec['course_id']);
                        $sqlDisc = "
                        SELECT sd.*
                        FROM subsidy_discount sd
                        WHERE sd.subsidy_discount_id = {$ccRec['subsidy_discount_id']}
                        ";
                        $resultDisc  = $db->sql_query($sqlDisc);
                        $rowDisc     = $db->sql_fetchrow($resultDisc);    
                        if ($rowDisc['mode_of_calculation'] == 'Value') {
                            $discTotal = $rowDisc['value'];
                        } else {
                            $discTotal = ($courseRec['price']*$rowDisc['value'])/100;
                        }
                        
                        $item_title = $rowDisc['title'];
                        $unit_price = -$discTotal;

                        /* Updating Order Item record or inserting a new order item record */
                        $sqlOi = "
                        SELECT order_item_id
                          FROM order_item
                        WHERE contact_id = {$trainee_id}
                          AND order_id = {$order_id}
                          AND module = 'agileIms_discount'
                        ";
                        $resultOi  = $db->sql_query($sqlOi);
                        $numRowsOi = $db->sql_numrows($resultOi);
                        if ($numRowsOi) {
                            $sqlOiUpdate = "
                            UPDATE order_item
                            SET item_title = '{$item_title}'
                               ,unit_price = '{$unit_price}'
                            WHERE contact_id = {$trainee_id}
                              AND order_id = {$order_id}
                              AND module = 'agileIms_discount'
                            ";
                            $resultOiUpdate = $db->sql_query($sqlOiUpdate);
                        } else {
                            $fa = array();
                            $fa['order_id']   = $order_id;
                            $fa['module']     = 'agileIms_discount';
                            $fa['record_id']  = $ccRec['course_id'];
                            $fa['qty']        = 1;
                            $fa['item_title'] = $item_title;
                            $fa['unit_price'] = $unit_price;
                            $fa['contact_id'] = $trainee_id;
                            $fn->addRecord($fa, 'order_item');
                        }
                    } else {
                        $item_title = '';
                        $unit_price = 0;

                        $sqlOiUpdate = "
                        UPDATE order_item
                        SET item_title = '{$item_title}'
                           ,unit_price = '{$unit_price}'
                        WHERE contact_id = {$trainee_id}
                          AND order_id = {$order_id}
                          AND module = 'agileIms_discount'
                        ";
                        $resultOiUpdate = $db->sql_query($sqlOiUpdate);
                    }
                }
                /* Updating Discount in Course contact and Order Item table - Stop */
            }
        }
        
        /* Removal of contacts from enrollment */
        $selectedTraineeIds = join(',', $_SESSION['selectedContactIds']);
        $sqlCc = "
        SELECT contact_id
        FROM course_contact
        WHERE order_id = {$order_id}
          AND contact_id NOT IN ($selectedTraineeIds)
        ";
        $resultCc = $db->sql_query($sqlCc);
        while ($rowCc = $db->sql_fetchrow($resultCc)) {
            /* Removal of contact from Order Item Table */
            $removeOi = "
            DELETE FROM `order_item`
            WHERE contact_id = {$rowCc['contact_id']}
              AND order_id = {$order_id}
            ";
            $resultRemoveOi = $db->sql_query($removeOi);

            /* Removal of contact from Course Contact Table */
            $removeCc = "
            DELETE FROM course_contact
            WHERE contact_id = {$rowCc['contact_id']}
              AND order_id = {$order_id}
            ";
            $resultRemoveCc = $db->sql_query($removeCc);
        }
        
        return $validate->getSuccessMessageXML();
    }

    /**
    */
    function getSaveOld() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $courseRec = '';
        $course_contact_id = '';
        $recCount = 0;

        if (!$this->getEditValidate()) {
            return $validate->getErrorMessageXML();
        }

        $company_id = $fn->getReqParam('company_id');
        $order_id   = $fn->getPostParam('order_id', '', true);

        $trainee_id_arr = $_SESSION['selectedContactIds'];
        $batch_id_arr   = $fn->getPostParam('batch_id', array());
        $subsidy_id_arr = $fn->getPostParam('course_subsidy_history_id', array());
        $discount_id_arr= $fn->getPostParam('discount_id', array());

        $count = count($trainee_id_arr);
        for ($i= 0; $i< $count; $i++){
            if(count($trainee_id_arr)){
                $trainee_id = $trainee_id_arr[$i];
            } else {
                $trainee_id = '';
                continue;
            }
            
            $batch_id    = $batch_id_arr[$i];
            $subsidy_id  = $subsidy_id_arr[$i];
            $discount_id = $discount_id_arr[$i];

            $expCourseContact = array('condn' => "
            AND contact_id    = $trainee_id
            ");

            $courseContactRec  = $fn->getRecordRowByID('course_contact', 'order_id', $order_id, $expCourseContact);

            $course_contact_id = $courseContactRec['course_contact_id'];

            $expOrderItemCourse = array('condn' => "AND record_id = $course_id
            AND module      = 'agileIms_course'
            AND contact_id  = $trainee_id
            ");

            $expOrderItemSubsidy = array('condn' => "AND record_id = $course_id
            AND module      = 'agileIms_subsidy'
            AND contact_id  = $trainee_id
            ");

            $expOrderItemDiscount = array('condn' => "AND record_id = $course_id
            AND module      = 'agileIms_discount'
            AND contact_id  = $trainee_id
            ");

            // To update record in course contact item if batch/subsidy is changed
            if ($course_contact_id > 0) {
                $fa = array();
                $fa['batch_id']         = $batch_id;
                $fa['course_subsidy_history_id']= $subsidy_id;
                $fa['discount']         = $discount_id;
                $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id);

                // to create new order item, if new subsidy is created or update the existing subsidy
                $orderItemRecSubsidy = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemSubsidy);
                $discountRec         = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemDiscount);

                //$subsidyRec = $fn->getRecordRowByID('course_subsidy_history_id', 'course_subsidy_history_id', $subsidy_id);

                if($subsidy_id){
                    $subsidySql = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                    WHERE csh.course_subsidy_history_id = {$subsidy_id}
                    ";
                    $resultsubsidy  = $db->sql_query($subsidySql);
                    $rowsubsidy = $db->sql_fetchrow($resultsubsidy);

                    if (!is_array($orderItemRecSubsidy)) {
                        $fa = array();
                        $fa['order_id']   = $order_id;
                        $fa['module']     = 'agileIms_subsidy';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        $fa['item_title'] = $rowsubsidy['title'];
                        $fa['unit_price'] = -$rowsubsidy['value'];
                        $fn->addRecord($fa, 'order_item');
                    } else {
                        $fa = array();
                        $fa['record_id']  = $course_id;
                        $fa['item_title'] = $rowsubsidy['title'];
                        $fa['unit_price'] = -$rowsubsidy['value'];
                        $fn->saveRecord($fa, 'order_item', 'order_item_id', $orderItemRecSubsidy['order_item_id']);
                    }
                }

                // to create new order item, if new discount is created or update the existing discount
                if($discount_id) {
                    $discountSql = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                    WHERE csh.course_subsidy_history_id = {$discount_id}
                    ";
                    $resultdiscount  = $db->sql_query($discountSql);
                    $rowdiscount = $db->sql_fetchrow($resultdiscount);

                    if (!is_array($discountRec)){
                        $fa = array();
                        $fa['order_id']   = $order_id;
                        $fa['module']     = 'agileIms_discount';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        //$fa['item_title'] = 'Discount';
                        $fa['item_title'] = $rowdiscount['title'];
                        $fa['unit_price'] = -$rowdiscount['value'];
                        $fn->addRecord($fa, 'order_item');
                    } else {
                        $fa = array();
                        $fa['record_id']  = $course_id;
                        //$fa['item_title'] = 'Discount';
                        $fa['item_title']  = $rowdiscount['title'];
                        $fa['unit_price'] = -$rowdiscount['value'];
                        $fn->saveRecord($fa, 'order_item', 'order_item_id',
                        $discountRec['order_item_id']);
                    }
                }
            }
            // To add record in course contact/order_item item if new trainee/course is added
            else{
                if ($course_id > 0) {
                    $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'agileIms_course';
                    $fa['record_id']  = $course_id;
                    $fa['contact_id'] = $trainee_id;
                    $fa['qty']        = 1;
                    $fa['item_title'] = $courseRec['title'];
                    $fa['unit_price'] = $courseRec['price'];
                    $fn->addRecord($fa, 'order_item');

                    if ($subsidy_id > 0) {
                        $subsidySql = "
                        SELECT sd.*
                        FROM subsidy_discount sd
                        LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                        WHERE csh.course_subsidy_history_id = {$subsidy_id}
                        ";
                        $resultsubsidy  = $db->sql_query($subsidySql);
                        $rowsubsidy = $db->sql_fetchrow($resultsubsidy);

                        $fa = array();
                        $fa['order_id']   = $order_id;
                        $fa['module']     = 'agileIms_subsidy';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        $fa['item_title'] = $rowsubsidy['title'];
                        $fa['unit_price'] = -$rowsubsidy['value'];
                        $fn->addRecord($fa, 'order_item');
                    }

                    if ($discount_id > 0) {
                        $discountSql = "
                        SELECT sd.*
                        FROM subsidy_discount sd
                        LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                        WHERE csh.course_subsidy_history_id = {$discount_id}
                        ";
                        $resultdiscount  = $db->sql_query($discountSql);
                        $rowdiscount = $db->sql_fetchrow($resultdiscount);

                        $fa = array();
                        $fa['order_id']   = $order_id;
                        $fa['module']     = 'agileIms_discount';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        //$fa['item_title'] = 'Discount';;
                        $fa['item_title'] = $rowdiscount['title'];
                        $fa['unit_price'] = -$rowdiscount['value'];
                        $fn->addRecord($fa, 'order_item');
                    }

                    $fa = array();
                    $fa['order_id']                  = $order_id;
                    $fa['course_id']                 = $course_id;
                    $fa['company_id']                = $company_id;
                    $fa['batch_id']                  = $batch_id;
                    $fa['contact_id']                = $trainee_id;
                    $fa['course_subsidy_history_id'] = $subsidy_id;
                    $fa['discount']                  = $discount_id;

                    $fn->addRecord($fa, 'course_contact');
                    $recCount++;
                }
            }

        }
       return $validate->getSuccessMessageXML();
    }

    /**
     * REMOVING THE TRAINEE DURING ENROLLMENT PROCESS
    */
    function getRemoveTrainee(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $contact_id = $fn->getReqParam('contact_id');
        $order_id   = $fn->getReqParam('order_id');
        $s = &$_SESSION['selectedContactIds'];

        if(($key= array_search($contact_id, $s)) !== false){
            unset($s[$key]);
        }

        $s = array_values($s);
        
        return $this->view->getTraineeSearchResult();
    }

    /**
     * EDITING AND SAVING CONTACT DETAILS DURING ENROLLMENT
    */
    function getContactSave(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $contact_id = $fn->getPostParam('contact_id');

        if (!$this->getContactEditValidate()) {
            return $validate->getErrorMessageXML();
        }

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'is_citizen');
        $fa = $fn->addToFieldsArray($fa, 'nationality');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'age');
        print_r ($fa);
        return
        $fn->saveRecord($fa, 'contact', 'contact_id', $contact_id);

        return $validate->getSuccessMessageXML('', '', array('data' => $fa));

    }

    /**
     * ADDING A NEW CONTACT FOR THE COMPANY DURING ENROOLMENT AND ADDING HIM TO THE LINKED CONTACTS
    */
    function getContactAddSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $history_id  = $fn->getPostParam('history_id');
        $parent_link = $fn->getPostParam('parent_link');

        if (!$this->getContactAddValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'academic_school_level');
        $fa = $fn->addToFieldsArray($fa, 'id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'is_citizen');
        $fa = $fn->addToFieldsArray($fa, 'nationality');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'age');

        if ($parent_link != 'yes') {
            $fa['company_id'] = $history_id;
        }

        $contact_id = $fn->addRecord($fa, 'contact');

        if ($parent_link == 'yes') {
            $fa1 = array();
            $fa1['parent_id']  = $history_id;
            $fa1['contact_id'] = $contact_id;
            $fa1['creation_date']  = date("Y-m-d H:i:s");

            $parent_contact_id = $fn->addRecord($fa1, 'parent_contact');
        }

        $_SESSION['selectedContactIds'][] = $contact_id;
        //below code will be used in case if a new trainee is added, in getSelectedTraineeResultRow
        $_SESSION['newTrainee']           = $contact_id;

        return $validate->getSuccessMessageXML();

    }

    /**
     * VALIDATION DURING ADDING A NEW CONTACT DURING COMPANY ENROLLMENT
    */
    function getContactAddValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');
        $validate->validateData('id_card_no' , 'Please enter NRIC / FIN / Passport');

        $email = $fn->getPostParam('email', '', true);
        $id_card_no = $fn->getPostParam('id_card_no', '', true);

        if ($id_card_no != ''){
            $rec = $fn->getRecordByCondition('contact', "id_card_no = '{$id_card_no}'");
            $expIdCard = array('displayText' => $id_card_no);
            $IdCardlink = $fn->getRecordDetailLink('agileIms_contact', 'record_id', $rec['contact_id'], $expIdCard);

            if (is_array($rec)){
                $validate->errorArray['id_card_no']['name'] = "id_card_no";
                $validate->errorArray['id_card_no']['msg']  = "NRIC / FIN / Passport already exists. '{$IdCardlink}'";
            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * VALIDATION DURING editing A CONTACT DURING COMPANY ENROLLMENT
    */
    function getContactEditValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');
        //$validate->validateData('email' , 'Please enter a valid email address', 'email');
        $validate->validateData('id_card_no' , 'Please enter NRIC / FIN / Passport');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *STEP 1: INSERTING RECORD IN ORDER ITEM TABLE
     *STEP 2: UPDATING REG FEE VALUE IN COURSE CONTACT TABLE
     */
    function getInsertOrderItemRecForRegFees($order_id, $trainee_id, $course_id) {
        $fn = Zend_Registry::get('fn');

        /********************************** STEP 1 **************************************/
        $fa = array();
        $fa['order_id']   = $order_id;
        $fa['module']     = 'agileIms_registration';
        $fa['record_id']  = $course_id;
        $fa['qty']        = 1;
        $fa['item_title'] = 'Registration Fee';
        $fa['unit_price'] = $fn->getSettingsValueByKey("registrationFee");;
        $fa['contact_id'] = $trainee_id;
        $fn->addRecord($fa, 'order_item');
        /********************************** STEP 1 ENDS HERE ****************************/
        /********************************** STEP 2 **************************************/
        $expCc = array('customWhereCondn' => "contact_id = '{$trainee_id}' AND course_id = '{$course_id}'");
        $faCc = array();
        $faCc['add_registration_fee'] = 1;
        $fn->saveRecord($faCc, 'course_contact', 'order_id', $order_id, $expCc);
        /********************************** STEP 2 ENDS HERE ****************************/
    }

    /**
     */
    function getCheckInvoiceForContactInCompanyEditEnrollment() {
        $fn = Zend_Registry::get('fn');

        $contact_id = $fn->getReqParam('contact_id');
        $order_id   = $fn->getReqParam('order_id');
        
        $recCount = $fn->getRecordCount('order_item', "contact_id = '{$contact_id}' AND order_id = '{$order_id}' AND invoice_id != ''");
        if ($recCount) {
            return "Invoice cannot modify";
        } else {
            return "1";
        }
    }
    
    /**
     */
    function getInsertEnrollmentRecordsForTrainee($company_id, $trainee_id, $order_id, $course_id, $batch_id, $subsidy_id, $discount_id) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /********************************** STEP 2B *****************************/
        $fa = array();
        $fa['order_id']                 = $order_id;
        $fa['course_id']                = $course_id;
        $fa['company_id']               = $company_id;
        $fa['batch_id']                 = $batch_id;
        $fa['contact_id']               = $trainee_id;

        if ($subsidy_id > 0) {
            $fa['subsidy_discount_type'] = 'Subsidy';
            $fa['subsidy_discount_id']   = $subsidy_id;
        } else if ($discount_id > 0) {
            $fa['subsidy_discount_type'] = 'Discount';
            $fa['subsidy_discount_id']   = $discount_id;
        }

        $id = $fn->addRecord($fa, 'course_contact');
        $ccRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $id);
        /********************************** STEP 2B ENDS HERE *******************/
        /********************************** STEP 2C *****************************/
        $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
        $fa = array();
        $fa['order_id']          = $order_id;
        $fa['module']            = 'agileIms_course';
        $fa['record_id']         = $course_id;
        $fa['contact_id']        = $trainee_id;
        $fa['qty']               = 1;
        $fa['item_title']        = $courseRec['title'];
        $fa['unit_price']        = $courseRec['price'];
        $fa['course_start_date'] = $courseRec['valid_date_from'];
        $fa['course_end_date']   = $courseRec['valid_date_to'];
        $fa['course_code']       = $courseRec['course_code'];
        $fn->addRecord($fa, 'order_item');
        /********************************** STEP 2C ENDS HERE *******************/
        /********************************** STEP 2D *****************************/
        if ($subsidy_id > 0) {
            $sqlSubsidy = "
            SELECT sd.*
            FROM subsidy_discount sd
            LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
            WHERE sd.subsidy_discount_id = {$subsidy_id}
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
            $fa['record_id']  = $course_id;
            $fa['contact_id'] = $trainee_id;
            $fa['qty']        = 1;
            $fa['item_title'] = $rowSubsidy['title'];
            $fa['unit_price'] = -$subsidyTotal;
            $fn->addRecord($fa, 'order_item');
        }
        /********************************** STEP 2D ENDS HERE *******************/
        /********************************** STEP 2E *****************************/
        if ($discount_id > 0) {
            $sqlDiscount = "
            SELECT sd.*
            FROM subsidy_discount sd
            LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
            WHERE sd.subsidy_discount_id = {$discount_id}
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
            $fa['contact_id'] = $trainee_id;
            $fa['record_id']  = $course_id;
            $fa['qty']        = 1;
            $fa['item_title'] = $rowDiscount['title'];
            $fa['unit_price'] = -$discTotal;
            $discount         = $discount_id;
            $fn->addRecord($fa, 'order_item');
        }
        /********************************** STEP 2E ENDS HERE *******************/
    }
}