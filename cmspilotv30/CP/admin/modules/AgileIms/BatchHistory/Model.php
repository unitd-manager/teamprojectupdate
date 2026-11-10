<?
class CP_Admin_Modules_AgileIms_BatchHistory_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {

        $SQL = "
        SELECT DISTINCT cc.course_contact_id
              ,CONCAT_WS(' ', t.first_name, t.last_name ) AS teacher_name
              ,b.title AS batch_title
              ,b.status AS batch_status
              ,c.title AS course_title
              ,cc.contact_id
              ,ct.first_name 
              ,ct.email 
              ,ct.phone 
              ,cc.evaluate_status
              ,cc.certificate_status
              ,cc.flag
              ,cc.creation_date
              ,cc.modification_date
              ,cc.created_by
              ,cc.modified_by
        FROM course_contact cc 
        JOIN course c ON (c.course_id = cc.course_id)
        JOIN contact ct ON (ct.contact_id = cc.contact_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        LEFT JOIN teacher t ON (t.teacher_id = b.teacher_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'b';

        $course_id    = $fn->getReqParam('course_id');
        $teacher_id   = $fn->getReqParam('teacher_id');
        $batch_id     = $fn->getReqParam('batch_id');
        $batch_status = $fn->getReqParam('batch_status');
        $course_contact_id     = $fn->getReqParam('course_contact_id');

        if ($batch_status != "") {
            $searchVar->sqlSearchVar[] = "b.status = '{$batch_status}'";
        }
        else{
            $searchVar->sqlSearchVar[] = "b.status = 'Open'";
        }
        
        if ($course_contact_id != "") {
            $searchVar->sqlSearchVar[] = "cc.course_contact_id = '{$course_contact_id}'";
        } else if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "cc.course_contact_id = '{$tv['record_id']}'";
        } else {

            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'b.batch_id');

            if ($tv['keyword'] != "") {
                $searchVar->sqlSearchVar[] = "(
											   ct.first_name LIKE '%{$tv['keyword']}%' OR
											   ct.last_name  LIKE '%{$tv['keyword']}%' OR
											   b.title       LIKE '%{$tv['keyword']}%' OR
											   c.title       LIKE '%{$tv['keyword']}%'
                )";
            }
            
            if ($course_id != '' ) {
                $searchVar->sqlSearchVar[] = "cc.course_id = {$course_id}";
            }
            
