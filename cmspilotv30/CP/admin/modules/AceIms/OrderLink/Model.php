<?
class CP_Admin_Modules_AceIms_OrderLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $company_id = $fn->getReqParam('company_id');

        $fa['company_id']       = $company_id;
        $fa['payment_method']   = '';
        $fa['module']           = 'aceIms_course';
        $fa['order_status']     = 'Due';
        $fa['order_date']       =  date('Y-m-d');
        $fa['contact_module']   = 'aceIms_company';

        $companyRec = $fn->getRecordRowByID('company', 'company_id', $company_id);

        $fa['cust_first_name']            = $companyRec['title'];
        $fa['cust_email']                 = $companyRec['email'];
        $fa['cust_phone']                 = $companyRec['phone'];
        $fa['cust_address1']              = $companyRec['address1'];
        $fa['cust_address2']              = $companyRec['address2'];
        $fa['cust_address_city']          = $companyRec['address_city'];
        $fa['cust_address_state']         = $companyRec['address_state'];
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
        $fa['module']                   = 'aceIms_course';
        $fa['order_status']             = 'Due';
        $fa['order_date']               =  date('Y-m-d');
        $fa['contact_module']           = 'aceIms_parent';

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
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

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
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $order_id        = $fn->getReqParam('order_id');
        $company_id      = $fn->getReqParam('company_id');
        $enrollment_year = $fn->getPostParam('enrollment_year');

        if (!$this->getNewValidate()) {
            return $validate->getErrorMessageXML();
        }
        /********************************** STEP 1 **************************************/
        if ($order_id == '') {
            $fa                       = $this->getFields();
            $fa['year_of_enrollment'] = $enrollment_year;
            $order_id = $fn->addRecord($fa);
        }
        /********************************** STEP 1 ENDS HERE ****************************/
        $trainee_id_arr            = $_SESSION['selectedContactIds'];
        $trainee_id_subject_id_arr = $_SESSION['selectedSubjectIds'];
        $course_type_arr           = $fn->getPostParam('course_type', array());
        $course_id_arr             = $fn->getPostParam('course_id', array());
        $batch_id_arr              = $fn->getPostParam('batch_id', array());
        $batch_longterm_id_arr     = $_SESSION['selectedBatchIds'];
        $subsidy_id_arr            = $fn->getPostParam('course_subsidy_history_id', array());
        $discount_id_arr           = $fn->getPostParam('discount_id', array());
        $add_reg_fee_arr           = $fn->getPostParam('add_reg_fee', array());
        $fees_by_module_arr        = $fn->getPostParam('fees_by_module', array());
        $auto_generate_invoice     = $fn->getPostParam('auto_generate_invoice');
        $auto_generate_receipt     = $fn->getPostParam('auto_generate_receipt');

        $count = count($trainee_id_arr);
        $recCount = 0;
        /********************************** STEP 2 **************************************/
        for ($i= 0; $i< $count; $i++) {
            /********************************** STEP 2A *********************************/
            $discount    = '';

            $course_type = $course_type_arr[$i];
            $trainee_id  = $trainee_id_arr[$i];
            $course_type = $course_type_arr[$i];
            $course_id   = $course_id_arr[$i];
            if ($course_type != 'Long Term') {
                $batch_id    = $batch_id_arr[$i];
            } else {
                $batch_id = '';
            }
            $subsidy_id  = $subsidy_id_arr[$i];
            $discount_id = $discount_id_arr[$i];

            $contactRec = $fn->getRecordRowById('contact', 'contact_id', $trainee_id);
            if ($contactRec['registration_no'] == '') {
                $modObj = getCPModuleObj('aceIms_contact');
                $last_reg_no = $modObj->model->getFindContactCountForRegNo($enrollment_year);
                $next_reg_no = $last_reg_no + 1;

                if ($next_reg_no < 10) {
                    $reg_no = $enrollment_year . '-000' . $next_reg_no;
                } else if($nextInvoiceCode < 99) {
                    $reg_no = $enrollment_year . '-00' . $next_reg_no;
                } else if($nextInvoiceCode < 999) {
                    $reg_no = $enrollment_year . '-0' . $next_reg_no;
                } else {
                    $reg_no = $enrollment_year . '-'. $next_reg_no;
                }

                $faCont = array();
                $faCont['registration_no'] = $reg_no;
                $fn->saveRecord($faCont, 'contact', 'contact_id', $trainee_id);
            }
            /********************************** STEP 2A ENDS HERE ***********************/
            if ($course_id > 0) {
                if ($course_type == 'Long Term') {
                    $fees_by_module = 0;
                    if (in_array($trainee_id, $fees_by_module_arr)) {
                        $fees_by_module = 1;
                    }
                    $this->getInsertLongTermEnrollmentRecordForTrainee($company_id, $trainee_id, $order_id, $course_id, $batch_id, $subsidy_id, $discount_id, $course_type, $fees_by_module, $trainee_id_subject_id_arr, $enrollment_year, $batch_longterm_id_arr);
                } else {
                    $this->getInsertEnrollmentRecordsForTrainee($company_id, $trainee_id, $order_id, $course_id, $batch_id, $subsidy_id, $discount_id, $enrollment_year);
                    $recCount++;
                }
                /********************************** STEP 2F *****************************/
                foreach ($add_reg_fee_arr AS $reg_fee_contact_id) {
                    if ($trainee_id == $reg_fee_contact_id) {
                        $this->getInsertOrderItemRecForRegFees($order_id, $trainee_id, $course_id);
                    }
                }
                /********************************** STEP 2F ENDS HERE *******************/
                /*foreach ($batch_longterm_id_arr as $key=>$value){
                    if ($value) {
                        $fa = array();
                        $fa['batch_id']   = $value;
                        $fa['contact_id'] = $trainee_id;
                        $fn->addRecord($fa, 'batch_history');
                    }

                }*/
                foreach ($batch_longterm_id_arr AS $trainee_id_batch_id) {
                    $position1   = strpos($trainee_id_batch_id, '_');
                    $student_id = substr($trainee_id_batch_id, 0, $position1);
                    $position2   = strpos($trainee_id_batch_id, '_', $position1);
                    if ($trainee_id == $student_id) {
                        $batch_id = substr($trainee_id_batch_id, $position1+1, $position2-1);

                        $fa = array();
                        $fa['batch_id']   = $batch_id;
                        $fa['contact_id'] = $trainee_id;
                        $fn->addRecord($fa, 'batch_history');
                    }
                }
            }
        }
        // Creating Invoice and Invoice Item records for the contact
        if ($auto_generate_invoice == 1) {
            $sqlCc = "
            SELECT cc.course_id
                  ,cc.contact_id
              FROM course_contact cc
            WHERE cc.order_id = {$order_id}
            ";
            $resultCc = $db->sql_query($sqlCc);
            $total_amount_payable = 0;
            while ($rowCc = $db->sql_fetchrow($resultCc)) {
                /* Total amount from Order Item */
                $modObj = getCPModuleObj('aceIms_order');
                $total_amount_payable += $modObj->model->getTotalAmountFromOrderItem($order_id, $rowCc['course_id'], $rowCc['contact_id']);
            }

            $modObj = getCPModuleObj('aceIms_courseLink');
            $modObj->model->getGenerateInvoiceAndInvoiceItemForOrder($order_id, $total_amount_payable);
        }
        /********************************** STEP 2 ENDS HERE ****************************/
        if ($auto_generate_invoice == 1 && $auto_generate_receipt == 1) {
            $modObj = getCPModuleObj('aceIms_courseLink');
            $modObj->model->getGenerateReceiptForOrder($order_id);

            $modObj = getCPModuleObj('aceIms_courseLink');
            $modObj->model->getUpdateOrderStatusToPaid($order_id);
        }

        return $validate->getSuccessMessageXML();
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
        $subject_id_arr  = $_SESSION['selectedSubjectIds'];
        $batch_id_arr    = $fn->getPostParam('batch_id', array());
        $subsidy_id_arr  = $fn->getPostParam('course_subsidy_history_id', array());
        $discount_id_arr = $fn->getPostParam('discount_id', array());
        $course_id_arr   = $fn->getPostParam('course_id', array());
        $add_reg_fee_arr = $fn->getPostParam('add_reg_fee', array());
        $fees_by_module_arr     = $fn->getPostParam('fees_by_module', array());
        $enrollment_year        = $fn->getReqParam('enrollment_year');
        $batch_longterm_id_arr  = $_SESSION['selectedBatchIds'];

        $selectedBatchIds = array();

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

            $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id_arr[$i]);
            /* Checking whether student is already available in course contact table for the order record */
            $recCount = $fn->getRecordCount('course_contact', "contact_id = '{$trainee_id}' AND order_id = '{$order_id}'");
            if ($recCount == 0) {

                $contactRec = $fn->getRecordRowById('contact', 'contact_id', $trainee_id);
                if ($contactRec['registration_no'] == '') {
                    $modObj = getCPModuleObj('aceIms_contact');
                    $last_reg_no = $modObj->model->getFindContactCountForRegNo($enrollment_year);
                    $next_reg_no = $last_reg_no + 1;

                    if ($next_reg_no < 10) {
                        $reg_no = $enrollment_year . '-000' . $next_reg_no;
                    } else if($nextInvoiceCode < 99) {
                        $reg_no = $enrollment_year . '-00' . $next_reg_no;
                    } else if($nextInvoiceCode < 999) {
                        $reg_no = $enrollment_year . '-0' . $next_reg_no;
                    } else {
                        $reg_no = $enrollment_year . '-'. $next_reg_no;
                    }

                    $faCont = array();
                    $faCont['registration_no'] = $reg_no;
                    $fn->saveRecord($faCont, 'contact', 'contact_id', $trainee_id);
                }

                if ($courseRec['course_type'] == 'Long Term') {
                    $fees_by_module = 0;
                    if (in_array($trainee_id, $fees_by_module_arr)) {
                        $fees_by_module = 1;
                    }
                    $this->getInsertLongTermEnrollmentRecordForTrainee($company_id, $trainee_id, $order_id, $course_id_arr[$i], $batch_id_arr[$i], $subsidy_id_arr[$i], $discount_id_arr[$i], 'Long Term', $fees_by_module, $subject_id_arr, $enrollment_year, $batch_longterm_id_arr);
                } else {
                    $this->getInsertEnrollmentRecordsForTrainee($company_id, $trainee_id, $order_id, $course_id_arr[$i], $batch_id_arr[$i], $subsidy_id_arr[$i], $discount_id_arr[$i], $enrollment_year);
                }

                foreach ($add_reg_fee_arr AS $reg_fee_contact_id) {
                    if ($trainee_id == $reg_fee_contact_id) {
                        $this->getInsertOrderItemRecForRegFees($order_id, $trainee_id, $course_id_arr[$i]);
                    }
                }
                continue;
            } else {
                //----------------- LONG TERM ------------------------------
                if ($courseRec['course_type'] == 'Long Term') {
                    $_SESSION['selectedSubjectIdsForContact'] = array();
                    //TO STORE SUBJECT ID IN A SESSION VARIABLE
                    foreach ($subject_id_arr AS $trainee_id_subject_id) {
                        $position   = strpos($trainee_id_subject_id, '_');
                        $student_id = substr($trainee_id_subject_id, 0, $position);

                        if ($trainee_id == $student_id) {
                            $subject_id = substr($trainee_id_subject_id, $position+1);
                            $_SESSION['selectedSubjectIdsForContact'][] = $subject_id;
                        }
                    }

                    //TO CREATE/UPDATE COURSE CONTACT TABLE FOR FEES BY MODULE VALUE
                    $modify_subject_data = 0;
                    $ccRec = $fn->getRecordByCondition('course_contact', "contact_id = '{$trainee_id}' AND order_id = '{$order_id}'");
                    if ((in_array($trainee_id, $fees_by_module_arr)) &&
                        ($ccRec['fees_by_module'] != 1)) {
                            $faCc = array();
                            $faCc['fees_by_module'] = 1;
                            $fn->saveRecord($faCc, 'course_contact', 'course_contact_id', $ccRec['course_contact_id']);
                            $modify_subject_data = 1;
                    } else if ((!in_array($trainee_id, $fees_by_module_arr)) &&
                            ($ccRec['fees_by_module'] == 1)) {
                            $faCc = array();
                            $faCc['fees_by_module'] = 0;
                            $fn->saveRecord($faCc, 'course_contact', 'course_contact_id', $ccRec['course_contact_id']);
                            $modify_subject_data = 1;
                    }

                    /* Finding whether selected subject id's are already saved in history table (when subject is removed) */
                    /* COMMENTED BY SYED
                    $selectedSubjectIds = join(',', $_SESSION['selectedSubjectIdsForContact']);

                    $modObj = getCPModuleObj('aceIms_courseLink');
                    $numRowsCcSubjHistRemove = $modObj->model->getFindNumrowsRemoveFromHistTable($selectedSubjectIds, $ccRec['course_contact_id']);

                    $sessionExplode = explode(',', $selectedSubjectIds);
                    $modObj = getCPModuleObj('aceIms_courseLink');
                    $subject_not_in_hist_table = $modObj->model->getFindNumrowsAddFromHistTable($sessionExplode, $ccRec['course_contact_id']);
                    */

                    //if (($numRowsCcSubjHistRemove > 0) ||
                        //($subject_not_in_hist_table == 1) ||
                        //($modify_subject_data == 1)) {
                        $modObj = getCPModuleObj('aceIms_courseLink');
                        $modObj->model->getDeleteOrderItem($ccRec['course_contact_id'], $order_id, $ccRec['contact_id']);
                        $modObj->model->getCreateOrderItemForCourse($ccRec['course_contact_id']);
                        //Codes added to add batch ids
                        if(is_array($batch_longterm_id_arr)){
                            $this->getDeleteBatchHistoryRecords($trainee_id, $course_id_arr[$i]);

                            foreach ($batch_longterm_id_arr AS $trainee_id_batch_id) {
                                $position1   = strpos($trainee_id_batch_id, '_');
                                $student_id = substr($trainee_id_batch_id, 0, $position1);

                                $position2   = strpos($trainee_id_batch_id, '_', $position1);
                                if ($trainee_id == $student_id) {
                                    $batch_id = substr($trainee_id_batch_id, $position1+1, $position2-1);
                                    $selectedBatchIds[] = $batch_id;

                                    //To create records in batch_history table//
                                    if ($batch_id > 0) {
                                        $fa = array();
                                        $fa['batch_id']   = $batch_id;
                                        $fa['contact_id'] = $trainee_id;
                                        $fn->addRecord($fa, 'batch_history');
                                    }
                                }
                            }
                        }

                        //$modObj->model->getDeleteOrderItem($ccRec['course_contact_id'], $ccRec['order_id'], $ccRec['contact_id']);
                        $modObj->model->getCreateOrderItemForSubject($_SESSION['selectedSubjectIdsForContact'], $ccRec['course_contact_id'], $selectedBatchIds);

                        //$modObj->model->getCreateOrderItemForSubject($_SESSION['selectedSubjectIdsForContact'], $ccRec['course_contact_id']);

                        if ($ccRec['subsidy_discount_type'] == 'Subsidy' && $ccRec['subsidy_discount_id'] > 0) {
                            $this->getAddSubsidyDiscountFeesInOrderItemForLongTermCompany($ccRec['course_contact_id'] , 'Subsidy');
                        } else if ($ccRec['subsidy_discount_type'] == 'Discount' && $ccRec['subsidy_discount_id'] > 0) {
                            $this->getAddSubsidyDiscountFeesInOrderItemForLongTermCompany($ccRec['course_contact_id'] , 'Discount');
                        }

                        $modObj->model->getUpdateOrderItemInvoiceToNull($order_id);
                    //}

                }
            }

            $expCourseContact = array('condn' => " AND contact_id = $trainee_id");
            $courseContactRec = $fn->getRecordRowByID('course_contact', 'order_id', $order_id, $expCourseContact);
            if ($courseRec['course_type'] != 'Long Term') {
                $batch_id         = $batch_id_arr[$i];
            } else {
                $batch_id = '';
            }

            /*if ($courseRec['course_type'] == 'Short Term') {
                $fa = array();
                $fa['batch_id']   = $batch_id;
                $fa['contact_id'] = $trainee_id;
                $fn->addRecord($fa, 'batch_history');
            }*/

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

                    $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $trainee_id);
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
                            $recCount = $fn->getRecordCount('order_item', "contact_id = '{$trainee_id}' AND order_id = '{$order_id}' AND module = 'aceIms_subject'");
                            if ($recCount) {
                                $sqlOi = "
                                SELECT SUM(unit_price) AS total_amt_payable
                                FROM order_item
                                WHERE module = 'aceIms_subject'
                                  AND contact_id = '{$ccRec['contact_id']}'
                                  AND order_id = '{$ccRec['order_id']}'
                                ";
                                $resultOi = $db->sql_query($sqlOi);
                                $rowOi = $db->sql_fetchrow($resultOi);
                                $fees  = $rowOi['total_amt_payable'];
                            } else {
                                $fees = $courseRec['price'];
                            }
                            $subsidyTotal = ($fees*$rowSubsidy['value'])/100;
                        }

                        $item_title = $rowSubsidy['title'];
                        $unit_price = -$subsidyTotal;

                        /* Updating Order Item record or inserting a new order item record */
                        $sqlOi = "
                        SELECT order_item_id
                          FROM order_item
                        WHERE contact_id = {$trainee_id}
                          AND order_id = {$order_id}
                          AND module = 'aceIms_subsidy'
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
                              AND module = 'aceIms_subsidy'
                              AND contact_name = '{$contactRec['first_name']}'
                            ";
                            $resultOiUpdate = $db->sql_query($sqlOiUpdate);
                        } else {
                            $fa = array();
                            $fa['order_id']   = $order_id;
                            $fa['module']     = 'aceIms_subsidy';
                            $fa['record_id']  = $ccRec['course_id'];
                            $fa['qty']        = 1;
                            $fa['item_title'] = $item_title;
                            $fa['unit_price'] = $unit_price;
                            $fa['contact_id'] = $trainee_id;
                            $fa['contact_name'] = $contactRec['first_name'];
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
                          AND module = 'aceIms_subsidy'
                          AND contact_name = '{$contactRec['first_name']}'
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
                    $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $trainee_id);

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
                            $recCount = $fn->getRecordCount('order_item', "contact_id = '{$trainee_id}' AND order_id = '{$order_id}' AND module = 'aceIms_subject'");
                            if ($recCount) {
                                $sqlOi = "
                                SELECT SUM(unit_price) AS total_amt_payable
                                FROM order_item
                                WHERE module = 'aceIms_subject'
                                  AND contact_id = '{$ccRec['contact_id']}'
                                  AND order_id = '{$ccRec['order_id']}'
                                ";
                                $resultOi = $db->sql_query($sqlOi);
                                $rowOi = $db->sql_fetchrow($resultOi);
                                $fees  = $rowOi['total_amt_payable'];
                            } else {
                                $fees = $courseRec['price'];
                            }
                            $discTotal = ($fees*$rowDisc['value'])/100;
                        }

                        $item_title = $rowDisc['title'];
                        $unit_price = -$discTotal;

                        /* Updating Order Item record or inserting a new order item record */
                        $sqlOi = "
                        SELECT order_item_id
                          FROM order_item
                        WHERE contact_id = {$trainee_id}
                          AND order_id = {$order_id}
                          AND module = 'aceIms_discount'
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
                              AND module = 'aceIms_discount'
                              AND contact_name = '{$contactRec['first_name']}'
                            ";
                            $resultOiUpdate = $db->sql_query($sqlOiUpdate);
                        } else {
                            $fa = array();
                            $fa['order_id']   = $order_id;
                            $fa['module']     = 'aceIms_discount';
                            $fa['record_id']  = $ccRec['course_id'];
                            $fa['qty']        = 1;
                            $fa['item_title'] = $item_title;
                            $fa['unit_price'] = $unit_price;
                            $fa['contact_id'] = $trainee_id;
                            $fa['contact_name'] = $contactRec['first_name'];
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
                          AND module = 'aceIms_discount'
                          AND contact_name = '{$contactRec['first_name']}'
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
     *
     */
    function getDeleteBatchHistoryRecords($trainee_id, $course_id) {
        $db = Zend_Registry::get('db');

        $SQLBatch  = "
        SELECT b.*
        FROM batch b
        WHERE b.course_id = {$course_id}
        ";
        $resultBatch = $db->sql_query($SQLBatch);
        while ($row = $db->sql_fetchrow($resultBatch)) {
            $sql = "
            DELETE FROM `batch_history`
            WHERE batch_id = {$row['batch_id']}
              AND contact_id = {$trainee_id}
            ";
            $result = $db->sql_query($sql);
        }
    }

    /**
    */
    function getSaveOLd() {
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

        $order_id = $fn->getPostParam('order_id', '', true);

        $course_id      = $fn->getPostParam('course_id');
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
            $batch_id   = $batch_id_arr[$i];
            $subsidy_id = $subsidy_id_arr[$i];
            //$course_contact_id = $course_contact_arr[$i];
            $discount_id = $discount_id_arr[$i];

            $expCourseContact = array('condn' => "
            AND course_id      =  $course_id
            AND contact_id  = $trainee_id
            ");

            $courseContactRec   = $fn->getRecordRowByID('course_contact', 'order_id', $order_id, $expCourseContact);

            $course_contact_id = $courseContactRec['course_contact_id'];

            $expOrderItemCourse = array('condn' => "AND record_id = $course_id
            AND module      = 'aceIms_course'
            AND contact_id  = $trainee_id
            ");

            $expOrderItemSubsidy = array('condn' => "AND record_id = $course_id
            AND module      = 'aceIms_subsidy'
            AND contact_id  = $trainee_id
            ");

            $expOrderItemDiscount = array('condn' => "AND record_id = $course_id
            AND module      = 'aceIms_discount'
            AND contact_id  = $trainee_id
            ");

            // To update record in course contact item if batch/subsidy is changed
            if ($course_contact_id > 0){
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
                        $fa['module']     = 'aceIms_subsidy';
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
                        $fa['module']     = 'aceIms_discount';
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
                    $fa['module']     = 'aceIms_course';
                    $fa['record_id']  = $course_id;
                    $fa['contact_id'] = $trainee_id;
                    $fa['qty']        = 1;
                    $fa['item_title'] = $courseRec['title'];
                    $fa['unit_price'] = $courseRec['price'];
                    $fa['course_type'] = $courseRec['course_type'];
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
                        $fa['module']     = 'aceIms_subsidy';
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
                        $fa['module']     = 'aceIms_discount';
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
    */
    function getSaveBulkParentStudentSubmit() {
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

        $parent_id= $fn->getPostParam('parent_id');
        $order_id = $fn->getPostParam('order_id', '', true);

        $course_id_arr  = $fn->getPostParam('course_id_row', array());
        $trainee_id_arr = $_SESSION['selectedContactIds'];
        $level_id_arr   = $fn->getPostParam('level_id', array());
        $batch_id_arr   = $fn->getPostParam('batch_id', array());
        $subsidy_id_arr = $fn->getPostParam('course_subsidy_history_id', array());

        $count = count($trainee_id_arr);
        for ($i= 0; $i< $count; $i++){
            if(count($trainee_id_arr)){
                $trainee_id = $trainee_id_arr[$i];
            } else {
                $trainee_id = '';
                continue;
            }

            $pfx = $trainee_id . '_' ;
            $add_reg_fee  = $fn->getPostParam("{$pfx}add_reg_fee");
            $course_id  = $course_id_arr[$i];
            $level_id   = $level_id_arr[$i];
            $batch_id   = $batch_id_arr[$i];
            $subsidy_id = $subsidy_id_arr[$i];
            //$course_contact_id = $course_contact_arr[$i];
            //$discount_id = $discount_id_arr[$i];

            $expCourseContact = array('condn' => "
            AND contact_id  = $trainee_id
            ");

            $courseContactRec   = $fn->getRecordRowByID('course_contact', 'order_id', $order_id, $expCourseContact);

            $course_contact_id = $courseContactRec['course_contact_id'];

            $expOrderItemCourse = array('condn' => "
            AND module      = 'aceIms_course'
            AND contact_id  = $trainee_id
            ");

            $expOrderItem = array('condn' => "
            AND module      = 'aceIms_course'
            AND contact_id  = $trainee_id
            ");

            $expOrderItemSubsidy = array('condn' => "
            AND module      = 'aceIms_subsidy'
            AND contact_id  = $trainee_id
            ");

            $expOrderItemDiscount = array('condn' => "
            AND module      = 'aceIms_discount'
            AND contact_id  = $trainee_id
            ");

            // To update record in course contact item if batch/subsidy is changed
            if ($course_contact_id > 0) {
                $fa = array();
                if ($add_reg_fee  == 'Yes') {
                    $fa['add_registration_fee'] = 1;
                } else if($add_reg_fee  == 'No') {
                    $fa['add_registration_fee'] = 0;
                }

                $fa['course_id']                 = $course_id;
                $fa['level_id']                  = $level_id;
                $fa['batch_id']                  = $batch_id;
                $fa['course_subsidy_history_id'] = $subsidy_id;
                $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id);

                $courseRec      = $fn->getRecordRowByID('course', 'course_id', $course_id);
                $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItem);

                // creates order item record for registration fee
                if ($add_reg_fee  == 'No') {
                    $expOrderItemReg = array('condn' => "
                    AND module      = 'aceIms_reg_fee'
                    AND contact_id  = $trainee_id
                    ");
                    //to check if there is already reg record created or not in order item
                    $orderItemRegRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemReg);
                    if (is_array($orderItemRegRec)) {
                        $deleteSQL = "
                        DELETE FROM order_item
                        WHERE order_item_id = {$orderItemRegRec['order_item_id']}
                        ";
                        $resultDelete  = $db->sql_query($deleteSQL);
                    }

                    $expInvoiceReg = array('condn' => "
                    AND add_registration_fee = 1
                    AND contact_id  = $trainee_id
                    ");

                    $invoiceRegRec   = $fn->getRecordRowByID('invoice', 'order_id', $order_id, $expInvoiceReg);
                    if (is_array($invoiceRegRec)) {
                        $deleteSQL = "
                        DELETE FROM invoice
                        WHERE invoice_id = {$invoiceRegRec['invoice_id']}
                        ";
                        $resultDelete  = $db->sql_query($deleteSQL);
                    }
                }
                else if ($add_reg_fee  == 'Yes') {
                    $expOrderItemReg = array('condn' => "
                    AND module      = 'aceIms_reg_fee'
                    AND contact_id  = $trainee_id
                    ");
                    //to check if there is already reg record created or not in order item
                    $orderItemRegRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemReg);
                    if (!is_array($orderItemRegRec)){
                        $fa = array();
                        $fa['order_id']   = $order_id;
                        $fa['record_id']  = $course_id;
                        $fa['qty']        = 1;
                        $fa['unit_price'] = $fn->getSettingsValueByKey("registrationFeeEnt");
                        $fa['item_title'] = 'Registration Fee';
                        $fa['module']     = 'aceIms_reg_fee';
                        $fa['contact_id'] = $trainee_id;
                        $fa['add_registration_fee'] = 1;
                        $fn->addRecord($fa, 'order_item');
                    }
                }

                // to update new order item,
                $fa = array();
                $fa['order_id']   = $order_id;
                $fa['module']     = 'aceIms_course';
                $fa['record_id']  = $course_id;
                $fa['contact_id'] = $trainee_id;
                $fa['qty']        = 1;
                $fa['item_title'] = $courseRec['title'];
                $fa['unit_price'] = $courseRec['price'];
                $fa['course_type'] = $courseRec['course_type'];
                $fn->saveRecord($fa, 'order_item', 'order_item_id', $orderItemRec['order_item_id']);

                // to create new subsidy/discount, if new subsidy is created or update the existing subsidy

                $orderItemRecSubsidy = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemSubsidy);
                $discountRec         = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemDiscount);

                if($subsidy_id) {
                    $subsidySql = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                    WHERE csh.course_subsidy_history_id = {$subsidy_id}
                    ";
                    $resultsubsidy  = $db->sql_query($subsidySql);
                    $rowsubsidy     = $db->sql_fetchrow($resultsubsidy);

                    if($rowsubsidy['mode_of_calculation'] == '%'){
                        $rowsubsidy['value'] = $courseRec['price'] * $rowsubsidy['value'] / 100;
                    }

                    if (!is_array($orderItemRecSubsidy)) {
                        $fa = array();
                        $fa['order_id']   = $order_id;
                        $fa['module']     = 'aceIms_subsidy';
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
                //to delete the record in order item
                else {
                    $deleteSQL = "
                    DELETE FROM order_item
                    WHERE module = 'aceIms_subsidy' AND contact_id = {$trainee_id}
                    ";
                    $resultDelete  = $db->sql_query($deleteSQL);
                }
            }
            // To add record in course contact/order_item item if new trainee/course is added
            else {
                if ($course_id > 0) {
                    $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
                    $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);

                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'aceIms_course';
                    $fa['record_id']  = $course_id;
                    $fa['contact_id'] = $trainee_id;
                    $fa['qty']        = 1;
                    $fa['item_title'] = $courseRec['title'];
                    $fa['unit_price'] = $courseRec['price'];
                    $fa['course_type'] = $courseRec['course_type'];
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
                        $fa['module']     = 'aceIms_subsidy';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        $fa['item_title'] = $rowsubsidy['title'];
                        $fa['unit_price'] = -$rowsubsidy['value'];
                        $fn->addRecord($fa, 'order_item');
                    }

                    $fa = array();
                    $fa['order_id']         = $order_id;
                    $fa['course_id']        = $course_id;
                    $fa['parent_id']        = $parent_id;
                    $fa['level_id']         = $level_id;
                    $fa['batch_id']         = $batch_id;
                    $fa['contact_id']       = $trainee_id;
                    $fa['course_subsidy_history_id']= $subsidy_id;
                    $fa['year_of_enrollment'] = $orderRec['year_of_enrollment'];

                    $fn->addRecord($fa, 'course_contact');
                    $recCount++;
                }
            }

        }
       return $validate->getSuccessMessageXML();
    }

    /**
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
            $IdCardlink = $fn->getRecordDetailLink('aceIms_contact', 'record_id', $rec['contact_id'], $expIdCard);

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
    function getInsertEnrollmentRecordsForTrainee($company_id, $trainee_id, $order_id, $course_id, $batch_id, $subsidy_id, $discount_id, $enrollment_year) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /********************************** STEP 2B *****************************/
        $fa = array();
        $fa['order_id']                 = $order_id;
        $fa['course_id']                = $course_id;
        $fa['company_id']               = $company_id;
        $fa['contact_id']               = $trainee_id;
        $fa['batch_id']                 = $batch_id;
        $fa['year_of_enrollment']       = $enrollment_year;

        if ($subsidy_id > 0) {
            $fa['subsidy_discount_type'] = 'Subsidy';
            $fa['subsidy_discount_id']   = $subsidy_id;
        } else if ($discount_id > 0) {
            $fa['subsidy_discount_type'] = 'Discount';
            $fa['subsidy_discount_id']   = $discount_id;
        }

        $id    = $fn->addRecord($fa, 'course_contact');
        $ccRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $id);
        /********************************** STEP 2B ENDS HERE *******************/
        /********************************** STEP 2C *****************************/
        $modObj = getCPModuleObj('aceIms_courseLink');
        $modObj->model->getCreateOrderItemForCourse($ccRec['course_contact_id']);
        /********************************** STEP 2C ENDS HERE *******************/
        /********************************** STEP 2D *****************************/
        if ($subsidy_id > 0) {
            $modObj = getCPModuleObj('aceIms_courseLink');
            $modObj->model->getAddSubsidyFeesInOrderItem($ccRec['course_contact_id'], '');
        }
        /********************************** STEP 2D ENDS HERE *******************/
        /********************************** STEP 2E *****************************/
        if ($discount_id > 0) {
            $modObj = getCPModuleObj('aceIms_courseLink');
            $modObj->model->getAddDiscountFeesInOrderItem($ccRec['course_contact_id'], '');
        }
        /********************************** STEP 2E ENDS HERE *******************/
    }

    /**
     * STEP 2B: CREATING A COURSE CONTACT RECORD FOR THE ORDER (ONE COURSE CONTACT RECEORD FOR EACH STUDENT)
     * STEP 2C: CREATING A ORDER ITEM FOR COURSE FOR THE ORDER
     * STEP 2D: CREATING A ORDER ITEM FOR SUBSIDY FOR THE ORDER
     * STEP 2E: CREATING A ORDER ITEM FOR DISCOUNT FOR THE ORDER
     */
    function getInsertLongTermEnrollmentRecordForTrainee($company_id, $trainee_id, $order_id, $course_id, $batch_id, $subsidy_id, $discount_id, $course_type, $fees_by_module, $trainee_id_subject_id_arr, $enrollment_year, $batch_longterm_id_arr) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /********************************** STEP 2B *****************************/
        $fa = array();
        $fa['registration_type']        = $course_type;
        $fa['fees_by_module']           = $fees_by_module;
        $fa['order_id']                 = $order_id;
        $fa['course_id']                = $course_id;
        $fa['company_id']               = $company_id;
        $fa['contact_id']               = $trainee_id;
        $fa['batch_id']                 = $batch_id;
        $fa['year_of_enrollment']       = $enrollment_year;

        if ($subsidy_id > 0) {
            $fa['subsidy_discount_type'] = 'Subsidy';
            $fa['subsidy_discount_id']   = $subsidy_id;
        } else if ($discount_id > 0) {
            $fa['subsidy_discount_type'] = 'Discount';
            $fa['subsidy_discount_id']   = $discount_id;
        }

        $id    = $fn->addRecord($fa, 'course_contact');
        $ccRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $id);
        /********************************** STEP 2B ENDS HERE *******************/
        /********************************** STEP 2C *****************************/
        $modObj = getCPModuleObj('aceIms_courseLink');
        $modObj->model->getCreateOrderItemForCourse($ccRec['course_contact_id']);

        /* Creating Order Item for Subjects */
        $_SESSION['selectedSubjectIds'] = array();
        $count = count($trainee_id_subject_id_arr);
        $selectedBatchIds = array();

        if ($count) {
            foreach ($trainee_id_subject_id_arr AS $trainee_id_subject_id) {
                $position   = strpos($trainee_id_subject_id, '_');
                $student_id = substr($trainee_id_subject_id, 0, $position);

                if ($trainee_id == $student_id) {
                    $subject_id = substr($trainee_id_subject_id, $position+1);
                    $_SESSION['selectedSubjectIds'][] = $subject_id;
                }
            }

            foreach ($batch_longterm_id_arr AS $trainee_id_batch_id) {
                $position1   = strpos($trainee_id_batch_id, '_');
                $student_id = substr($trainee_id_batch_id, 0, $position1);

                $position2   = strpos($trainee_id_batch_id, '_', $position1);
                if ($trainee_id == $student_id) {
                    $batch_id = substr($trainee_id_batch_id, $position1+1, $position2-1);
                    $selectedBatchIds[] = $batch_id;
                }
            }

            $modObj = getCPModuleObj('aceIms_courseLink');
            $modObj->model->getCreateOrderItemForSubject($_SESSION['selectedSubjectIds'], $ccRec['course_contact_id'], $selectedBatchIds);
        }
        /********************************** STEP 2C ENDS HERE *******************/
        /********************************** STEP 2D *****************************/
        if ($subsidy_id > 0) {
            $modObj = getCPModuleObj('aceIms_courseLink');
            $modObj->model->getAddSubsidyFeesInOrderItem($ccRec['course_contact_id'], '');
        }
        /********************************** STEP 2D ENDS HERE *******************/
        /********************************** STEP 2E *****************************/
        if ($discount_id > 0) {
            $modObj = getCPModuleObj('aceIms_courseLink');
            $modObj->model->getAddDiscountFeesInOrderItem($ccRec['course_contact_id'], '');
        }
        /********************************** STEP 2E ENDS HERE *******************/
    }

    /**
     *STEP 1: INSERTING RECORD IN ORDER ITEM TABLE
     *STEP 2: UPDATING REG FEE VALUE IN COURSE CONTACT TABLE
     */
    function getInsertOrderItemRecForRegFees($order_id, $trainee_id, $course_id) {
        $fn = Zend_Registry::get('fn');

        /********************************** STEP 1 **************************************/
        $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $trainee_id);

        $fa = array();
        $fa['order_id']   = $order_id;
        $fa['module']     = 'aceIms_registration';
        $fa['record_id']  = $course_id;
        $fa['qty']        = 1;
        $fa['item_title'] = 'Registration Fee';
        $fa['unit_price'] = $fn->getSettingsValueByKey("registrationFeePvt");;
        $fa['contact_id'] = $trainee_id;
        $fa['contact_name'] = $contactRec['first_name'];
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
    function getCreateReceiptRecords($order_id, $invoice_id) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        /* Total amount from Order Item */
        $modObj = getCPModuleObj('aceIms_order');
        $total_amount_payable = $modObj->model->getTotalAmountFromOrderItem($order_id);

        $modObj = getCPModuleObj('aceIms_receipt');
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
     * Add Subsidy/Discount Fee in Order Item for long term company edit enrollment
     */
    function getAddSubsidyDiscountFeesInOrderItemForLongTermCompany($course_contact_id, $subsidy_discount_val) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $ccRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);

        $sqlOi = "
        SELECT SUM(unit_price) AS total_amt_payable
        FROM order_item
        WHERE module = 'aceIms_subject'
          AND contact_id = '{$ccRec['contact_id']}'
          AND order_id = '{$ccRec['order_id']}'
        ";
        $resultOi = $db->sql_query($sqlOi);
        $rowOi = $db->sql_fetchrow($resultOi);
        $total_amt_payable = $rowOi['total_amt_payable'];

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
            $subsidyTotal = ($total_amt_payable*$rowSubsidy['value'])/100;
        }

        if ($subsidy_discount_val == 'Subsidy') {
            $module = 'aceIms_subsidy';
        } else {
            $module = 'aceIms_discount';
        }

        $recCount = $fn->getRecordCount('order_item', "contact_id = '{$ccRec['contact_id']}' AND order_id = '{$ccRec['order_id']}' AND module = '{$module}'");
        if ($recCount) {
            $sqlOiUpdate = "
            UPDATE order_item
            SET item_title = '{$rowSubsidy['title']}'
               ,unit_price = '{$subsidyTotal}'
            WHERE contact_id = {$trainee_id}
              AND order_id = {$order_id}
              AND module = '{$module}'
            ";
            $resultOiUpdate = $db->sql_query($sqlOiUpdate);
        } else {
            $fa = array();
            $fa['order_id']   = $ccRec['order_id'];
            $fa['module']     = $module;
            $fa['record_id']  = $ccRec['course_id'];
            $fa['qty']        = 1;
            $fa['item_title'] = $rowSubsidy['title'];
            $fa['unit_price'] = -$subsidyTotal;
            $fa['contact_id'] = $ccRec['contact_id'];
            $fn->addRecord($fa, 'order_item');
        }
    }
}