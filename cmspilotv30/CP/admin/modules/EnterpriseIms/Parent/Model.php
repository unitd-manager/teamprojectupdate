<?
class CP_Admin_Modules_EnterpriseIms_Parent_Model extends CP_Common_Modules_EnterpriseIms_Parent_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        $validate->validateData('last_name' , 'Please enter the last name');
        $validate->validateData('email' , 'Please enter a valid email address', 'email');
        $validate->validateData('id_card_no' , 'Please enter the id card no.');

        $email = $fn->getPostParam('email', '', true);
        $rec = $fn->getRecordByCondition('parent', "email = '{$email}'");
        if (is_array($rec)){
            $validate->errorArray['email']['name'] = "email";
            $validate->errorArray['email']['msg']  = "Email already exists.";
        }

        $id_card_no = $fn->getPostParam('id_card_no', '', true);
        $rec = $fn->getRecordByCondition('parent', "id_card_no = '{$id_card_no}'");
        if (is_array($rec)){
            $validate->errorArray['id_card_no']['name'] = "id_card_no";
            $validate->errorArray['id_card_no']['msg']  = "Id card number already exists.";
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
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $mode_of_payment = $fn->getReqparam('mode_of_payment');

        $validate->resetErrorArray();
        $validate->validateData('mode_of_payment' , 'Please choose mode of payment');
        
        if ($mode_of_payment == 'Giro') {
            $validate->validateData('bank_name' , 'Please enter Name of the Bank');
            $validate->validateData('bank_code' , 'Please enter Bank Code');
            $validate->validateData('account_name' , 'Please enter Account Name');
            $validate->validateData('branch' , 'Please enter Branch Code');
            $validate->validateData('account_no' , 'Please enter Account No');
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
        $cpCfg  = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if ($cpCfg['cp.hasPasswordSalt']) {
            $pass_word = $fa['pass_word'];
            $email = $fa['email'];
            if ($pass_word != '') {
                $arr = $cpUtil->getSaltAndPasswordArray($email, $pass_word);
                $fa['salt'] = $arr['salt'];
                $fa['pass_word'] = $arr['pass_word'];
            } else {
                //remove pass_word field from the fields array
                unset($fa['pass_word']);
            }
        }
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
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
        $fa = $fn->addToFieldsArray($fa, 'phone_direct');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile');

        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'department');

        $fa = $fn->addToFieldsArray($fa, 'address_flat');
        $fa = $fn->addToFieldsArray($fa, 'address_street');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'address_country');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');

        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'marital_status');
        $fa = $fn->addToFieldsArray($fa, 'id_card_no');
        $fa = $fn->addToFieldsArray($fa, 'nationality');
        $fa = $fn->addToFieldsArray($fa, 'race');
        $fa = $fn->addToFieldsArray($fa, 'date_of_birth');
        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_name');
        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_mobile');
        $fa = $fn->addToFieldsArray($fa, 'emergency_contact_office_no');
        $fa = $fn->addToFieldsArray($fa, 'school_name');
        $fa = $fn->addToFieldsArray($fa, 'school_country');
        $fa = $fn->addToFieldsArray($fa, 'school_from');
        $fa = $fn->addToFieldsArray($fa, 'school_to');
        $fa = $fn->addToFieldsArray($fa, 'school_highest_qual');
        $fa = $fn->addToFieldsArray($fa, 'company_name');
        $fa = $fn->addToFieldsArray($fa, 'position');
        $fa = $fn->addToFieldsArray($fa, 'company_phone');
        $fa = $fn->addToFieldsArray($fa, 'company_roc_no');
        $fa = $fn->addToFieldsArray($fa, 'company_address');
        $fa = $fn->addToFieldsArray($fa, 'company_po_code');
        $fa = $fn->addToFieldsArray($fa, 'company_fax');
        $fa = $fn->addToFieldsArray($fa, 'yr_of_exp');
        $fa = $fn->addToFieldsArray($fa, 'salary_range');
        $fa = $fn->addToFieldsArray($fa, 'apply_for_sdf');

        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'pass_word');
        $fa = $fn->addToFieldsArray($fa, 'notes');
        $fa = $fn->addToFieldsArray($fa, 'company_id');

        return $fa;
    }

    /**
     *
     */
    function getImportData(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $phpExcel = includeCPClass('Lib', 'PhpExcelImportWrapper', 'PhpExcelImportWrapper');
        $fa = array(
              'first_name'           => $phpExcel->getImportFldObj('First Name')
             ,'last_name'            => $phpExcel->getImportFldObj('Last Name')
             ,'email'                => $phpExcel->getImportFldObj('Email')
             ,'phone_direct'         => $phpExcel->getImportFldObj('Phone')
             ,'mobile'               => $phpExcel->getImportFldObj('Mobile')
             ,'address_flat'         => $phpExcel->getImportFldObj('Address 1')
             ,'address_street'       => $phpExcel->getImportFldObj('Address 2')
             ,'address_city'         => $phpExcel->getImportFldObj('City')
             ,'address_state'        => $phpExcel->getImportFldObj('State')
             ,'address_po_code'      => $phpExcel->getImportFldObj('Zip Code')
             ,'address_country' => $phpExcel->getImportFldObj('Country')
        );

        $fa['address_country']['specialType'] = 'geo_country';

        $config = array(
             'module'          => 'enterpriseIms_parent'
            ,'matchFieldArr'   => array('email')
            ,'fldsArr'         => $fa
        );

        return $phpExcel->importData($config);
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');

        $mode_of_payment            = $fn->getReqParam('mode_of_payment');
        $parent_id                  = $fn->getReqParam('parent_id');
        $subscribe                  = $fn->getReqParam('subscribe');
        $special_search             = $fn->getReqParam('special_search');
        $continuing_to_next_year    = $fn->getReqParam('continuing_to_next_year');
        $enrollment_year 		    = $fn->getReqParam('enrollment_year');
        $course_id       		    = $fn->getReqParam('course_id');
        $student_status          	= $fn->getReqParam('student_status');

        $searchVar->mainTableAlias = 'pc';

        // to check if the record exists in parent_contact
        // if no then hide the search var condition, in edit mode
        
        //$recParentContact = $fn->getRecordRowById('parent_contact', 'parent_id', $parent_id);
        //if ($tv['action'] == 'edit' && is_array($recParentContact)) {
        //    $searchVar->mainTableAlias = 'pc';
        //} else  if ($tv['action'] == 'list') {
        //    $searchVar->mainTableAlias = 'pc';
        //}

        if ($enrollment_year == '') {
            $enrollment_year = date('Y');
        }

        if ($parent_id != "") {
            $searchVar->sqlSearchVar[] = "p.parent_id = '{$parent_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "p.parent_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'p.parent_id');

            //------------------------------------------------------------------------//
            if ($tv['special_search'] == "Subscribed") {
                $searchVar->sqlSearchVar[] = "p.subscribe = 1";
            }

            if ($tv['special_search'] == "Not-Subscribed") {
                $searchVar->sqlSearchVar[] = "(p.subscribe != 1 OR p.subscribe IS null)";
            }

            if ($tv['special_search'] == "Flagged") {
                $searchVar->sqlSearchVar[] = "p.flag = 1";
            }

            if ($tv['special_search'] == "Not-Flagged") {
                $searchVar->sqlSearchVar[] = "(p.flag != 1 OR p.flag IS null)";
            }

            if ($tv['special_search']  == 'Published') {
                $searchVar->sqlSearchVar[] = "p.published = 1";
            }

            if ($tv['special_search'] == 'Not-Published' ) {
                $searchVar->sqlSearchVar[] = "p.published = 0 OR p.published IS NULL OR p.published = ''";
            }

            if ($mode_of_payment != '' ) {
                $searchVar->sqlSearchVar[] = "p.mode_of_payment = '{$mode_of_payment}'";
            }

            if ($course_id != '' ) {
                $searchVar->sqlSearchVar[] = "cc.course_id = {$course_id}";
            }

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       p.first_name      LIKE '%{$tv['keyword']}%'
                    OR p.last_name       LIKE '%{$tv['keyword']}%'
                    OR p.email           LIKE '%{$tv['keyword']}%'
                    OR p.dda             LIKE '%{$tv['keyword']}%'
                    OR p.id_card_no      LIKE '%{$tv['keyword']}%'
                    OR p.phone           LIKE '%{$tv['keyword']}%'
                    OR p.mobile          LIKE '%{$tv['keyword']}%'
                    OR p.address_flat    LIKE '%{$tv['keyword']}%'
                    OR p.address_street  LIKE '%{$tv['keyword']}%'
                    OR p.address_po_code LIKE '%{$tv['keyword']}%'
                )";

                //$searchVar->sqlSearchVar[] = "cc.year_of_enrollment = '{$enrollment_year}' OR cc.year_of_enrollment IS NULL";
            } else {
                if ($student_status != '' ) {
                    $searchVar->sqlSearchVar[] = "c.status = '{$student_status}'";
                    
                    if ($student_status == 'Graduated') {
                        $searchVar->sqlSearchVar[] = "c.graduation_year = '{$enrollment_year}'";
                    } else {
                        $searchVar->sqlSearchVar[] = "cc.year_of_enrollment = '{$enrollment_year}'";
                    }
                } else {
                    $searchVar->sqlSearchVar[] = "c.status = 'Active'";
              	}

                if ($enrollment_year != '') {
                    $searchVar->sqlSearchVar[] = "cc.year_of_enrollment = '{$enrollment_year}'";
                }
            }

	        if ($continuing_to_next_year != "") {
		        if ($continuing_to_next_year == "Yes") {
		            $searchVar->sqlSearchVar[] = "c.continuing_to_next_year = 1 AND c.status = 'Active'";
	        	} else if ($continuing_to_next_year == "No"){
		            $searchVar->sqlSearchVar[] = "c.continuing_to_next_year = 0 AND c.status = 'Active'";
	        	}	
	        }

            if ($tv['spAction'] == 'link' && $tv['module'] == 'broadcast' ){
                $searchVar->sqlSearchVar[] = "p.subscribe = 1";
            }

            $searchVar->sortOrder = "p.parent_code";
        }
    }

    /**
     *
     */
	function getEnterpriseImsParentEnterpriseImsContactLinkSQL($id) {

    return "
    SELECT c.contact_id
          ,CONCAT_WS(' ', c.first_name, c.last_name ) as name
          ,DATE_FORMAT(c.date_of_birth, '%d-%m-%Y') AS date_of_birth
          ,c.age
          ,c.gender
          ,c.registration_no
    FROM contact c 
    LEFT JOIN parent_contact pc ON (c.contact_id = pc.contact_id)
    WHERE pc.parent_id = '{$id}'
    ";

    }

    /**
     * Submit functionality for Parent Transfer form
     */
    function getParentTransferFormSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        
        if (!$this->getParentTransferFormValidate()){
            return $validate->getErrorMessageXML();
        }

        $site_id         = $fn->getPostParam('site_id');
        $parent_id       = $fn->getReqParam('parent_id');
        $contact_id_arr  = $fn->getPostParam('contact_ids', array());
        
        foreach ($contact_id_arr AS $contact_id) {
            /* Updating Site id in Parent Contact table */
            $faPar = array();
            $faPar['site_id']           = $site_id;
            $faPar['modification_date'] = date("Y-m-d H:i:s");
            $faPar['modified_by']       = $fn->getSessionParam('userName');
            
            $wherePar   = "WHERE parent_id = {$parent_id} AND contact_id = {$contact_id}";
            $sqlPar     = $dbUtil->getUpdateSQLStringFromArray($faPar, 'parent_contact', $wherePar);
            $resultPar  = $db->sql_query($sqlPar);
            
            /* Updating Site id in Contact table */
            $faCont = array();
            $faCont['site_id']           = $site_id;
            $faCont['modification_date'] = date("Y-m-d H:i:s");
            $faCont['modified_by']       = $fn->getSessionParam('userName');
            
            $whereCont  = "WHERE contact_id = {$contact_id}";
            $sqlCont    = $dbUtil->getUpdateSQLStringFromArray($faCont, 'contact', $whereCont);
            $resultCont = $db->sql_query($sqlCont);
            
            /* Updating Site id in Course Contact table */
            $current_year = date('Y');
            $sqlCc = "
            SELECT cc.course_contact_id FROM course_contact cc
            WHERE cc.contact_id = {$contact_id}
              AND cc.parent_id = {$parent_id}
              AND cc.year_of_enrollment >= {$current_year}
            ";
            $resultCc = $db->sql_query($sqlCc);
            while ($rowCc = $db->sql_fetchrow($resultCc)) {
                $faCc = array();
                $faCc['site_id']           = $site_id;
                $faCc['modification_date'] = date("Y-m-d H:i:s");
                $faCc['modified_by']       = $fn->getSessionParam('userName');
                
                $whereCc      = "WHERE course_contact_id = {$rowCc['course_contact_id']}";
                $sqlUpdate    = $dbUtil->getUpdateSQLStringFromArray($faCc, 'course_contact', $whereCc);
                $resultUpdate = $db->sql_query($sqlUpdate);
            }

            /* Finding whether the parent has same or different order id in course contact table */            
            $current_year = date('Y');
            $sqlCc1 = "
            SELECT cc.course_contact_id, cc.contact_id, cc.order_id
            FROM course_contact cc
            WHERE cc.parent_id = {$parent_id}
              AND cc.year_of_enrollment >= {$current_year}
            ORDER BY cc.course_contact_id ASC
            ";
            $resultCc1 = $db->sql_query($sqlCc1);
            while ($rowCc1 = $db->sql_fetchrow($resultCc1)) {
                
                /* Fetching the Course Contact Id and Order Id from Course Contact table for the selected contact */
                $sqlCc2 = "
                SELECT cc.course_contact_id, cc.contact_id, cc.order_id
                FROM course_contact cc
                WHERE cc.parent_id = {$parent_id}
                  AND cc.contact_id = {$contact_id}
                  AND cc.year_of_enrollment >= {$current_year}
                  AND cc.course_contact_id != {$rowCc1['course_contact_id']}
                ORDER BY cc.course_contact_id ASC
                ";
                $resultCc2 = $db->sql_query($sqlCc2);
                $already_updated = 0;
                while ($rowCc2 = $db->sql_fetchrow($resultCc2)) {
                    /* Creating a new Order record for same Orders for more than one student with same parent */
                    if ($rowCc1['order_id'] == $rowCc2['order_id']) {
                        
                        $already_updated = 1;

                        /* Getting Parent and Order details from Parent and related Order table */
                        $parentRec = $fn->getRecordRowByID('parent', 'parent_id', $parent_id);
                        $orderRec  = $fn->getRecordRowByID('order', 'order_id', $rowCc1['order_id']);

                        $faNewOrder = array();
                        $faNewOrder['cust_first_name']      = $orderRec['cust_first_name'];
                        $faNewOrder['cust_last_name']       = $orderRec['cust_last_name'];
                        $faNewOrder['cust_email']           = $orderRec['cust_email'];
                        $faNewOrder['cust_phone']           = $orderRec['cust_phone'];
                        $faNewOrder['cust_address1']        = $orderRec['cust_address1'];
                        $faNewOrder['cust_address_city']    = $orderRec['cust_address_city'];
                        $faNewOrder['cust_address_state']   = $orderRec['cust_address_state'];
                        $faNewOrder['cust_address_po_code'] = $orderRec['cust_address_po_code'];                
                        $faNewOrder['parent_id']            = $parent_id;
                        $faNewOrder['payment_method']       = $orderRec['payment_method'];
                        $faNewOrder['module']               = 'pms_course';
                        $faNewOrder['order_status']         = 'Due';
                        $faNewOrder['order_date']           =  $orderRec['order_date'];
                        $faNewOrder['contact_module']       = 'pms_parent';
                        $faNewOrder['year_of_enrollment']   = $orderRec['year_of_enrollment'];
                        $faNewOrder['site_id']              = $site_id;
                        
                        $new_order_id = $fn->addRecord($faNewOrder, 'order');
                        
                        /* Updating the Order Item table with Order id */
                        $faOi = array();
                        $faOi['order_id'] = $new_order_id;
                        $whereOi          = "WHERE order_id = {$rowCc2['order_id']}";
                        $sqlOiUpdate      = $dbUtil->getUpdateSQLStringFromArray($faOi, 'order_item', $whereOi);
                        $resultOiUpdate   = $db->sql_query($sqlOiUpdate);

                        /* Updating the Course Contact table with new Order id */
                        $faCc = array();
                        $faCc['order_id'] = $new_order_id;
                        $whereCc          = "WHERE course_contact_id = {$rowCc2['course_contact_id']} AND order_id = {$rowCc2['order_id']}";
                        $sqlCcUpdate      = $dbUtil->getUpdateSQLStringFromArray($faCc, 'course_contact', $whereCc);
                        $resultCcUpdate   = $db->sql_query($sqlCcUpdate);

                        /* Updating Site id in Invoice table */
                        $sqlInv = "
                        SELECT i.invoice_id FROM invoice i
                        WHERE i.contact_id = {$contact_id}
                          AND i.status = 'Due'
                        ";
                        $resultInv = $db->sql_query($sqlInv);
                        while ($rowInv = $db->sql_fetchrow($resultInv)) {
                            $faInv = array();
                            $faInv['site_id']           = $site_id;
                            $faInv['order_id']          = $new_order_id;
                            $faInv['modification_date'] = date("Y-m-d H:i:s");
                            
                            $whereInvoice  = "WHERE invoice_id = {$rowInv['invoice_id']} AND order_id = {$rowCc2['order_id']}";
                            $sqlInvoice    = $dbUtil->getUpdateSQLStringFromArray($faInv, 'invoice', $whereInvoice);
                            $resultInvoice = $db->sql_query($sqlInvoice);
                        }
                    }
                }

                if ($already_updated == 0) {
                    /* If no found set result in $sqlCc2, then Updating Site ID in Order id. */
                    
                    $sqlCcNew = "
                    SELECT cc.course_contact_id, cc.contact_id, cc.order_id
                    FROM course_contact cc
                    WHERE cc.parent_id = {$parent_id}
                      AND cc.contact_id = {$contact_id}
                      AND cc.year_of_enrollment >= {$current_year}
                    ORDER BY cc.course_contact_id ASC
                    ";
                    $resultCcNew = $db->sql_query($sqlCcNew);
                    while ($rowCcNew = $db->sql_fetchrow($resultCcNew)) {
                        
                        $faOrder = array();
                        $faOrder['site_id']           = $site_id;
                        $faOrder['modification_date'] = date("Y-m-d H:i:s");
                        $faOrder['modified_by']       = $fn->getSessionParam('userName');
                        
                        $whereOrder         = "WHERE order_id = {$rowCcNew['order_id']}";
                        $sqlOrder           = $dbUtil->getUpdateSQLStringFromArray($faOrder, 'order', $whereOrder);
                        $resultOrder        = $db->sql_query($sqlOrder);
                    }

                    /* Updating Site id in Invoice table */
                    $sqlInv = "
                    SELECT i.invoice_id FROM invoice i
                    WHERE i.contact_id = {$contact_id}
                      AND i.status = 'Due'
                    ";
                    $resultInv = $db->sql_query($sqlInv);
                    while ($rowInv = $db->sql_fetchrow($resultInv)) {
                        $faInv = array();
                        $faInv['site_id']           = $site_id;
                        $faInv['modification_date'] = date("Y-m-d H:i:s");
                        
                        $whereInvoice  = "WHERE invoice_id = {$rowInv['invoice_id']}";
                        $sqlInvoice    = $dbUtil->getUpdateSQLStringFromArray($faInv, 'invoice', $whereInvoice);
                        $resultInvoice = $db->sql_query($sqlInvoice);
                    }
                }
            }
            
            ///*************************************************************/
            //
            ///* Updating Site id in Order table */
            //$sqlOrder = "
            //SELECT o.order_id FROM `order` o
            //LEFT JOIN (course_contact cc) ON (o.order_id = cc.order_id)
            //WHERE o.parent_id = {$parent_id}
            //  AND cc.contact_id = {$contact_id}
            //  AND o.year_of_enrollment >= {$current_year}
            //";
            //$resultOrder = $db->sql_query($sqlOrder);
            //while ($rowOrder = $db->sql_fetchrow($resultOrder)) {
            //    
            //    $faOrder = array();
            //    $faOrder['site_id']           = $site_id;
            //    $faOrder['modification_date'] = date("Y-m-d H:i:s");
            //    $faOrder['modified_by']       = $fn->getSessionParam('userName');
            //    
            //    $whereOrder         = "WHERE order_id = {$rowOrder['order_id']} AND parent_id = {$parent_id} AND year_of_enrollment >= {$current_year}";
            //    $sqlOrderUpdate     = $dbUtil->getUpdateSQLStringFromArray($faOrder, 'order', $whereOrder);
            //    $resultOrderUpdate  = $db->sql_query($sqlOrderUpdate);
            //
            //    /* Updating Site id in Invoice table */
            //    $sqlInv = "
            //    SELECT i.invoice_id FROM invoice i
            //    WHERE i.order_id = {$rowOrder['order_id']}
            //      AND i.status = 'Due'
            //    ";
            //    $resultInv = $db->sql_query($sqlInv);
            //    while ($rowInv = $db->sql_fetchrow($resultInv)) {
            //        $faInv = array();
            //        $faInv['site_id']           = $site_id;
            //        $faInv['modification_date'] = date("Y-m-d H:i:s");
            //        
            //        $whereInvoice  = "WHERE invoice_id = {$rowInv['invoice_id']}";
            //        $sqlInvoice    = $dbUtil->getUpdateSQLStringFromArray($faInv, 'invoice', $whereInvoice);
            //        $resultInvoice = $db->sql_query($sqlInvoice);
            //    }
            //}
        }
                
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getParentTransferFormValidate() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $contact_id_arr = $fn->getPostParam('contact_ids', array());

        $validate->resetErrorArray();
        $validate->validateData('site_id' , 'Please choose the branch');

        if(count($contact_id_arr) == 0){
            $validate->validateData('contact_ids' , 'Please check atleast one student for transfer');
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Export Parent Name and their Address
     */
    function getExportData($dataArray){
        $fn = Zend_Registry::get('fn');
        $exportType = $fn->getReqParam('exportType');

        if ($exportType == 'printMobileNo'){
            return $this->getParentExportMobile($dataArray);
        } else {
            $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

            $fa = array(
                  'first_name'      => $phpExcel->getFldObj('Parent Name')
                 ,'address_flat'    => $phpExcel->getFldObj('Address 1')
                 ,'address_street'  => $phpExcel->getFldObj('Address 2')
                 ,'country_name'    => $phpExcel->getFldObj('Country')
                 ,'address_po_code' => $phpExcel->getFldObj('Zip Code')
            );

            $file_name = "Parent_Address_" . date("d-m-Y") . ".xls";

            $config = array(
                 'filename'  => $file_name
                ,'fldsArr'   => $fa
                ,'dataArray' => $dataArray
            );

            return $phpExcel->exportData($config);
        }
    }

    /**
     * Export Parent Name and Mobile No
     */
    function getParentExportMobile($dataArray){
        $phpExcel = includeCPClass('Lib', 'PhpExcelExportWrapper', 'PhpExcelExportWrapper');

        $fa = array(
              'first_name'    => $phpExcel->getFldObj('Parent Name')
             ,'mobile'        => $phpExcel->getFldObj('Mobile')
        );

        $file_name = "Parent_Mobile_" . date("d-m-Y") . ".xls";
        $config = array(
             'filename'  => $file_name
            ,'fldsArr'   => $fa
            ,'dataArray' => $dataArray
        );

        return $phpExcel->exportData($config);
    }
}
