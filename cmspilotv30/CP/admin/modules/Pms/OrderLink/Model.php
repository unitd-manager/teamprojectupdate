<?
class CP_Admin_Modules_Pms_OrderLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $company_id = $fn->getReqParam('company_id');
        
        $fa['company_id']       = $company_id;
        $fa['payment_method']   = '';
        $fa['module']           = 'pms_course';
        $fa['order_status']     = 'Due';
        $fa['order_date']       =  date('Y-m-d');
        $fa['contact_module']   = 'pms_company';
        
        $companyRec = $fn->getRecordRowByID('company', 'company_id', $company_id);

        $fa['cust_first_name']          = $companyRec['title'];
        $fa['cust_email']               = $companyRec['email'];
        $fa['cust_phone']               = $companyRec['phone'];
        $fa['cust_address1']            = $companyRec['address1'];
        $fa['cust_address2']            = $companyRec['address2'];
        $fa['cust_address_city']        = $companyRec['address_city'];
        $fa['cust_address_state']       = $companyRec['address_state'];
        $fa['cust_address_po_code']     = $companyRec['address_po_code'];
        $fa['cust_address_country_code']= $companyRec['address_country_code'];

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

        $fa['parent_id']        = $parent_id;
        $fa['payment_method']   = $parentRec['mode_of_payment'];
        $fa['module']           = 'pms_course';
        $fa['order_status']     = 'Due';
        $fa['order_date']       =  date('Y-m-d');
        $fa['contact_module']   = 'pms_parent';
        
        //$fa['cust_address_country_code']= $parentRec['address_country_code'];

        return $fa;
    }

    /**
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
    */
    function getAdd() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $order_id = $fn->getReqParam('order_id');
        if (!$this->getNewValidate()) {
            return $validate->getErrorMessageXML();
        }
        $company_id = $fn->getReqParam('company_id');
        $parent_id  = $fn->getReqParam('parent_id');
        
        if ($order_id == ''){
            $fa = $this->getFields();
            $order_id = $fn->addRecord($fa);
        }
        
        $course_id      = $fn->getPostParam('course_id');
        $trainee_id_arr = $_SESSION['selectedContactIds'];
        $batch_id_arr   = $fn->getPostParam('batch_id', array());
        $subsidy_id_arr = $fn->getPostParam('course_subsidy_history_id', array());
        $discount_id_arr= $fn->getPostParam('discount_id', array());
        
        /*
        print 'company-id ' . $company_id . "<br/>";
        print 'parent_id ' . $parent_id . "<br/>";
        print 'trainee' . "<br/>";
        print_r ($trainee_id_arr);
        print 'batch' . "<br/>";
        print_r ($batch_id_arr);
        print 'subsidy' . "<br/>";
        print_r ($subsidy_id_arr);
        print 'discount' . "<br/>";
        print_r ($discount_id_arr);
        */
        
        $count = count($trainee_id_arr);
        $recCount = 0;
        for ($i= 0; $i< $count; $i++){
            $discount = '';
            $trainee_id = $trainee_id_arr[$i];
            $batch_id = $batch_id_arr[$i];

            $subsidy_id = $subsidy_id_arr[$i];
            $discount_id = $discount_id_arr[$i];

            //creates order, order_item and course contact records
            if ($course_id > 0){
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
                $fa = array();
                $fa['order_id']   = $order_id;
                $fa['module']     = 'pms_course';
                $fa['record_id']  = $course_id;
                $fa['contact_id'] = $trainee_id;
                $fa['qty']        = 1;
                $fa['item_title'] = $courseRec['title'];
                $fa['unit_price'] = $courseRec['price'];
                $fn->addRecord($fa, 'order_item');
                
                if ($subsidy_id > 0){
                    $sqlSubsidy = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                    WHERE csh.course_subsidy_history_id = {$subsidy_id}
                    ";
                    $resultSubsidy  = $db->sql_query($sqlSubsidy);
                    $rowSubsidy     = $db->sql_fetchrow($resultSubsidy);
                    
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'pms_subsidy';
                    $fa['record_id']  = $course_id;
                    $fa['contact_id'] = $trainee_id;
                    $fa['qty']        = 1;
                    $fa['item_title'] = $rowSubsidy['title'];
                    $fa['unit_price'] = -$rowSubsidy['value'];
                    $fn->addRecord($fa, 'order_item');
                }

                if ($discount_id > 0){
                    $sqlDiscount = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                    WHERE csh.course_subsidy_history_id = {$discount_id}
                    ";
                    $resultDiscount  = $db->sql_query($sqlDiscount);
                    $rowDiscount     = $db->sql_fetchrow($resultDiscount);
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'pms_discount';
                    $fa['contact_id'] = $trainee_id;
                    $fa['record_id']  = $course_id;
                    $fa['qty']        = 1;
                    $fa['item_title'] = $rowDiscount['title'];
                    $fa['unit_price'] = -$rowDiscount['value'];
                    $discount         = $discount_id;
                    $fn->addRecord($fa, 'order_item');
                }

                $fa = array();
                $fa['order_id']         = $order_id;
                $fa['course_id']        = $course_id;
                $fa['company_id']       = $company_id;
                $fa['batch_id']         = $batch_id;
                $fa['contact_id']       = $trainee_id;
                $fa['course_subsidy_history_id']= $subsidy_id;
                $fa['discount']         = $discount_id;

                $id = $fn->addRecord($fa, 'course_contact');
                $recCount++;
            }
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

        $order_id = $fn->getPostParam('order_id', '', true);

        $course_id      = $fn->getPostParam('course_id');
        $trainee_id_arr = $_SESSION['selectedContactIds'];
        $batch_id_arr   = $fn->getPostParam('batch_id', array());
        $subsidy_id_arr = $fn->getPostParam('course_subsidy_history_id', array());
        $discount_id_arr= $fn->getPostParam('discount_id', array());

        /*
        print 'company-id ' . $company_id . "<br/>";
        print 'course_id ' . $course_id . "<br/>";
        print 'trainee' . "<br/>";
        print_r ($trainee_id_arr);
        print 'batch' . "<br/>";
        print_r ($batch_id_arr);
        print 'subsidy' . "<br/>";
        print_r ($subsidy_id_arr);
        print 'discount' . "<br/>";
        print_r ($discount_id_arr);
        return;
        */

        /*
        $course_id_arr  = $fn->getPostParam('course_id', array());
        $trainee_id_arr = $fn->getPostParam('contact_id', array());
        $batch_id_arr   = $fn->getPostParam('batch_id', array());
        $subsidy_id_arr = $fn->getPostParam('course_subsidy_id', array());
        $course_contact_arr  = $fn->getPostParam('course_contact_id', array());
        $discount       = $fn->getPostParam('discount');
        */
        
        $count = count($trainee_id_arr);
        for ($i= 0; $i< $count; $i++){
            if(count($trainee_id_arr)){
                $trainee_id = $trainee_id_arr[$i];
            }
            else{
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
            AND module      = 'pms_course'
            AND contact_id  = $trainee_id
            ");

            $expOrderItemSubsidy = array('condn' => "AND record_id = $course_id 
            AND module      = 'pms_subsidy'
            AND contact_id  = $trainee_id
            ");

            $expOrderItemDiscount = array('condn' => "AND record_id = $course_id 
            AND module      = 'pms_discount'
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
                $orderItemRecSubsidy   = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemSubsidy);
                
                $discountRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemDiscount);
                
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
                    
                    if (!is_array($orderItemRecSubsidy)){
                        $fa = array();
                        $fa['order_id']   = $order_id;
                        $fa['module']     = 'pms_subsidy';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        $fa['item_title'] = $rowsubsidy['title'];
                        $fa['unit_price'] = -$rowsubsidy['value'];
                        $fn->addRecord($fa, 'order_item');
                    }
                    else{
                        $fa = array();
                        $fa['record_id']  = $course_id;
                        $fa['item_title'] = $rowsubsidy['title'];
                        $fa['unit_price'] = -$rowsubsidy['value'];
                        $fn->saveRecord($fa, 'order_item', 'order_item_id', $orderItemRecSubsidy['order_item_id']);
                    }
                }
                
                // to create new order item, if new discount is created or update the existing discount
                if($discount_id){
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
                        $fa['module']     = 'pms_discount';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        //$fa['item_title'] = 'Discount';
                        $fa['item_title'] = $rowdiscount['title'];
                        $fa['unit_price'] = -$rowdiscount['value'];
                        $fn->addRecord($fa, 'order_item');
                    }
                    else{
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
                if ($course_id > 0){
                    $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'pms_course';
                    $fa['record_id']  = $course_id;
                    $fa['contact_id'] = $trainee_id;
                    $fa['qty']        = 1;
                    $fa['item_title'] = $courseRec['title'];
                    $fa['unit_price'] = $courseRec['price'];
                    $fn->addRecord($fa, 'order_item');
                    
                    if ($subsidy_id > 0){
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
                        $fa['module']     = 'pms_subsidy';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        $fa['item_title'] = $rowsubsidy['title'];
                        $fa['unit_price'] = -$rowsubsidy['value'];
                        $fn->addRecord($fa, 'order_item');
                    }
                    
                    if ($discount_id > 0){
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
                        $fa['module']     = 'pms_discount';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        //$fa['item_title'] = 'Discount';;
                        $fa['item_title'] = $rowdiscount['title'];
                        $fa['unit_price'] = -$rowdiscount['value'];
                        $fn->addRecord($fa, 'order_item');
                    }


                    $fa = array();
                    $fa['order_id']         = $order_id;
                    $fa['course_id']        = $course_id;
                    $fa['company_id']       = $company_id;
                    $fa['batch_id']         = $batch_id;
                    $fa['contact_id']       = $trainee_id;
                    $fa['course_subsidy_history_id']= $subsidy_id;
                    $fa['discount']         = $discount_id;

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
        //$discount_id_arr= $fn->getPostParam('discount_id', array());

        /*
        print 'parent_id ' . $parent_id . "</n>";
        print 'course' . "</n>";
        print_r ($course_id_arr);
        print 'trainee' . "</n>";
        print_r ($trainee_id_arr);
        print 'batch' . "</n>";
        print_r ($batch_id_arr);
        print 'subsidy' . "</n>";
        print_r ($subsidy_id_arr);
        print 'discount' . "</n>";
        print_r ($discount_id_arr);
        return;
        */
        
        $count = count($trainee_id_arr);
        for ($i= 0; $i< $count; $i++){
            if(count($trainee_id_arr)){
                $trainee_id = $trainee_id_arr[$i];
            }
            else{
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
            AND module      = 'pms_course'
            AND contact_id  = $trainee_id
            ");

            $expOrderItem = array('condn' => "
            AND module      = 'pms_course'
            AND contact_id  = $trainee_id
            ");

            $expOrderItemSubsidy = array('condn' => "
            AND module      = 'pms_subsidy'
            AND contact_id  = $trainee_id
            ");

            $expOrderItemDiscount = array('condn' => "
            AND module      = 'pms_discount'
            AND contact_id  = $trainee_id 
            ");

            // To update record in course contact item if batch/subsidy is changed
            if ($course_contact_id > 0){
                $fa = array();
                if ($add_reg_fee  == 'Yes'){
                    $fa['add_registration_fee'] = 1;
                }     
                else if($add_reg_fee  == 'No'){
                    $fa['add_registration_fee'] = 0;
                }     

                $fa['course_id']        = $course_id;
                $fa['level_id']         = $level_id;
                $fa['batch_id']         = $batch_id;
                //$fa['discount']         = $discount_id;
                $fa['course_subsidy_history_id']= $subsidy_id;
                $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id);

                $courseRec      = $fn->getRecordRowByID('course', 'course_id', $course_id);
                $orderItemRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItem);

                // creates order item record for registration fee
                if ($add_reg_fee  == 'No'){
                    $expOrderItemReg = array('condn' => "
                    AND module      = 'pms_reg_fee'
                    AND contact_id  = $trainee_id
                    ");
                    //to check if there is already reg record created or not in order item
                    $orderItemRegRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemReg);
                    if (is_array($orderItemRegRec)){
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
                    if (is_array($invoiceRegRec)){
                        $deleteSQL = "
                        DELETE FROM invoice 
                        WHERE invoice_id = {$invoiceRegRec['invoice_id']}
                        ";
                        $resultDelete  = $db->sql_query($deleteSQL);
                    }
                }
                else if ($add_reg_fee  == 'Yes'){
                    $expOrderItemReg = array('condn' => "
                    AND module      = 'pms_reg_fee'
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
                        $fa['module']     = 'pms_reg_fee';
                        $fa['contact_id'] = $trainee_id;
                        $fa['add_registration_fee'] = 1;
                        $fn->addRecord($fa, 'order_item');
                    }
                }
                
                // to update new order item,
                $fa = array();
                $fa['order_id']   = $order_id;
                $fa['module']     = 'pms_course';
                $fa['record_id']  = $course_id;
                $fa['contact_id'] = $trainee_id;
                $fa['qty']        = 1;
                $fa['item_title'] = $courseRec['title'];
                $fa['unit_price'] = $courseRec['price'];
                $fn->saveRecord($fa, 'order_item', 'order_item_id', $orderItemRec['order_item_id']);

                // to create new subsidy/discount, if new subsidy is created or update the existing subsidy

                $orderItemRecSubsidy   = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemSubsidy);
                
                $discountRec   = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemDiscount);
                
                //$subsidyRec = $fn->getRecordRowByID('course_subsidy_history_id', 'course_subsidy_history_id', $subsidy_id);
                
                if($subsidy_id){
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
                    
                    if (!is_array($orderItemRecSubsidy)){
                        $fa = array();
                        $fa['order_id']   = $order_id;
                        $fa['module']     = 'pms_subsidy';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        $fa['item_title'] = $rowsubsidy['title'];
                        $fa['unit_price'] = -$rowsubsidy['value'];
                        $fn->addRecord($fa, 'order_item');
                    }
                    else{
                        $fa = array();
                        $fa['record_id']  = $course_id;
                        $fa['item_title'] = $rowsubsidy['title'];
                        $fa['unit_price'] = -$rowsubsidy['value'];
                        $fn->saveRecord($fa, 'order_item', 'order_item_id', $orderItemRecSubsidy['order_item_id']);
                    }
                }
                //to delete the record in order item
                else{
                    $deleteSQL = "
                    DELETE FROM order_item
                    WHERE module = 'pms_subsidy' AND contact_id = {$trainee_id}
                    ";
                    $resultDelete  = $db->sql_query($deleteSQL);
                }
                
                // to create new order item, if new discount is created or update the existing discount
                /*
                if($discount_id){
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
                        $fa['module']     = 'pms_discount';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        //$fa['item_title'] = 'Discount';
                        $fa['item_title'] = $rowdiscount['title'];
                        $fa['unit_price'] = -$rowdiscount['value'];
                        $fn->addRecord($fa, 'order_item');
                    }
                    else{
                        $fa = array();
                        $fa['record_id']  = $course_id;
                        //$fa['item_title'] = 'Discount';
                        $fa['item_title']  = $rowdiscount['title'];
                        $fa['unit_price'] = -$rowdiscount['value'];
                        $fn->saveRecord($fa, 'order_item', 'order_item_id', 
                        $discountRec['order_item_id']);
                    }
                }
                else{
                    $deleteSQL = "
                    DELETE FROM order_item
                    WHERE module = 'pms_discount' AND contact_id = {$trainee_id}
                    ";
                    $resultDelete  = $db->sql_query($deleteSQL);
                }
                */
            }
            // To add record in course contact/order_item item if new trainee/course is added
            else{
                if ($course_id > 0){
                    $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
                    $orderRec = $fn->getRecordRowByID('order', 'order_id', $order_id);
                    
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'pms_course';
                    $fa['record_id']  = $course_id;
                    $fa['contact_id'] = $trainee_id;
                    $fa['qty']        = 1;
                    $fa['item_title'] = $courseRec['title'];
                    $fa['unit_price'] = $courseRec['price'];
                    $fn->addRecord($fa, 'order_item');
                    
                    if ($subsidy_id > 0){
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
                        $fa['module']     = 'pms_subsidy';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        $fa['item_title'] = $rowsubsidy['title'];
                        $fa['unit_price'] = -$rowsubsidy['value'];
                        $fn->addRecord($fa, 'order_item');
                    }
                    /*
                    if ($discount_id > 0){
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
                        $fa['module']     = 'pms_discount';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        //$fa['item_title'] = 'Discount';;
                        $fa['item_title'] = $rowdiscount['title'];
                        $fa['unit_price'] = -$rowdiscount['value'];
                        $fn->addRecord($fa, 'order_item');
                    }
                    */


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
        
        /*
        if($order_id){
            $expCourseContact = array('condn' => "
            AND contact_id  = $contact_id
            ");

            $courseContactRec   = $fn->getRecordRowByID('course_contact', 'order_id', $order_id, $expCourseContact);

            $course_contact_id = $courseContactRec['course_contact_id'];
            
            if($course_contact_id){
                $deleteSQl = "
                DELETE FROM course_contact
                WHERE course_contact_id = {$course_contact_id}
                ";
                $result   = $db->sql_query($deleteSQl);  

                $deleteSQl = "
                DELETE FROM order_item
                WHERE contact_id = {$contact_id}
                    AND order_id = {$order_id}
                ";
                $result   = $db->sql_query($deleteSQl);  
            }
        }
        */
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
            $IdCardlink = $fn->getRecordDetailLink('pms_contact', 'record_id', $rec['contact_id'], $expIdCard);
    
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
        //$validate->validateData('email' , 'Please enter a valid email address', 'email');
        
        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
    */
    function getAddBulkParentStudentSubmit() {
        /********************************* PROCESS ************************************/
        /*
        ACTION: CREATING INDIVIDUAL ENROLLMENT RECORD FOR THE STUDENTS AND ALSO CREATING INVOICES FOR SELECTED MONTHS
        STEP 1: CHECKING FOR PREVIOUS ENROLLMENT FOR THE CHOSEN YEAR AND GIVING VALIDATION MESSAGE
        STEP 2: CREATION OF ORDER RECORD
        STEP 3: CREATION OF RECORD IN ORDER ITEM TABLE FOR REG FEE
        STEP 4: CREATION OF RECORD IN ORDER ITEM FOR CHOSEN COURSE
        STEP 5: CREATION OF RECORD IN ORDER ITEM IF SUBSIDY IS CHOSEN
        STEP 6: CREATION OF RECORD IN COURSE CONTACT TABLE
        STEP 7: CREATION OF INVOICE RECORDS FOR THE CHOSEN MONTH
        */
        /******************************* END PROCESS **********************************/

        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $order_id           = $fn->getReqParam('order_id');
        $parent_id          = $fn->getReqParam('parent_id');
        $year_of_enrollment = $fn->getPostParam('year_of_enrollment');
        $discount_amount    = $fn->getPostParam('discount_amount');
        $site_id            = $fn->getSessionParam('cp_site_id');

        if (!$this->getAddBulkParentStudentValidate()){
            return $validate->getErrorMessageXML();
        }

        /* Creation of order record */
        //if ($order_id == ''){
        //    $fa = $this->getParentFields();
        //    $fa['year_of_enrollment']   = $year_of_enrollment;
        //    $order_id = $fn->addRecord($fa);
        //}

        $trainee_id_arr = $_SESSION['selectedContactIds'];
        $course_id_arr  = $fn->getPostParam('course_id_row', array());
        $level_id_arr   = $fn->getPostParam('level_id', array());
        $batch_id_arr   = $fn->getPostParam('batch_id', array());
        $subsidy_id_arr = $fn->getPostParam('course_subsidy_history_id', array());
        
        $monthArray  = $fn->getPostParam('month', array());

        /*
        print 'parent_id ' . $parent_id . "</n>";
        print 'course' . "</n>";
        print_r ($course_id_arr);
        print 'trainee' . "</n>";
        print_r ($trainee_id_arr);
        print 'batch' . "</n>";
        print_r ($batch_id_arr);
        print 'subsidy' . "</n>";
        print_r ($subsidy_id_arr);
        print 'discount' . "</n>";
        print_r ($discount_id_arr);
        return;
        */
        
        $count = count($trainee_id_arr);
        $trainee_key_increment = key($trainee_id_arr);
        $recCount = 0;
        for ($i= 0; $i < $count; $i++){
            if ($trainee_key_increment == $i) {
                $discount = '';
                $trainee_id = $trainee_id_arr[$i];

                /********************************** STEP 1 **************************************/
                if (!$this->getCheckPreviousEnrollment($trainee_id, $year_of_enrollment)) {
                    return $validate->getErrorMessageXML();
                }
                /********************************** STEP 1 ENDS HERE ****************************/
                
                /********************************** STEP 2 **************************************/
                /* Creation of order record */
                $fa = $this->getParentFields();
                $fa['year_of_enrollment']   = $year_of_enrollment;
                $order_id = $fn->addRecord($fa);
                /********************************** STEP 2 ENDS HERE ****************************/
        
                $course_id  = $course_id_arr[$i];
                $level_id   = $level_id_arr[$i];
                $batch_id   = $batch_id_arr[$i];
                
                $subsidy_id = $subsidy_id_arr[$i];
                //$discount_id = $discount_id_arr[$i];
                $pfx = $trainee_id . '_' ;
                $add_reg_fee  = $fn->getPostParam("{$pfx}add_reg_fee");
                
                /********************************** STEP 3 **************************************/
                if ($add_reg_fee  == 'Yes'){
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['record_id']  = $course_id;
                    $fa['qty']        = 1;
                    $fa['unit_price'] = $fn->getSettingsValueByKey("registrationFeeEnt");
                    $fa['item_title'] = 'Registration Fee';
                    $fa['module']     = 'pms_reg_fee';
                    $fa['contact_id'] = $trainee_id;
                    $fa['add_registration_fee'] = 1;
                    $fn->addRecord($fa, 'order_item');
                }
                /********************************** STEP 3 ENDS HERE ****************************/
                
                if ($course_id > 0){
                    /********************************** STEP 4 **************************************/
                    $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'pms_course';
                    $fa['record_id']  = $course_id;
                    $fa['contact_id'] = $trainee_id;
                    $fa['qty']        = 1;
                    $fa['item_title'] = $courseRec['title'];
                    $fa['unit_price'] = $courseRec['price'];
                    $fn->addRecord($fa, 'order_item');
                    /********************************** STEP 4 ENDS HERE ****************************/
                        
                    /********************************** STEP 5 **************************************/
                    if ($subsidy_id > 0){
                        $sqlSubsidy = "
                        SELECT sd.*
                        FROM subsidy_discount sd
                        LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                        WHERE csh.course_subsidy_history_id = {$subsidy_id}
                        ";
                        $resultSubsidy  = $db->sql_query($sqlSubsidy);
                        $rowSubsidy     = $db->sql_fetchrow($resultSubsidy);
                        
                        if($rowSubsidy['mode_of_calculation'] == '%'){
                            $rowSubsidy['value'] = $courseRec['price'] * $rowSubsidy['value'] / 100;
                        }
                        
                        $fa = array();
                        $fa['order_id']   = $order_id;
                        $fa['module']     = 'pms_subsidy';
                        $fa['record_id']  = $course_id;
                        $fa['contact_id'] = $trainee_id;
                        $fa['qty']        = 1;
                        $fa['item_title'] = $rowSubsidy['title'];
                        $fa['unit_price'] = -$rowSubsidy['value'];
                        $fa['subsidy_discount_type'] = $rowSubsidy['mode_of_calculation'];
                        $fn->addRecord($fa, 'order_item');
                    }
                    /********************************** STEP 5 ENDS HERE ****************************/
                    
                    /*if ($discount_id > 0){
                        $sqlDiscount = "
                        SELECT sd.*
                        FROM subsidy_discount sd
                        LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                        WHERE csh.course_subsidy_history_id = {$discount_id}
                        ";
                        $resultDiscount  = $db->sql_query($sqlDiscount);
                        $rowDiscount     = $db->sql_fetchrow($resultDiscount);
                        $fa = array();
                        $fa['order_id']   = $order_id;
                        $fa['module']     = 'pms_discount';
                        $fa['contact_id'] = $trainee_id;
                        $fa['record_id']  = $course_id;
                        $fa['qty']        = 1;
                        $fa['item_title'] = $rowDiscount['title'];
                        $fa['unit_price'] = -$rowDiscount['value'];
                        $fa['subsidy_discount_type'] = $rowSubsidy['mode_of_calculation'];
                        $discount         = $discount_id;
                        $fn->addRecord($fa, 'order_item');
                    }*/
                
                    /********************************** STEP 6 **************************************/
                    $fa = array();
                    $fa['order_id']         = $order_id;
                    $fa['course_id']        = $course_id;
                    $fa['parent_id']        = $parent_id;
                    $fa['level_id']         = $level_id;
                    $fa['batch_id']         = $batch_id;
                    $fa['contact_id']       = $trainee_id;
                    $fa['course_subsidy_history_id']= $subsidy_id;
                    $fa['year_of_enrollment']= $year_of_enrollment;
                    $fa['discount']         = $discount_amount;
                    
                    if ($site_id) {
                        $fa['site_id']      = $site_id;
                    }
                
                    if ($add_reg_fee  == 'Yes'){
                        $fa['add_registration_fee'] = 1;
                    }
                
                    $id = $fn->addRecord($fa, 'course_contact');
                    /********************************** STEP 6 ENDS HERE ****************************/
                
                    /********************************** STEP 7 **************************************/
                    foreach($monthArray AS $month_value){
                        $this->getGenerateInvoiceRecords($order_id, $trainee_id , $courseRec['price'], $month_value, $site_id, $year_of_enrollment, $discount_amount);
                    }
                    /********************************** STEP 7 ENDS HERE ****************************/
                
                    $recCount++;
                }
            }
            $trainee_key_increment++;
        }
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getAddBulkParentStudentValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('year_of_enrollment', 'Please select enrollment year');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
    */
    function getCheckPreviousEnrollment($trainee_id, $year_of_enrollment) {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');  
        
        if ($trainee_id == '') {
            return;
        }

        $sqlContact = "
        SELECT cc.*
              ,c.first_name AS student_name
        FROM course_contact cc
        LEFT JOIN (contact c) ON (cc.contact_id = c.contact_id)
        WHERE cc.contact_id = {$trainee_id}
          AND cc.year_of_enrollment = {$year_of_enrollment}
        ";
        $resultContact  = $db->sql_query($sqlContact);
        $numRowsContact = $db->sql_numrows($resultContact);
        $rowContact     = $db->sql_fetchrow($resultContact);

        $validate->resetErrorArray();
		if($numRowsContact > 0){
            $msg = 'The student ' . $rowContact['student_name'] . ' is already enrolled for selected year';
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
    function getCalculateStudentAge() {
        $fn = Zend_Registry::get('fn');
        
        $date_of_birth = $fn->getReqParam('date_of_birth');
        $diff = '';
        /* to calculate age according to months.
        $date_of_birth = date('Ymd', strtotime($date_of_birth));
        $diff = date('Ymd') - $date_of_birth;

        return substr($diff, 0, -4);
        */
        // to calculate age according to difference in years.
        $date_of_birth = date('Y', strtotime($date_of_birth));
        $diff = date('Y') - $date_of_birth;
        
        $json = array('age' => $diff);
        return json_encode($json);
    }
    
    /**
     *
     */
    function getGenerateInvoiceRecords($order_id ='' ,$contact_id = '', $total = '', $month = '', $site_id, $year_of_enrollment, $discount_amount) {
        /********************************* PROCESS ************************************/
        /*
        ACTION: DISPLAY FOR PARENT # STUDENT ENROLLMENT WINDOW WHEN CLICK HERE FOR ENROLLMENT BUTTON IS CLICKED IN RIGHT PANEL
        STEP 1: CHECKING FOR ORDER ITEM RECORD IF REG FEE IS AVAILABLE
        STEP 2: CHECKING WHETHER INVOICE RECORD IS CREATED EARLIER
        STEP 3: IF RECORDS RELATED TO REG FEE IS FOUND IN ORDER ITEM RECORD AND NO INVOICE RECORD IS CREATED EARLIER, 
        GENERATE A NEW INVOICE FOR REG FEE. THIS WILL BE USEFUL TO ADD THE REG FEE AMOUNT IN THE INVOICE TOTAL.
        STEP 4: SELECTION OF NEXT INVOICE CODE FROM SETTING TABLE
        STEP 5: CREATION OF INVOICE RECORD
        STEP 6: UPDATION OF INVOICE CODE IN SETTING TABLE
        STEP 6a: UPDATE INVOICE WITH DISCOUNT IF DISCOUNT IS AVAILABLE OR THERE ARE 3 OR MORE ACTIVE SIBLINGS
        STEP 7: AUTO GENERATION OF RECEIPT IF DISCOUNT AMOUNT IS FULL (REFER SETTING TABLE FOR DISCOUNT AMOUNT)
        */
        /******************************* END PROCESS **********************************/
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        $subsidyAmount = '';
        $current_year = date('Y');
        $rowDate      = $fn->getRecordByCondition('setting', "key_text = 'invoiceDate'");
        /********************************** STEP 1 **************************************/
        $SQLOrderItem = "
        SELECT oi.*
        FROM order_item oi
        WHERE oi.order_id = {$order_id}
          AND oi.contact_id = {$contact_id}
          AND oi.module = 'pms_reg_fee'
        ";
        $resultOrderItem    = $db->sql_query($SQLOrderItem);
        $numRowsOrderItem   = $db->sql_numrows($resultOrderItem);
        $rowOrderItem       = $db->sql_fetchrow($resultOrderItem);
        /********************************** STEP 1 ENDS HERE ****************************/                 
        /********************************** STEP 2 **************************************/
        $SQLInv = "
        SELECT i.*
        FROM invoice i
        WHERE i.order_id = {$order_id}
          AND i.contact_id = {$contact_id}
          AND i.add_registration_fee = 1
        ";
        $resultInv    = $db->sql_query($SQLInv);
        $numRowsInv   = $db->sql_numrows($resultInv);
        /********************************** STEP 2 ENDS HERE ****************************/
        /********************************** STEP 3 **************************************/
        if ($numRowsOrderItem > 0 && $numRowsInv == 0) {

            /* SELECTION OF NEXT INVOICE CODE FROM SETTING */
            $nextInvoiceCode = $fn->getSettingsValueByKey("nextInvoiceCode");            
            if($nextInvoiceCode < 10) {
                $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '000' . $nextInvoiceCode;
            } else if($nextInvoiceCode < 99) {
                $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '00' . $nextInvoiceCode;
            } else if($nextInvoiceCode < 999) {
                $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '0' . $nextInvoiceCode;
            } else {
                $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . $nextInvoiceCode;
            }

            $expOrderItemSubsidy = array('condn' => "
            AND module      = 'pms_subsidy'
            AND contact_id  = $contact_id
            ");
            $orderItemRecSubsidy   = $fn->getRecordRowByID('order_item', 'order_id', $order_id, $expOrderItemSubsidy);
            
            if (is_array($orderItemRecSubsidy)){
                $subsidyAmount = $orderItemRecSubsidy['unit_price'];
            }
        
            /* CREATION OF INVOICE RECORD */
            $fa = array();
            $fa['order_id']             = $order_id;
            $fa['contact_id']           = $contact_id;
            $fa['invoice_code']         = $nextInvoiceCode;
            $fa['invoice_month']        = $month;
            $fa['invoice_date']         = $year_of_enrollment . '-' . $month . '-' . $rowDate['value'];
            $fa['invoice_amount']       = $rowOrderItem['unit_price'];
            $fa['status']               = 'Due';
            $fa['creation_date']        = date("Y-m-d H:i:s");
            $fa['created_by']           = $fn->getSessionParam('userName');
            $fa['add_registration_fee'] = 1;
            
            if ($site_id) {
                $fa['site_id']          = $site_id;
            }
            
            $invoice_id                 = $fn->addRecord($fa, 'invoice');

            /* UPDATION OF INVOICE CODE IN SETTING TABLE */
            if ($site_id) {
                $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode' AND site_id = '{$site_id}'";
            } else {
                $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
            }
            $resultUpdate    = $db->sql_query($SQLUpdate);

        }
        /********************************** STEP 3 ENDS HERE ****************************/
        /********************************** STEP 4 **************************************/
        $nextInvoiceCode = $fn->getSettingsValueByKey("nextInvoiceCode");        
        if($nextInvoiceCode < 10) {
            $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '000' . $nextInvoiceCode;
        } else if($nextInvoiceCode < 99) {
            $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '00' . $nextInvoiceCode;
        } else if($nextInvoiceCode < 999) {
            $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . '0' . $nextInvoiceCode;
        } else {
            $nextInvoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . $nextInvoiceCode;
        }
        /********************************** STEP 4 ENDS HERE ****************************/        
        /********************************** STEP 5 **************************************/
        $fa = array();
        $fa['order_id']         = $order_id;
        $fa['contact_id']       = $contact_id;
        $fa['invoice_code']     = $nextInvoiceCode;
        $fa['invoice_month']    = $month;
        $fa['invoice_date']     = $year_of_enrollment . '-' . $month . '-' . $rowDate['value'];
        $fa['invoice_amount']   = $total + $subsidyAmount;
        $fa['status']           = 'Due';
        $fa['creation_date']    = date("Y-m-d H:i:s");
        $fa['created_by']       = $fn->getSessionParam('userName');

        if ($site_id) {
            $fa['site_id']      = $site_id;
        }
        
        $invoice_id = $fn->addRecord($fa, 'invoice');
        /********************************** STEP 5 ENDS HERE ****************************/
        /********************************** STEP 6 **************************************/
        if ($site_id) {
            $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode' AND site_id = '{$site_id}'";
        } else {
            $SQLUpdate     = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        }
        $resultUpdate    = $db->sql_query($SQLUpdate);
        /********************************** STEP 6 ENDS HERE ****************************/
        /********************************** STEP 6a **************************************/
        $faInv = array();

        $parentContactRec = $fn->getRecordRowByID('parent_contact', 'contact_id', $contact_id);
        $sqlSiblingCount = "
        SELECT DISTINCT c.contact_id
        FROM contact c
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        WHERE c.status = 'Active'
          AND pc.parent_id = {$parentContactRec['parent_id']}
        ";
        $resultSiblingCount     = $db->sql_query($sqlSiblingCount);
        $numRowsSiblingCount    = $db->sql_numrows($resultSiblingCount);

        $current_date_time = date('Y-m-d H:i:s');
        $staff_name = $fn->getSessionParam('userName');
        /* Checking whether total sibling count is equal or greater than minimum sibling count */
        if ($discount_amount != '') {
            $faInv['discount_amount'] = $discount_amount;
            $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);

            /* Applying Disount for active siblings except joining student */
            if ($numRowsSiblingCount >= $fn->getSettingsValueByKey("minNoSiblingForDiscount")) {

                $sqlSibling = "
                SELECT DISTINCT c.contact_id
                FROM contact c
                LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
                WHERE c.status = 'Active'
                  AND pc.parent_id = {$parentContactRec['parent_id']}
                  AND c.contact_id != {$contact_id}
                ";
                $resultSibling = $db->sql_query($sqlSibling);
                while ($rowSibling = $db->sql_fetchrow($resultSibling)) {
                    $invoice_date = $year_of_enrollment . '-' . $month . '-' . $rowDate['value'];
                    $sqlUpdate = "
                    UPDATE invoice
                    SET discount_amount = '{$discount_amount}'
                       ,modification_date = '{$current_date_time}'
                       ,modified_by = '{$staff_name}'
                    WHERE contact_id = {$rowSibling['contact_id']}
                      AND invoice_month = '{$month}'
                      AND invoice_date = '{$invoice_date}'
                      AND status = 'Due'
                      AND add_registration_fee = ''
                    ";
                    $resultUpdate = $db->sql_query($sqlUpdate);
                }
            }
        } else {
            if ($numRowsSiblingCount >= $fn->getSettingsValueByKey("minNoSiblingForDiscount")) {
                $discount_percent = $fn->getSettingsValueByKey("discountPercentForSibling");

                $sqlSibling = "
                SELECT DISTINCT c.contact_id
                      ,cc.order_id
                FROM contact c
                LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
                LEFT JOIN (course_contact cc) ON (c.contact_id = cc.contact_id)
                WHERE c.status = 'Active'
                  AND pc.parent_id = {$parentContactRec['parent_id']}
                  AND c.contact_id != {$contact_id}
                  AND cc.year_of_enrollment = '{$year_of_enrollment}'
                ";
                $resultSibling = $db->sql_query($sqlSibling);
                while ($rowSibling = $db->sql_fetchrow($resultSibling)) {
                    /* Finding course fees for the student */
                    $sqlOi = "
                    SELECT unit_price
                    FROM order_item
                    WHERE order_id = {$rowSibling['order_id']} 
                      AND module = 'pms_course'
                    ";
                    $resultOi = $db->sql_query($sqlOi);
                    $orderItemRec = $db->sql_fetchrow($resultOi);

                    $discount_amount = ($orderItemRec['unit_price']/$discount_percent);

                    $invoice_date = $year_of_enrollment . '-' . $month . '-' . $rowDate['value'];
                    $sqlUpdate = "
                    UPDATE invoice
                    SET discount_amount = '{$discount_amount}'
                       ,modification_date = '{$current_date_time}'
                       ,modified_by = '{$staff_name}'
                    WHERE contact_id = {$rowSibling['contact_id']}
                      AND invoice_month = '{$month}'
                      AND invoice_date = '{$invoice_date}'
                      AND status = 'Due'
                      AND add_registration_fee = ''
                    ";
                    $resultUpdate = $db->sql_query($sqlUpdate);
                }

                $faInv['discount_amount'] = $discount_amount;
                $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
            } else {
                $discount_amount = 0;                
                $fn->saveRecord($faInv, 'invoice', 'invoice_id', $invoice_id);
            }
        }
        /********************************** STEP 6a ENDS HERE ****************************/
        /********************************** STEP 7 ****************************/
        if ($discount_amount == $fn->getSettingsValueByKey("discountAmount")) {
            
            $modObj = getCPModuleObj('pms_order');
            $receiptCode    = $modObj->model->getFindReceiptCodeWithSite($site_id);
            $receiptCodePfx = $modObj->model->getReceiptCodePrefixWithSite($site_id);

            /* SELECTION OF NEXT RECEIPT CODE FROM SETTING */
            $receiptCode = $fn->getSettingsValueByKey("nextReceiptCode");    
            if ($receiptCode < 10) {
                $receipt_code = $receiptCodePfx . '000' . $receiptCode;
            } else if ($receiptCode < 99) {
                $receipt_code = $receiptCodePfx . '00' . $receiptCode;
            } else if ($receiptCode < 999) {
                $receipt_code = $receiptCodePfx . '0' . $receiptCode;
            } else {
                $receipt_code = $receiptCodePfx . $receiptCode;
            }
            
            /* CREATION OF RECEIPT RECORD */
            $fa = array();
            if ($site_id) {
                $fa['site_id']  = $site_id;
            }
    
            $fa['amount']         = 0;
            $fa['discount_amount']= $fn->getSettingsValueByKey("discountAmount");
            $fa['order_id']       = $order_id;
            $fa['receipt_code']   = $receipt_code;
            //$fa['mode_of_payment']= '';
            $fa['date']           = $year_of_enrollment . '-' . $month . '-' . $rowDate['value'];
            $fa['receipt_status'] = 'Paid';
            $fa['creation_date']  = date("Y-m-d H:i:s");
            $fa['created_by']     = $fn->getSessionParam('userName');
            
            $insertReceiptSQL   = $dbUtil->getInsertSQLStringFromArray($fa, 'receipt');
            $resultSQL          = $db->sql_query($insertReceiptSQL);
            $receipt_id         = $db->sql_nextid();

            /* UPDATION OF RECEIPT CODE IN SETTING TABLE */
            if ($site_id) {
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode' AND site_id = '{$site_id}'";
            } else {
                $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextReceiptCode'";
            }
            $resultUpdate = $db->sql_query($SQLUpdate);
            
            /* INSERTING A RECORD FOR EACH RECEIPT IN HISTORY TABLE (ONE INVOICE CAN HAVE MULTIPLE RECEIPTS) */
            $fa = array();
            $fa['receipt_id']    = $receipt_id;
            $fa['invoice_id']    = $invoice_id;
            $fa['amount']        = 0;
            $fa['creation_date'] = date("Y-m-d H:i:s");
            $histId = $fn->addRecord($fa, 'invoice_receipt_history');

            /* UPDATING INVOICE STATUS TO PAID */
            $sqlUpdateInvoice    = "UPDATE invoice SET status = 'Paid' WHERE invoice_id = {$invoice_id}";
            $resultUpdateInvoice = $db->sql_query($sqlUpdateInvoice);
        }
        /********************************** STEP 7 ENDS HERE ****************************/

        /*
        $modObj = getCPModuleObj('pms_order');
        $modObj->model->getGenerateInvoiceForEntMedia($invoice_id);
        */
    }

    function getCalculateTotalStudentsInBatch() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $batch_id = $fn->getReqParam('batch_id');
        $year     = $fn->getReqParam('year');
        $site_id  = $fn->getSessionParam('cp_site_id');
        
        if ($year == '') {
            $year = date('Y');
        }
        
        $SQL = "
        SELECT COUNT(*) AS total_students_enrolled
        FROM course_contact cc
        LEFT JOIN (contact cont) ON (cc.contact_id = cont.contact_id)
        LEFT JOIN (batch b)      ON (cc.batch_id   = b.batch_id)
        WHERE b.batch_id = {$batch_id}
        AND cont.status = 'Active'
        AND cc.year_of_enrollment = {$year}
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);
        
        $batchRec     = $fn->getRecordRowByID('batch', 'batch_id', $batch_id);
        $balance_seat = $batchRec['max_enroll_count'] - $row['total_students_enrolled'];        
        
        $text = $balance_seat;

        return $text;
    }
}