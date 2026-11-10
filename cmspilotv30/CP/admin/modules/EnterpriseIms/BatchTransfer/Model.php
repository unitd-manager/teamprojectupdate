<?
class CP_Admin_Modules_EnterpriseIms_BatchTransfer_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {

        $SQL   = "
        SELECT c.*
        FROM contact c
        ";

        return $SQL;
    }

    /**
    */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

        $contact_id = $fn->getReqParam('contact_id');

        if ($contact_id != "") {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$contact_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "c.contact_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'c.contact_id');

            //------------------------------------------------------------------------//
            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
                       c.first_name         LIKE '%{$tv['keyword']}%'
                    OR c.last_name          LIKE '%{$tv['keyword']}%'
                    OR c.company_name       LIKE '%{$tv['keyword']}%'
                    OR c.id_card_no         LIKE '%{$tv['keyword']}%'
                    OR c.registration_no    LIKE '%{$tv['keyword']}%'
                    OR c.email              LIKE '%{$tv['keyword']}%'
                )";
            }

            $searchVar->sortOrder = "c.contact_id DESC";
        }
    }

    /**
    */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $validate->resetErrorArray();
        
        $validate->validateData('course_id', "Please select the {$modulesArr['enterpriseIms_course']['title']}");
        $validate->validateData('batch_id', "Please select the {$modulesArr['enterpriseIms_batch']['title']}");

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
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
    */
    function getEditValidate() {
        return $this->getNewValidate();
    }

    /**
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
    */
    function getFields(){
        $fn = Zend_Registry::get('fn');
        
        return;

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title', '', true);
        $fa = $fn->addToFieldsArray($fa, 'course_code');
        $fa = $fn->addToFieldsArray($fa, 'course_type');
        $fa = $fn->addToFieldsArray($fa, 'description', '', true);

        return $fa;
    }

    /**
     *
    */
    function getStudentSearchValidate() {
        $validate = Zend_Registry::get('validate');
        $modulesArr = Zend_Registry::get('modulesArr');

        $validate->resetErrorArray();
        $validate->validateData('year', 'Please select the year');
        $validate->validateData('course_id', "Please select the {$modulesArr['enterpriseIms_course']['title']}");

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
    */
    function getBatchTransferStudentValidate() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');

        $enrollment_year = $fn->getPostParam('enrollment_year');
        $graduated       = $fn->getPostParam('graduated');
        $site_id         = $fn->getSessionParam('cp_site_id');
        
        $appendSQL = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSQL .= " AND c.site_id  = {$_SESSION['cp_site_id']}";
        }
        
        $validate->resetErrorArray();
        $validate->validateData('enrollment_year', 'Please select the year');

        if ($graduated == 0) {
            $validate->validateData('enrollment_course_id', "Please select the {$modulesArr['enterpriseIms_course']['title']}");
        }
                
        $trainee_id_arr = $_SESSION['selectedContactIds'];        
        $count = count($trainee_id_arr);
        
        if ($count == 0) {
            $msg = 'No students are selected for enrollment';
            $validate->validateData('error_box', $msg);
        }

        if ($enrollment_year != '' && $count > 0) {
            $selectContactIds = join(',', $_SESSION['selectedContactIds']);
            $sqlContact = "
            SELECT cc.course_contact_id
                  ,c.first_name
            FROM course_contact cc
            LEFT JOIN (contact c) ON (cc.contact_id = c.contact_id)
            WHERE cc.year_of_enrollment = {$enrollment_year}
              AND cc.contact_id IN ({$selectContactIds})
              {$appendSQL}
            ";
            $result  = $db->sql_query($sqlContact);  
            $numRows = $db->sql_numrows($result);
            
		    if ($numRows > 0){
                $contactRow = '';
                $count = 1;
                while ($row = $db->sql_fetchrow($result)) {
                    
                    if ($count == $numRows) {
                        $contactRow .= $row['first_name'];
                    } else {
                        $contactRow .= $row['first_name'] . ' , ';
                    }
                    $count++;
                }
            
                $msg = 'The students ' . $contactRow . ' are already enrolled for selected year';
                $validate->validateData('error_box', $msg);
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
    function getContactsFromCourseContact() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $course_id  = $fn->getReqParam('course_id');
        $level_id   = $fn->getReqParam('level_id');
        $batch_id   = $fn->getReqParam('batch_id');
        $year       = $fn->getReqParam('year');

        $appendSQL = '';
        
        $s = $_SESSION['selectedContactIds'];
        
        if ($level_id) {
            $appendSQL .= " AND cc.level_id  = {$level_id}";
        }
        
        if ($batch_id) {
            $appendSQL .= " AND cc.batch_id  = {$batch_id}";
        }
        
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSQL .= " AND c.site_id  = {$_SESSION['cp_site_id']}";
        }
        
        if (count($s) > 0){
            $selectContactIds = join(',', $s);
            $appendSQL .= " AND c.contact_id NOT IN ($selectContactIds) ";
        }

        return "
        SELECT c.*
        FROM contact c
        LEFT JOIN (course_contact cc) ON (c.contact_id = cc.contact_id)
        WHERE cc.year_of_enrollment = '{$year}'
          AND cc.course_id = {$course_id}
        {$appendSQL}
        ";
    }

    /**
     *
     */
    function getBatchTransferStudentSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');

        if (!$this->getBatchTransferStudentValidate()){
            return $validate->getErrorMessageXML();
        }

        $year        = $fn->getPostParam('enrollment_year');
        $course_id   = $fn->getPostParam('enrollment_course_id');
        $level_id    = $fn->getPostParam('enrollment_level_id');
        $batch_id    = $fn->getPostParam('enrollment_batch_id');
        $graduated   = $fn->getPostParam('graduated');
        $site_id     = $fn->getSessionParam('cp_site_id');
        
        if ($graduated == 1) {
            $trainee_id_arr = $_SESSION['selectedContactIds'];
            foreach ($trainee_id_arr AS $trainee_id){
                $fa = array();
                $fa['graduation_year']   = $year;
                $fa['status']            = 'Graduated';
                $fa['modified_by']       = $fn->getSessionParam('userName');
                $fa['modification_date'] = date("Y-m-d H:i:s");
        
                $whereCondition = "
                WHERE contact_id = {$trainee_id}
                ";
        
                $SQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'contact', $whereCondition);
                $db->sql_query($SQL);
            }
        } else {
            $appendSQLOrder = '';
            $appendSQLInv = '';
            $appendSQLPar = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSQLOrder .= " AND o.site_id  = {$site_id}";
                $appendSQLInv   .= " AND i.site_id  = {$site_id}";
                $appendSQLPar   .= " AND p.site_id  = {$site_id}";
            }
    
            $trainee_id_arr = $_SESSION['selectedContactIds'];
            foreach ($trainee_id_arr AS $trainee_id){
            
                /* Creation of order record */
                $fa = $this->getParentFields($trainee_id);
                $fa['year_of_enrollment']   = $year;
                $order_id = $fn->addRecord($fa, 'order');
                
                // Finding last discount value for the student
                $sqlOrder = "
                SELECT o.order_id FROM `order` o
                LEFT JOIN (order_item oi) ON (o.order_id = oi.order_id)
                WHERE oi.contact_id = {$trainee_id}
                ORDER BY o.year_of_enrollment DESC
                ";
                $resultOrder  = $db->sql_query($sqlOrder);
                $rowOrder     = $db->sql_fetchrow($resultOrder);
                
                $sqlInv = "
                SELECT i.discount_amount FROM invoice i
                WHERE i.order_id = {$rowOrder['order_id']}
                  AND i.contact_id = {$trainee_id}
                  AND i.invoice_month = 
                  (SELECT MAX(i1.invoice_month)
                   FROM invoice i1
                   WHERE i1.order_id = {$rowOrder['order_id']}
                     AND i.contact_id = {$trainee_id}
                  )
                  {$appendSQLInv}
                ";
                $resultInv  = $db->sql_query($sqlInv);
                $rowInv     = $db->sql_fetchrow($resultInv);
                
                //creation of order_item record
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
                $fa = array();
                $fa['order_id']   = $order_id;
                $fa['module']     = 'enterpriseIms_course';
                $fa['record_id']  = $course_id;
                $fa['contact_id'] = $trainee_id;
                $fa['qty']        = 1;
                $fa['item_title'] = $courseRec['title'];
                $fa['unit_price'] = $courseRec['price'];
                $fn->addRecord($fa, 'order_item');
                
                //creation of course_contact record
                $sqlParent = "
                SELECT p.* FROM parent p
                LEFT JOIN (parent_contact pc) ON (p.parent_id = pc.parent_id)
                WHERE pc.contact_id = {$trainee_id}
                {$appendSQLPar}
                ";
                $resultParent  = $db->sql_query($sqlParent);
                $parentRec     = $db->sql_fetchrow($resultParent);
                
                $fa = array();
                $fa['order_id']           = $order_id;
                $fa['course_id']          = $course_id;
                $fa['parent_id']          = $parentRec['parent_id'];
                $fa['level_id']           = $level_id;
                $fa['batch_id']           = $batch_id;
                $fa['contact_id']         = $trainee_id;
                $fa['year_of_enrollment'] = $year;
                $fa['creation_date']      = date("Y-m-d H:i:s");
                $fa['created_by']         = $fn->getSessionParam('userName');
                
                if ($site_id) {
                    $fa['site_id']      = $site_id;
                }
                
                $id = $fn->addRecord($fa, 'course_contact');
                
                for ($inv=1; $inv <= 12; $inv++) {
                    $modGroup = getCPModuleObj('enterpriseIms_orderLink');                    
                    $modGroup->model->getGenerateInvoiceRecords($order_id, $trainee_id , $courseRec['price'], $inv, $site_id, $year, $rowInv['discount_amount']);
                }
            }
        }
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
    */
    function getParentFields($trainee_id){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        
        $sqlParent = "
        SELECT p.* FROM parent p
        LEFT JOIN (parent_contact pc) ON (p.parent_id = pc.parent_id)
        WHERE pc.contact_id = {$trainee_id}
        ";
        $resultParent  = $db->sql_query($sqlParent);
        $parentRec     = $db->sql_fetchrow($resultParent);

        $fa = array();

        $fa['cust_first_name']          = $parentRec['first_name'];
        $fa['cust_last_name']           = $parentRec['last_name'];
        $fa['cust_email']               = $parentRec['email'];
        $fa['cust_phone']               = $parentRec['phone'];
        $fa['cust_address1']            = $parentRec['address_flat'];
        $fa['cust_address_city']        = $parentRec['address_city'];
        $fa['cust_address_state']       = $parentRec['address_state'];
        $fa['cust_address_po_code']     = $parentRec['address_po_code'];
        $fa['parent_id']                = $parentRec['parent_id'];
        $fa['payment_method']           = $parentRec['mode_of_payment'];
        $fa['module']                   = 'enterpriseIms_course';
        $fa['order_status']             = 'Due';
        $fa['order_date']               =  date('Y-m-d');
        $fa['contact_module']           = 'enterpriseIms_parent';
        
        return $fa;
    }
}