            if ($teacher_id != '' ) {
                $searchVar->sqlSearchVar[] = "b.teacher_id = {$teacher_id}";
            }
        }        
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();
        $validate->validateData('title', 'Please enter the title');

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
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

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

        if (!$this->getEditValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();
        $id = $fn->saveRecord($fa, $tableName, $keyField, $keyFieldVal);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getFields() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'certificate_status');
        
        //-----------------------------------------------------------------------//
        /*if($cpCfg['generateSEOUrl'] == 1 && ($tv['lang'] == "eng" || $tv['lang'] == "")){
            $fa['seo_title'] = strtolower( $fn->_prepare_url_text($fa[$titleLang]));
        }*/

        return $fa;
    }
    /**
     *
     */
    function getAgileImsBatchAgileImsContactLinkSQL($id) {
        $formObj = Zend_Registry::get('formObj');

        $contactFld = ($formObj->mode == 'edit') ? 'c.contact_id' : 'c.first_name AS contact_name';

        $SQL = "
        SELECT cc.course_contact_id
              ,c.first_name
              ,c.last_name
              ,c.email
        FROM course_contact cc 
        LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
        LEFT JOIN batch b ON (b.batch_id = cc.batch_id)
        WHERE cc.batch_id = '{$id}'
        ORDER BY cc.course_contact_id
        ";

        return $SQL;
    }
    /**
     *
     */
    function getBulkUpdateEvaluate() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUrl = Zend_Registry::get('cpUrl');
        $db = Zend_Registry::get('db');
               
        $batch_id= $fn->getReqParam('id');
        $rows = '';
        $formAction = "index.php?module=agileIms_batch&_spAction=bulkUpdateEvaluateSubmit&showHTML=0";

        $SQL = "
        SELECT cc.course_contact_id
            ,CONCAT(c.first_name, ' ', c.last_name) AS trainee_name
        FROM course_contact cc
        LEFT JOIN contact c ON (c.contact_id = cc.contact_id)
        WHERE batch_id = {$batch_id}
        ";
        $result = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= $this->getBatchRecords($row['course_contact_id'], $row['trainee_name']);
        }
        
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <table id='courseLinkList' class='thinlist'>
                {$rows}
            </table>    
            <input type='hidden' name='batch_id' value='{$batch_id}' />
        </form>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getBulkUpdateEvaluateSubmit() {
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');
        $db = Zend_Registry::get('db');
        
        if (!$this->getBulkUpdateEvaluateValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $course_contact_arr  = $fn->getPostParam('course_contact_id', array());
        
        $count = count($course_contact_arr);
        for ($i= 0; $i< $count; $i++){
            $course_contact_id = $course_contact_arr[$i];
            $pfx = $course_contact_id . '_';
            $evaluate_status  = $fn->getPostParam("{$pfx}evaluate_status");
            $fa = array();
            $fa['evaluate_status']   = $evaluate_status;
            $fa['course_contact_id'] = $course_contact_id;
            $fn->saveRecord($fa, 'course_contact', 'course_contact_id', $course_contact_id);
        }

        return $validate->getSuccessMessageXML();

    }
    
    /**
     *
     */
    function getBulkUpdateEvaluateValidate() {
        $ln = Zend_Registry::get('ln');
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');

        //==================================================================//
        $validate->resetErrorArray();
        //$validate->validateData("email"       , $ln->gd("cp.form.fld.email.err")      , "email");

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     *
     */
    function getPrintVoucher() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        //include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new FPDF();
        $pdf->SetFont('Arial','B',14);

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $product_id  = $fn->getReqParam('id');
		$invoice_terms = '';
		$notes  = '';
        $total = '';
        
		$SQL = "
		SELECT pv.voucher_no
            ,pv.product_id
            ,p.title as product_title
		FROM product_voucher pv
		JOIN product p ON (pv.product_id = p.product_id)
		WHERE pv.product_id = {$product_id}
		ORDER BY pv.product_voucher_id
		";		
        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);
		if ($numRows == 0){
            $pdf->SetXY(60,30);
            $pdf->Cell(50, 20, "Please set the values for your Voucher and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        $rows = "";

        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                $pdf->Image('images/sgdealon_banner.jpg',0,0,210, 30);
                $pdf->SetY(32);
                $product_title = "Please find the Voucher Codes for the Product : " ;
                //$pdf->WordWrap($product_title, 200);
                $pdf->Write(5, $product_title);
                $pdf->Ln(8);
                $pdf->drawTextBox($row['product_title'], 195, 32, 'L', 'T', 0);
                $pdf->Ln(10);
            }
             //Table Content
            $voucher_no = $row['voucher_no'];
            $count++;
            //$pdf->Write(5, "Voucher No " . $count . ': ' . $voucher_no);
            $pdf->Cell(60, 5, "Voucher No " . $count . ': ' . $voucher_no, 1);
            if ($count % 3){
            }
            else{
                $pdf->Ln(10);
            }
        }
        //Final Values
        $pdf->Output();
    }
    
    /**
     *
     */
    function getBatchRecords($course_contact_id, $trainer_name){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $row = $fn->getRecordRowByID('course_contact', 'course_contact_id', $course_contact_id);
        $pfx = $course_contact_id . '_' ;
        $text = "
        <tr>
            <td>{$trainer_name}</td>
            <td>{$formObj->getYesNoRRow('Competent', "{$pfx}evaluate_status", $row['evaluate_status'])}</td>
            <input type='hidden' name='course_contact_id[]' value='{$course_contact_id}' />
        </tr>
        ";

        return $text;
    }
}