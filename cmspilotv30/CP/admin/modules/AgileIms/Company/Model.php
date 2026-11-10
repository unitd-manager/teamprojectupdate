<?
class CP_Admin_Modules_AgileIms_Company_Model extends CP_Common_Modules_AgileIms_Company_Model
{
    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the company name');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the company name');
        $validate->validateData('reg_number', 'Please enter the registration number');
        $validate->validateData('category', 'Please select type of registration');
        $validate->validateData('phone', 'Please enter phone no.');
        $validate->validateData('email', 'Please enter company email');

        $validate->validateData('address1', 'Please enter address 1');
        $validate->validateData('address_country_code', 'Please select the country');
        $validate->validateData('address_po_code', 'Please enter the postal code');

        $validate->validateData('contact_name', 'Please enter the contact person name');
        $validate->validateData('contact_phone', 'Please enter contact person no.');

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();
        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'reg_number');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'email');

        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');

        $fa = $fn->addToFieldsArray($fa, 'salutation');
        $fa = $fn->addToFieldsArray($fa, 'contact_name');
        $fa = $fn->addToFieldsArray($fa, 'contact_phone');
        $fa = $fn->addToFieldsArray($fa, 'contact_mobile');
        $fa = $fn->addToFieldsArray($fa, 'contact_email');
        $fa = $fn->addToFieldsArray($fa, 'contact_position');

        return $fa;
    }
    /**
     *
     */
    function getAgileImsCompanyAgileImsOrderLinkSQL($id) {

        $SQL = "
        SELECT a.order_id
              ,a.order_date
        FROM `order` a
        WHERE a.company_id = '{$id}'
        ORDER BY a.order_date
        ";

        return $SQL;
    }

    /**
     *
     */
    function getAgileImsCompanyAgileImsContactLinkSQL($id) {

        return "
        SELECT a.contact_id
              ,a.first_name
              ,a.email
              ,a.registration_no
              ,a.id_card_no
              ,a.phone
              ,a.mobile
        FROM company b, contact a
        WHERE a.company_id = b.company_id
          AND b.company_id = {$id}
        ";

    }

    /**
     *
     */
    function getCompanyAddSubmit(){
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        if (!$this->getCompanyAddValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'title');
        $fa = $fn->addToFieldsArray($fa, 'reg_number');
        $fa = $fn->addToFieldsArray($fa, 'category');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'email');

        $fa = $fn->addToFieldsArray($fa, 'address1');
        $fa = $fn->addToFieldsArray($fa, 'address2');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');

        $company_id = $fn->addRecord($fa, 'company');

        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getPrintCourseConfirmation() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        $dateUtil = Zend_Registry::get('dateUtil');

        //-----------------------------------------------------------------//
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/tbs_class.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_opentbs.php');
        include_once(CP_LIBRARY_PATH.'lib_php/tbs_us/plugins/tbs_plugin_html.php');

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $company_id  = $fn->getReqParam('id');
        $order_id    = $fn->getReqParam('order_id');
        $template    = 'Course Confirmation.docx';
        $templatePath = $cpCfg['cp.localPath'].'lib/template/' . $template;
        $TBS->LoadTemplate($templatePath);
        $rnd_no = mt_rand();
        $file_name = 'Course_Confirmation_' . $company_id . '_' . $rnd_no . '.docx';
        $file_name = str_replace('.', '_' . date('Y-m-d') . '.', $file_name);

        $path = realpath($cpCfg['cp.mediaFolder']) . '\temp';
        $file_name_save = $path . '\\' . $file_name;
        $sourceFilePath = $file_name_save;
        $today =  date('d/m/Y');

        $SQL = "
        SELECT DISTINCT cc.course_contact_id
              ,cc.discount
              ,cc.evaluate_status
              ,cc.course_status
              ,c.title AS course_title
              ,c.price
              ,b.title AS batch_title
              ,cont.first_name AS student_name
              ,cont.id_card_no AS contact_id_card_no
              ,o.order_date
              ,b.start_time
              ,b.end_time
              ,cp.contact_name
              ,o.order_id
        FROM course_contact cc
        LEFT JOIN course c ON (c.course_id = cc.course_id)
        LEFT JOIN contact cont ON (cont.contact_id = cc.contact_id)
        LEFT JOIN `order` o ON (o.order_id = cc.order_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        LEFT JOIN company cp ON (cp.company_id = cc.company_id)
        WHERE cc.company_id = {$company_id}
        AND o.order_id = {$order_id}
        ORDER BY o.order_date DESC
        ";
        $result  = $db->sql_query($SQL);
        $result1 = $db->sql_query($SQL);
        $row     = $db->sql_fetchrow($result);
        $numRows = $db->sql_numrows($result);
        $blkStd      = array();
        $serialNo    = 1;
        $arr         = array();
        $blkMain     = array();
        $blkRegNo    = array();
        $blkSerialNo = array();

        while ($rowz = $db->sql_fetchrow($result1)) {

            $arr['id_card_no']   = $rowz['contact_id_card_no'];
            $arr['serial_no']    = $serialNo;
            $arr['student_name'] = $rowz['student_name'];
            //$arr['course_title']  = $row['course_title'];
            //$arr['subject_title'] = $row['subject_title'];
            //$arr['batch_code']    = $row['batch_code'];
            $blkMain[] = $arr;

            $serialNo++;
        }

        $order_date = $fn->getCPDate($row['order_date'], 'd-m-Y');
        $totalPrice = $numRows * $row['price'];

        $arr1 = array();
        $arr1['start_time']   = $row['start_time'];
        $arr1['end_time']     = $row['end_time'];
        $arr1['contact_name'] = $row['contact_name'];
        $arr1['order_date']   = $order_date;
        $arr1['course_title'] = $row['course_title'];
        $arr1['persons']      = $numRows;
        $arr1['fees']         = $row['price'];
        $arr1['total']        = $totalPrice;
        $blkStd[] = $arr1;

        $TBS->MergeBlock('blkMain', $blkMain);
        $TBS->MergeBlock('blkStd', $blkStd);
        //$TBS->MergeBlock('blkRegNo', $blkRegNo);
        $TBS->MergeBlock('blkSerialNo', $blkSerialNo);
        $TBS->Show(OPENTBS_DOWNLOAD, $file_name);
    }

    /**
     *
     */
    function getCompanyAddValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();

        $validate->validateData('title', 'Please enter company name');
        $validate->validateData('reg_number' , 'Please enter business reg. No.');

        $reg_number = $fn->getPostParam('reg_number', '', true);

        if ($reg_number != ''){
            $rec = $fn->getRecordByCondition('company', "reg_number = '{$reg_number}'");
            $expEmail = array('displayText' => $reg_number,  'target' => '_blank');
            $emailLink = $fn->getRecordDetailLink('agileIms_company', 'record_id', $rec['company_id'], $expEmail);

            if (is_array($rec)){
                $validate->errorArray['reg_number']['name'] = "reg_number";
                $validate->errorArray['reg_number']['msg']  = "Company already registered. '{$emailLink}'";

            }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
    /********************************* PROCESS ************************************/
    /* ACTION: COMPANY MODULE : COMPANY - COURSE LINK - WHEN YOU CLICK 'CANCEL' LINK/BUTTON.
    * STEP 1: UPDATING ENROLLMENT STATUS TO 'CANCELLED' IN COURSE CONTACT TABLE
    * STEP 2: UPDATING ORDER STATUS TO 'CANCELLED' IN ORDER TABLE
    * STEP 3: UPDATING INVOICE STATUS TO 'CANCELLED' IN INVOICE TABLE
    * STEP 4: UPDATING RECEIPT STATUS 'TO CANCELLED' IN RECEIPT TABLE
    /******************************* END PROCESS **********************************/
    function getCancelEnrollmentForCompany(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $validate = Zend_Registry::get('validate');
        
        $order_id = $fn->getReqParam('order_id');        
        /********************************** STEP 1 **************************************/
        $faCc = array();
        $faCc['modification_date'] = date('Y-m-d H:i:s');
        $faCc['modified_by']       = $fn->getSessionParam('userName');
        $faCc['course_status']     = 'Cancelled'; 
        $fn->saveRecord($faCc, 'course_contact', 'order_id', $order_id);
        /********************************** STEP 1 ENDS HERE ****************************/
        /********************************** STEP 2 **************************************/
        $faO = array();
        $faO['modification_date'] = date('Y-m-d H:i:s');
        $faO['modified_by']       = $fn->getSessionParam('userName');
        $faO['order_status']      = 'Cancelled'; 
        $fn->saveRecord($faO, 'order', 'order_id', $order_id);
        /********************************** STEP 2 ENDS HERE ****************************/
        /********************************** STEP 3 **************************************/
        $expInv = array('customWhereCondn' => 'status != "Cancelled"');
        $faInv = array();
        $faInv['modification_date'] = date('Y-m-d H:i:s');
        $faInv['modified_by']       = $fn->getSessionParam('userName');
        $faInv['status']            = 'Cancelled'; 
        $fn->saveRecord($faInv, 'invoice', 'order_id', $order_id, $expInv);
        /********************************** STEP 3 ENDS HERE ****************************/
        /********************************** STEP 4 **************************************/
        $expRec = array('customWhereCondn' => 'status != "Cancelled"');
        $faRec = array();
        $faRec['modification_date'] = date('Y-m-d H:i:s');
        $faRec['modified_by']       = $fn->getSessionParam('userName');
        $faRec['receipt_status']    = 'Cancelled'; 
        $fn->saveRecord($faRec, 'receipt', 'order_id', $order_id, $expRec);
        /********************************** STEP 4 ENDS HERE ****************************/
        return $validate->getSuccessMessageXML();
    }
}
