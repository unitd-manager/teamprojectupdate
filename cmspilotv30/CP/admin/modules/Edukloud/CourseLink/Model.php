<?
class CP_Admin_Modules_Edukloud_CourseLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getFields(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        if ($tv['srcRoom'] == 'edukloud_company'){
            $fa = $fn->addToFieldsArray($fa, 'company_id');
        }
        $fa = $fn->addToFieldsArray($fa, 'course_id');
        $fa = $fn->addToFieldsArray($fa, 'batch_id');
        $fa = $fn->addToFieldsArray($fa, 'course_subsidy_history_id');
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
    function getNewValidateForSubsidy($course_subsidy_history_id) {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $validate->validateData('course_id', 'Please select the course');

        $validate->resetErrorArray();
        if($course_subsidy_history_id){
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
    */
    function getAdd() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $course_subsidy_history_id = $fn->getPostParam('course_subsidy_history_id');
        $batch_id = $fn->getPostParam('batch_id');
        $subsidy_code = $fn->getPostParam('subsidy_code');
        $is_citizen   = $fn->getPostParam('is_citizen');
        $subsidyTotal = '';
        $discTotal    = '';
        
        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        if ($is_citizen == 1 && !$this->getNewValidateForSubsidy($course_subsidy_history_id)) {
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $discount         = $fn->getPostParam('discount');
        $fa['discount']   = $discount;
        $id = $fn->addRecord($fa);
        // You need to get the max count from batch and make the status accordingly. Get the total number of students in a batch frmo course contact
        if ($batch_id) {
            $SQLCC = "
            SELECT COUNT(course_contact_id) AS actual_count
            FROM course_contact
            WHERE batch_id = {$batch_id}
            ";
            $resultCC  = $db->sql_query($SQLCC);
            //$rowCC     = $db->sql_fetchrow($resultCC);
            $numRows  = $db->sql_numrows($resultCC);
            $batchRec = $fn->getRecordRowByID('batch', 'batch_id', $batch_id);
            
            if($numRows == $batchRec['max_enroll_count']){
                $sqlUpdate = "
                UPDATE batch 
                set status = 'Closed'
                WHERE batch_id = {$batch_id}
                ";
                $resultUpdate = $db->sql_query($sqlUpdate);
            }  
        }

        $subsidy_paid_history_id = '';       
        $course_contact_id = $id ;
        
        if ($fa['course_id'] > 0){
            // to check invoice raised or not;
            // To check in course_contact - order_id has value or not.
            $ccRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);

            if ($ccRec['order_id'] == ''){
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $ccRec['course_id']);
                $contactRec = $fn->getRecordRowByID('contact', 'contact_id', $ccRec['contact_id']);

                $fa = array();
                $fa['contact_id']      = $ccRec['contact_id'];
                $fa['payment_method']  = '';
                $fa['module']          = 'edukloud_course';
                $fa['order_status']    = 'Due';
                $fa['order_date']      =  date('Y-m-d');
                $fa['contact_module']  = 'edukloud_contact';

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

                $fa = array();
                $fa['order_id']   = $order_id;
                $fa['contact_id'] = $ccRec['contact_id'];
                $fa['module']     = 'edukloud_course';
                $fa['record_id']  = $ccRec['course_id'];
                $fa['qty']        = 1;
                $fa['item_title'] = $courseRec['title'];
                $fa['unit_price'] = $courseRec['price'];
                $fn->addRecord($fa, 'order_item');
                
                if ($ccRec['course_subsidy_history_id'] > 0){
                
                    /* Inserting Subsidy code in Subsidy History table */
                    if ($subsidy_code){
                        $fa = array();
                        $fa['order_id']         = $order_id;
                        $fa['subsidy_code']     = $subsidy_code;
                        $fa['status']           = 'Due';
                        $fa['creation_date']    = date("Y-m-d H:i:s");
                        
                        $subsidy_paid_history_id = $fn->addRecord($fa, 'subsidy_paid_history');       
                    }
                
                    $sqlSubsidy = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                    WHERE csh.course_subsidy_history_id = {$ccRec['course_subsidy_history_id']}
                    ";
                    $resultSubsidy  = $db->sql_query($sqlSubsidy);
                    $rowSubsidy     = $db->sql_fetchrow($resultSubsidy);
                    if($rowSubsidy['mode_of_calculation'] == 'Value'){
                        $subsidyTotal = $rowSubsidy['value'];
                    }
                    else{
                        $subsidyTotal = ($courseRec['price']*$rowSubsidy['value'])/100;
                    }
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'edukloud_subsidy';
                    $fa['record_id']  = $ccRec['course_id'];
                    $fa['qty']        = 1;
                    $fa['item_title'] = $rowSubsidy['title'];
                    $fa['unit_price'] = -$subsidyTotal;
                    $fa['contact_id'] = $ccRec['contact_id'];
                    $fn->addRecord($fa, 'order_item');
                }
                
                if ($ccRec['discount'] > 0){
                    $sqlDiscount = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                    WHERE csh.course_subsidy_history_id = {$ccRec['discount']}
                    ";
                    $resultDiscount  = $db->sql_query($sqlDiscount);
                    $rowDiscount     = $db->sql_fetchrow($resultDiscount);
                    if($rowDiscount['mode_of_calculation'] == 'Value'){
                        $discTotal = $rowDiscount['value'];
                    }
                    else{
                        $discTotal = ($courseRec['price']*$rowDiscount['value'])/100;
                    }
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'edukloud_discount';
                    $fa['record_id']  = $ccRec['course_id'];
                    $fa['qty']        = 1;
                    $fa['item_title'] = $rowDiscount['title'];
                    $fa['unit_price'] = -$discTotal;
                    $fa['contact_id'] = $ccRec['contact_id'];
                    $fn->addRecord($fa, 'order_item');
                }
                    
                $cshRec = $fn->getRecordRowByID('course_subsidy_history', 'course_subsidy_history_id', $discount);

                $fa = array();
                $fa['order_id']   = $order_id;
                $fa['discount']   = $cshRec['subsidy_discount_id'];
                $fa['subsidy_paid_history_id'] = $subsidy_paid_history_id;
                $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id );   
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

        if (!$this->getEditValidate()) {
            return $validate->getErrorMessageXML();
        }
        $subsidyTotal = '';

        $fa = $this->getFields();
        //if ($fa['course_id'] > 0){
            /*
            To check invoice raised or not;
            To check in course_contact - order_id has value or not, 
            if not then create order, order item records.
            */
            $course_contact_id  = $fn->getPostParam('course_contact_id', '', true);
            $course_subsidy_id  = $fn->getPostParam('course_subsidy_history_id', '', true);
            $batch_id           = $fn->getPostParam('batch_id', '', true);
            $discount           = $fn->getPostParam('discount', '', true);
            $subsidy_code       = $fn->getPostParam('subsidy_code');

            $ccRec = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);

            // If the order record is not created following codes will get executed
            if (is_array($ccRec) && $ccRec['order_id'] == ''){
                $courseRec = $fn->getRecordRowByID('course', 'course_id', $ccRec['course_id']);

                $fa = array();
                $fa['contact_id']      = $ccRec['contact_id'];
                $fa['payment_method']  = '';
                $fa['module']          = 'edukloud_course';
                $fa['module']          = 'edukloud_course';
                $fa['order_date']      =  date('Y-m-d');
                $fa['contact_module']  = 'edukloud_contact';
                $order_id = $fn->addRecord($fa, 'order');
            
                $fa = array();
                $fa['order_id']   = $order_id;
                $fa['module']     = 'edukloud_course';
                $fa['record_id']  = $ccRec['course_id'];
                $fa['qty']        = 1;
                $fa['item_title'] = $courseRec['title'];
                $fa['unit_price'] = $courseRec['price'];
                $fn->addRecord($fa, 'order_item');
                
                // If subsidy record has value
                if ($fa['course_subsidy_id'] > 0){
                    $sqlSubsidy = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                    WHERE csh.course_subsidy_history_id = {$ccRec['course_subsidy_history_id']}
                    ";
                    $resultSubsidy  = $db->sql_query($sqlSubsidy);
                    $rowSubsidy     = $db->sql_fetchrow($resultSubsidy);
                    if($rowSubsidy['mode_of_calculation'] == 'Value'){
                        $subsidyTotal = $rowSubsidy['value'];
                    }
                    else{
                        $subsidyTotal = ($courseRec['price']*$rowSubsidy['value'])/100;
                    }
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'edukloud_subsidy';
                    $fa['contact_id'] = $ccRec['contact_id'];
                    $fa['record_id']  =  $ccRec['course_id'];
                    $fa['qty']        = 1;
                    $fa['item_title'] = $rowSubsidy['title'];
                    $fa['unit_price'] = -$subsidyTotal;
                    $fn->addRecord($fa, 'order_item');
                }

                // If discount record has value
                if ($fa['discount'] > 0){
                    $sqlDiscount = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                    WHERE csh.course_subsidy_history_id = {$ccRec['discount']}
                    ";
                    $resultDiscount  = $db->sql_query($sqlDiscount);
                    $rowDiscount     = $db->sql_fetchrow($resultDiscount);
                    $fa = array();
                    $fa['order_id']   = $order_id;
                    $fa['module']     = 'edukloud_discount';
                    $fa['contact_id'] = $ccRec['contact_id'];
                    $fa['record_id']  = $ccRec['course_id'];
                    $fa['qty']        = 1;
                    $fa['item_title'] = $rowDiscount['title'];
                    $fa['unit_price'] = -$rowDiscount['value'];
                    $fn->addRecord($fa, 'order_item');
                }
        
                $fa = array();
                $fa['order_id']   = $order_id;
                $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id );
            }

            //if there is change in subsidy/batch record below code will update the subsidy in order items
            else if(is_array($ccRec) && $ccRec['order_id'] != ''){
                $expOrderItemSubsidy = array('condn' => " AND module = 'edukloud_subsidy'");
                $orderItemRecSubsidy = $fn->getRecordRowByID('order_item', 'order_id', $ccRec['order_id'], $expOrderItemSubsidy);
                // If subsidy is not empty add in course contact and order_item
                if ($course_subsidy_id != ''){
                    $fa = array();
                    $fa['course_subsidy_history_id'] = $course_subsidy_id;
                    $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id );

                    $sqlSubsidy = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                    WHERE csh.course_subsidy_history_id = {$course_subsidy_id}
                    ";
                    $resultSubsidy  = $db->sql_query($sqlSubsidy);
                    $rowSubsidy     = $db->sql_fetchrow($resultSubsidy);
                    if ($orderItemRecSubsidy['order_item_id']){
                        $fa = array();
                        $fa['item_title'] = $rowSubsidy['title'];
                        $fa['unit_price'] = -$rowSubsidy['value'];
                        $fn->saveRecord($fa, 'order_item', 'order_item_id', $orderItemRecSubsidy['order_item_id']);
                    }
                    else{
                        $fa = array();
                        $fa['order_id']   = $ccRec['order_id'];
                        $fa['module']     = 'edukloud_subsidy';
                        $fa['contact_id'] = $ccRec['contact_id'];
                        $fa['record_id']  = $ccRec['course_id'];
                        $fa['qty']        = 1;
                        $fa['item_title'] = $rowSubsidy['title'];
                        $fa['unit_price'] = -$rowSubsidy['value'];
                        $fn->addRecord($fa, 'order_item');
                    }
                    
                }
                else{
                    $fa = array();
                    $fa['course_subsidy_history_id'] = '';
                    $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id );

                    $fa = array();
                    $fa['item_title'] = '';
                    $fa['unit_price'] = '';
                    $fn->saveRecord($fa, 'order_item', 'order_item_id', $orderItemRecSubsidy['order_item_id']);
                }

                // If discount is not empty add in course contact and order_item
                if ($discount){
                    $expOrderItemDiscount = array('condn' => " AND module = 'edukloud_discount'");
                    $orderItemRecDiscount = $fn->getRecordRowByID('order_item', 'order_id', $ccRec['order_id'], $expOrderItemDiscount);
                    
                    $fa = array();
                    $fa['discount'] = $discount;
                    $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id );

                    $sqlDiscount = "
                    SELECT sd.*
                    FROM subsidy_discount sd
                    LEFT JOIN (course_subsidy_history csh) ON (sd.subsidy_discount_id = csh.subsidy_discount_id)
                    WHERE csh.course_subsidy_history_id = {$discount}
                    ";
                    $resultDiscount  = $db->sql_query($sqlDiscount);
                    $rowDiscount     = $db->sql_fetchrow($resultDiscount);

                    if ($orderItemRecDiscount['order_item_id']){
                        $fa = array();
                        $fa['item_title'] = $rowDiscount['title'];
                        $fa['unit_price'] = -$rowDiscount['value'];
                        $fn->saveRecord($fa, 'order_item', 'order_item_id', $orderItemRecDiscount['order_item_id']);
                    }
                    else{
                        $fa = array();
                        $fa['order_id']   = $ccRec['order_id'];
                        $fa['module']     = 'edukloud_discount';
                        $fa['record_id']  = $ccRec['course_id'];
                        $fa['qty']        = 1;
                        $fa['contact_id'] = $ccRec['contact_id'];
                        $fa['item_title'] = $rowDiscount['title'];
                        $fa['unit_price'] = -$rowDiscount['value'];
                        $fn->addRecord($fa, 'order_item');
                    }
                }
                else{
                    $expOrderItemDiscount = array('condn' => " AND module = 'edukloud_discount'");
                    $orderItemRecDiscount = $fn->getRecordRowByID('order_item', 'order_id', $ccRec['order_id'], $expOrderItemDiscount);

                    $fa = array();
                    $fa['item_title'] = '';
                    $fa['unit_price'] = '';
                    $fn->saveRecord($fa, 'order_item', 'order_item_id', 
                    $orderItemRecDiscount['order_item_id']);
                }
                //To save edited value in subsidy paid history table
                $fa2 = array();
                $fa2['subsidy_code'] = $subsidy_code;
                $fn->saveRecord($fa2, 'subsidy_paid_history', 'subsidy_history_id', 
                 $ccRec['subsidy_paid_history_id']);
                 
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
                $fa['module']     = 'edukloud_course';
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
                        if($full_time == 1){
                            $subjectTotal = $rowSubject['fees'];
                        }
                        else{
                            //$subjectTotal = $rowSubject['fees'] - 255;
                            $subjectTotal = 1125;
                         }

                        $fa = array();
                        $fa['order_id']   = $order_id;
                        $fa['module']     = 'edukloud_subject';
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
                    $fa['module']     = 'edukloud_discount';
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
        $fa['module']          = 'edukloud_course';
        $fa['order_status']    = 'Due';
        $fa['no_of_installment']= $installment;
        $fa['registration_type']= $registration_type;
        $fa['medical_insurance']= $medical_insurance;
        $fa['add_registration_fee']= $add_registration_fee;
        $fa['order_date']      =  date('Y-m-d');
        $fa['contact_module']  = 'edukloud_contact';
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
                         AND module = 'edukloud_subject'
            ";
            $resultSubject  = $db->sql_query($DeleteSQL);  

            //to save the changed course details in order_item
            $courseRec = $fn->getRecordRowByID('course', 'course_id', $course_id);
            $expOrderItemCourse = array('condn' => " AND module = 'edukloud_course'");
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
            $expOrderItemSubsidy = array('condn' => " AND module = 'edukloud_subsidy'");
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
                    $fa['module']     = 'edukloud_subject';
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
                             AND module = 'edukloud_subject'
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
                             AND module = 'edukloud_subject'
                ";
                $resultSubject  = $db->sql_query($DeleteSQL);                  
            }
            
            // If discount is not empty add in course contact and order_item
            $expOrderItemDiscount = array('condn' => " AND module = 'edukloud_discount'");
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
                    $fa['module']     = 'edukloud_discount';
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
    function getDiscountValueForPvt($onlyTotal = "", $discount="", $course_id = "", $medical_insurance = "", $full_time = "" ){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $count = '';
        
        if($full_time == ''){
            $full_time = $fn->getReqParam('full_time');
        }
        
        if($medical_insurance == ''){
            $medical_insurance = $fn->getReqParam('medical_ins');
            if($medical_insurance == 1){
                $medical_insurance = $fn->getSettingsValueByKey("medicalInsuranceFeePvt");
            }
        }

        if($discount == ''){
            $discount  = $fn->getReqParam('discount');
        }
        
        if($course_id == ''){
            $course_id = $fn->getReqParam('course_id');
        }

        $courseRec       = $fn->getRecordRowByID('course', 'course_id', $course_id);
        $discount_amount = ($courseRec['price'] * $discount) / 100;
        
        if (isset($_SESSION['selectedSubjectIds'])){
            $count = count($_SESSION['selectedSubjectIds']);
        }
        
        if ($count != ''){
            $subject_total = '';
            $selectedSubjectIds = join(',', $_SESSION['selectedSubjectIds']);
            
            $SQL  = "
            SELECT s.*
            FROM subject s
            WHERE s.subject_id IN ({$selectedSubjectIds})
            ";
            $result  = $db->sql_query($SQL);  
            $numRows = $db->sql_numrows($result);
            
            while ($row = $db->sql_fetchrow($result)) {
                //$subject_total += $row['fees'];
                if($full_time != 1){
                    if($row['title'] == 'Science Lab'){
                        $row['fees'] = 400;
                    }
                    else{
                        $row['fees'] = 1125;
                    }
                }
                $subject_total += $row['fees'];
            }
            $total           = $subject_total + $medical_insurance;
            $discount_amount = ($total * $discount) / 100;
            $discount_amount = round($discount_amount, 3);
        }
    
        $text = "
        <td>Discount</td>
        <td class='amount txtRight'>{$discount_amount}</td>
        ";
        if($onlyTotal == 1){
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
                if($full_time == 1){
                    $subject_total += $row['fees'];
                }
                else{
                    if($row['title'] == 'Science Lab'){
                        $subject_total += 400;
                    }
                    else{
                        if($no_of_months != 9 && $no_of_months != ''){
                            $subject_total += (1125/9)* $no_of_months;
                        }
                        else{
                            $subject_total += 1125;
                        }
                    }
                    //$subjectTotal += $row['fees'] - 255;
                    //$subject_total += $subject_total;
                    //$subject_total = $subjectTotal;
                }
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
        <td>Intstallment Amount</td>
        <td class='amount txtRight'>{$instAmount}</td>
        ";
        
        return $text;
    }
}