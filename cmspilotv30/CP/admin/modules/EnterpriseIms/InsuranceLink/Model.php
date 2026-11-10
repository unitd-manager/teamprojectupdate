<?
class CP_Admin_Modules_EnterpriseIms_InsuranceLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        $validate->resetErrorArray();

        $validate->validateData('code', 'Please enter code');
        
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
        $db = Zend_Registry::get('db');
        
        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }
            
        $invoiceHistId  = $fn->getPostParam('invoiceHistId', array());
        $count = count($invoiceHistId);

        $count = 0;
        
        foreach($invoiceHistId AS $invoiceHistId){
            $invoiceRec = $fn->getRecordRowByID('invoice_receipt_history', 'invoice_receipt_history_id', $invoiceHistId);
            
            $course_id      = $fn->getReqParam('course_id');
            $order_id       = $fn->getReqParam('order_id');
            
            $sqlCourseContact = "
            SELECT course_contact_id FROM course_contact
            WHERE course_id = {$course_id}
              AND order_id = {$order_id}
            ";
            $resultCourseContact = $db->sql_query($sqlCourseContact);
            $rowCourseContact    = $db->sql_fetchrow($resultCourseContact); 
            
            $fa = $this->getFields();
            $fa['course_contact_id'] = $rowCourseContact['course_contact_id']; // Saving Course Contact ID in Student Insurance table
            $fa['invoice_receipt_history_id'] = $invoiceRec['invoice_receipt_history_id'];
            $fa['installment_id'] = $invoiceRec['installment_id'];
            $fa['premium_amount'] = $invoiceRec['amount'];
            $fa['status'] = 'Paid';
            
            $id = $fn->addRecord($fa);
        }

        return $validate->getSuccessMessageXML();
    }
    
    /**
     */
    function getAdd1(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        
        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }
            
        $invoiceHistId  = $fn->getPostParam('invoiceHistId', array());
        $count = count($invoiceHistId);

        $count = 0;
        
        foreach($invoiceHistId AS $invoiceHistId){
            $invoiceRec = $fn->getRecordRowByID('installment', 'installment_id', $invoiceHistId);
            
            $course_id      = $fn->getReqParam('course_id');
            $order_id       = $fn->getReqParam('order_id');
            
            $sqlCourseContact = "
            SELECT course_contact_id FROM course_contact
            WHERE course_id = {$course_id}
              AND order_id = {$order_id}
            ";
            $resultCourseContact = $db->sql_query($sqlCourseContact);
            $rowCourseContact    = $db->sql_fetchrow($resultCourseContact); 
            
            $fa = $this->getFields();
            $fa['course_contact_id'] = $rowCourseContact['course_contact_id'];
            // Saving Course Contact ID in Student Insurance table
            $fa['installment_id'] = $invoiceRec['installment_id'];
            $fa['premium_amount'] = $invoiceRec['amount'];
            $fa['status'] = 'Paid';
            
            $id = $fn->addRecord($fa);
        }

        return $validate->getSuccessMessageXML();
    }
    
    /**
     */
    function getEditPortalValidate() {
        return $this->getNewValidate();
    }

    /**
     */
    function getSaveOLd(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        
        $course_id  = $fn->getReqParam('course_id');
        $order_id   = $fn->getReqParam('order_id');
        
        $sqlCourseContact = "
        SELECT course_contact_id FROM course_contact
        WHERE course_id = {$course_id}
          AND order_id = {$order_id}
        ";
        $resultCourseContact = $db->sql_query($sqlCourseContact);
        $rowCourseContact    = $db->sql_fetchrow($resultCourseContact); 

        if (!$this->getEditPortalValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $fa['course_contact_id'] = $rowCourseContact['course_contact_id']; // Saving Course Contact ID in Student Insurance table

        $id = $fn->saveRecord($fa);
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'course_id');
        $fa = $fn->addToFieldsArray($fa, 'insurance_id');
        $fa = $fn->addToFieldsArray($fa, 'code');
        $fa = $fn->addToFieldsArray($fa, 'insurance_start_date');
        $fa = $fn->addToFieldsArray($fa, 'insurance_end_date');
        $fa = $fn->addToFieldsArray($fa, 'order_id');
        
        return $fa;
    }
}
