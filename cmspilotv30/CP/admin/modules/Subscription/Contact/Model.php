<?
class CP_Admin_Modules_Subscription_Contact_Model extends CP_Common_Modules_Subscription_Contact_Model
{
    /**
     *
     */
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        
        $extraFieldNames = '';
        $extraTableNames = '';


        
        $SQL = "
		SELECT c.*
              ,gc.name AS country_name                        
		FROM contact c
		LEFT JOIN geo_country gc ON (c.address_country_code = gc.country_code)
		";

        return $SQL;
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the First Name');
        $validate->validateData('last_name', 'Please enter the Last Name');
       
        

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
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        $site_id = $fn->getSessionParam('cp_site_id');
        
        if (!$this->getNewValidate()){
            return $validate->getErrorMessageXML();
        }

        $fa = $this->getFields();

        if($cpCfg['m.subscription.contact.hasRegisterNo']){
            $rowPrefix    = $fn->getRecordByCondition('setting', "key_text = 'registrationCodePrefix'");
            $current_year = date('Y');
            
            $SQLUpdate = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextRegistrationNo'";
            $resultUpdate = $db->sql_query($SQLUpdate);
            $nextRegNo = $fn->getSettingsValueByKey("nextRegistrationNo");
            
            $fa['registration_no'] = $nextRegNo;
            if ($site_id == 2) {
                $fa['registration_no'] = $nextRegNo . 'J';
            }
        }

        $fa['status'] = 'Active';
        $id = $fn->addRecord($fa);
        $fn->returnAfterNewSave($id);
    }

    /**
     *
     */
    function getEditValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('first_name', 'Please enter the first name');
        //$validate->validateData('gender' , 'Please select gender');
        //$validate->validateData('date_of_birth' , 'Please enter date of birth');

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

        $contact_id = $fn->getReqparam('contact_id');
        
        if (!$this->getEditValidate($contact_id)){
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

        $fa = $fn->addToFieldsArray($fa, 'contact_id');
        $fa = $fn->addToFieldsArray($fa, 'first_name');
        $fa = $fn->addToFieldsArray($fa, 'last_name');
        $fa = $fn->addToFieldsArray($fa, 'email');
        $fa = $fn->addToFieldsArray($fa, 'gender');
        $fa = $fn->addToFieldsArray($fa, 'year_of_joining');
        $fa = $fn->addToFieldsArray($fa, 'status');
        $fa = $fn->addToFieldsArray($fa, 'phone');
        $fa = $fn->addToFieldsArray($fa, 'fax');
        $fa = $fn->addToFieldsArray($fa, 'mobile');
        $fa = $fn->addToFieldsArray($fa, 'address_area');
        $fa = $fn->addToFieldsArray($fa, 'address_city');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'address_po_code');
        $fa = $fn->addToFieldsArray($fa, 'subscribe');
        $fa = $fn->addToFieldsArray($fa, 'published');
        $fa = $fn->addToFieldsArray($fa, 'address_country_code');
        $fa = $fn->addToFieldsArray($fa, 'address_state');
        $fa = $fn->addToFieldsArray($fa, 'company_id');

        return $fa;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar = Zend_Registry::get('searchVar');
        $searchVar->mainTableAlias = 'c';

		$contact_id = $fn->getReqParam ('contact_id');
		
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


            $searchVar->sortOrder = "";
        }
    }


    /**
     *
     */
    function getChangeStatusFormSubmit() {
        $validate = Zend_Registry::get('validate');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');
        
        if (!$this->getChangeStatusFormValidate()){
            return $validate->getErrorMessageXML();
        }
        
        $with_drawal= $fn->getPostParam('with_drawal');
        $refund_payable = $fn->getPostParam('refund_payable');
        $refund_payable_bank_ac = $fn->getPostParam('refund_payable_bank_ac');
        $contact_id = $fn->getPostParam('contact_id');
        
        $sqlUpdate = "
        UPDATE contact 
        SET with_drawal = '{$with_drawal}'
           ,refund_payable = '{$refund_payable}'
           ,refund_payable_bank_ac = '{$refund_payable_bank_ac}'
           ,status = 'Withdraw'
        WHERE contact_id = {$contact_id}
        ";
        $resultUpdate = $db->sql_query($sqlUpdate);
        
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    function getChangeStatusFormValidate() {
        $validate = Zend_Registry::get('validate');
        
        $validate->resetErrorArray();
        /*$validate->validateData('with_drawal', 'Please enter the reasons for withdrawal');
        $validate->validateData('refund_payable', 'Please enter the refund payable to');
        $validate->validateData('refund_payable_bank_ac', 'Please enter bank account');*/

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    function getPrintWithdrawalForm() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Courier','',11);

        $contact_id = $fn->getReqParam('contact_id');

        $SQL = "
        SELECT c.*
				,gc.name AS country_name
        		,cor.title AS course_title
        		,b.title AS session_title
        		,p.first_name AS parent_name
        		,p.address_flat
        		,p.address_street
        		,p.address_po_code
        		,p.address_country
        FROM contact c
        LEFT JOIN (parent_contact pc) ON (c.contact_id = pc.contact_id)
        LEFT JOIN (parent p) ON (pc.parent_id = p.parent_id)
        LEFT JOIN (course_contact cc) ON (cc.contact_id = c.contact_id)
        LEFT JOIN (course cor) ON (cc.course_id = cor.course_id)
        LEFT JOIN (batch b) ON (cor.course_id = b.course_id)
        LEFT JOIN geo_country gc ON (p.address_country = gc.country_code)
        WHERE c.contact_id = {$contact_id}
        ";

        $result = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
			$pdf->Output();
			return;
		}

        $count = 0;
        $today = date("Y-m-d");
        
        //============================================================================= //
        $pdf->SetFont('Courier','',11);
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                /* Logo of the institution */
                //$pdf->Image('images/icon-dashboard.png',10,5,45);

                /* Header */
                $pdf->SetFont('Courier','B',13);
                $pdf->SetXY(100, 0);
                $pdf->Cell(21, 20, "As-Siddiq Centre for Islamic Studies Pte Ltd", 0, 0, 'C');                
                $pdf->Ln(20);

                $pdf->SetFont('Courier','B',12);
                $pdf->SetXY(100, 8);
                $pdf->Cell(21, 20, "Student Withdrawal Form - Weekend Islamic School", 0, 0, 'C');                
                $pdf->Ln(20);

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(0,0,0);
				$pdf->SetTextColor(255,255,255);
                $pdf->Cell(190,8,"Student Information(1)",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Student Name",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['first_name'],1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(55,8,"BC / NRIC No.",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['id_card_no'],1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(55,8,"Current Level",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['course_title'],1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(55,8,"Current Session",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['session_title'],1,0, 'L', 1);
                $pdf->Ln();

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(0,0,0);
				$pdf->SetTextColor(255,255,255);
                $pdf->Cell(190,8,"Parent / Legal Guardian Information",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Name",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['parent_name'],1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(55,8,"Address",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(135,8,$row['address_flat'] . $row['address_street'],1,0, 'L', 1);
                $pdf->Ln();
                $pdf->Cell(120,8,$row['country_name'],1,0, 'L', 1);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(35,8,"Postal Code",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(35,8,$row['address_po_code'],1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(55,25,"Reason For Withdrawal",'TLR',0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->SetFont('Courier','B',9);
                $pdf->Cell(135,25,$row['with_drawal'],'TLR',0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->SetFont('Courier','B',10);
                $pdf->Cell(80,8,"If there is any refund of fees, to",'TLR',0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(110,8,$row['refund_payable'],'TLR',0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(80,8,"whom should the cheque be payable to?",'BLR',0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->Cell(110,8,"",'BLR',0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(255,255,255);
                $pdf->Cell(80,8,"GIRO Status",1,0, 'L', 1);
                $pdf->SetFillColor(221,221,221);
                $pdf->SetFont('Courier','B',9);
                $pdf->Cell(110,8,"Yes/No(If 'Yes', Please fill the GIRO Termination form)",1,0, 'L', 1);
                $pdf->Ln();

                $pdf->SetFont('Courier','B',11);
                $pdf->SetFillColor(0,0,0);
				$pdf->SetTextColor(255,255,255);
                $pdf->Cell(190,8,"For Office Use Only",1,0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFillColor(255,255,255);
				$pdf->SetTextColor(0,0,0);
                $pdf->Cell(55,8,"Refund Amount(if any):",'TL',0, 'L', 1);
                $pdf->SetFont('Courier','U',11);
                $pdf->Cell(135,8,"                   ",'TR',0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(55,8,"Cheque No.:",'L',0, 'L', 1);
                $pdf->SetFont('Courier','U',11);
                $pdf->Cell(55,8,"                   ",0,0, 'L', 1);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(15,8,"Date:",0,0, 'L', 1);
                $pdf->SetFont('Courier','U',11);
                $pdf->Cell(65,8,"                   ",'R',0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(26,8,"Remarks:",'L',0, 'L', 1);
                $pdf->SetFont('Courier','U',11);
                $pdf->Cell(164,8,"                                                                     ",'R',0, 'L', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50,8,"Name of Form Teacher:",'BL',0, 'L', 1);
                $pdf->SetFont('Courier','U',11);
                $pdf->Cell(50,8,"                ",'B',0, 'L', 1);
                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(50,8,"Teacher's Signature:",'B',0, 'L', 1);
                $pdf->SetFont('Courier','U',11);
                $pdf->Cell(40,8,"                ",'BR',0, 'L', 1);
                $pdf->Ln();
                $pdf->Ln(15);

                $pdf->SetFont('Courier','B',11);
                $pdf->Cell(30,8,"Date",'T',0, 'L', 1);
                $pdf->Cell(70,8,"  ",0,0, 'L', 1);
                $pdf->Cell(90,8,"Parent / Legal Guardian Information",'T',0, 'L', 1);
                $pdf->Ln();
                $pdf->Ln(15);

                $pdf->SetFont('Courier','B',12);
                $pdf->Cell(190,8,"As-Siddiq Centre for Islamic Studies Pte Ltd",'T',0, 'R', 1);
                $pdf->Ln();
                $pdf->SetFont('Courier','B',10);
                $pdf->Cell(190,8,"152 Still Road singapore 423991 - Tel: 65474407 - Fax: 63486023 - Email: info@simplyislam.sg",0,0, 'R', 1);
            }
            
        } 
       
        /* Creation of media record of the invoice */
        $file_name = 'WITHDRAWAL_SUBMIT_' . date('Y-m-d') .'.pdf';
        $outputPath = realpath($cpCfg['cp.mediaFolder']) . '/temp';

        $outputFileName = $outputPath . '/' . $file_name;
        //$pdf->Output($outputFileName , "F");
		$pdf->Output();
    }

    /**
     *
     */
    function getAddsubscriptionFormSubmit() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $validate = Zend_Registry::get('validate');
        
        $contact_id = $fn->getPostParam('contact_id');
        $from_year 	= $fn->getPostParam('from_year');
        $amount  	= $fn->getPostParam('amount');
        $to_year 	= $fn->getPostParam('to_year');
        $order_status 	= $fn->getPostParam('order_status');

        /*if (!$this->getAddsubscriptionFormValidate()){
            return $validate->getErrorMessageXML();
        }*/
        
        $contactRec    = $fn->getRecordRowByID('contact', 'contact_id', $contact_id);
		

        $faOrder = array();
       
        $faOrder['contact_id']      			= $contact_id;
        $faOrder['cust_first_name']      		= $contactRec['first_name'];
        $faOrder['cust_last_name']      		= $contactRec['last_name'];
        $faOrder['cust_email']      			= $contactRec['email'];
        $faOrder['cust_phone']      			= $contactRec['phone'];
        $faOrder['cust_fax']      				= $contactRec['fax'];
        $faOrder['cust_mobile']      			= $contactRec['mobile'];
        $faOrder['cust_address']      			= $contactRec['address_area'];
        $faOrder['cust_address_city']      		= $contactRec['address_city'];
        $faOrder['cust_address_state']      	= $contactRec['address_state'];
        $faOrder['cust_address_po_code']    	= $contactRec['address_po_code'];
        $faOrder['cust_address_country_code']   = $contactRec['address_country_code'];
        $faOrder['module']     	    			= 'course_subscription';
        $faOrder['amount']          			= $amount;
        $faOrder['from_year']       			= $from_year;
        $faOrder['to_year']         			= $to_year;
        $faOrder['order_status']         		= 'Due';
        $faOrder['order_date'] 					= date("Y-m-d");

        $order_id = $fn->addRecord($faOrder, 'order');

        $faInvoice = array();

        $faInvoice['contact_id']      			= $contact_id;
        $faInvoice['order_id']      			= $order_id;
        $faInvoice['contact_name']      		= $contactRec['first_name']. ' ' .$contactRec['last_name'];
        $faInvoice['from_year']       			= $from_year;
        $faInvoice['to_year']         			= $to_year;
        $faInvoice['invoice_date']         		= date('Y-m-d');
        $faInvoice['status']         			= $faOrder['order_status'];
        $faInvoice['invoice_code']         		= $this->getUpdateInvoiceCode();

        $fn->addRecord($faInvoice, 'invoice');

        $faCourseContact = array();

        $faCourseContact['contact_id']      	= $contact_id;
        $faCourseContact['from_year']       	= $from_year;
        $faCourseContact['to_year']         	= $to_year;
        $faCourseContact['order_id']      		= $order_id;


        $fn->addRecord($faCourseContact, 'course_contact');


        
        //$id     = $db->sql_nextid();
            
        return $validate->getSuccessMessageXML();
    }

    /**
     *
     */
    /*function getAddsubscriptionFormValidate() {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $from_year  = $fn->getPostParam('from_year');
        $to_year 	= $fn->getPostParam('to_year');

        $validate->resetErrorArray();
        $validate->validateData('from_year', 'Please select From year');
        //$validate->validateData('to_year', 'Please select To Year');
        
        if ($from_year) {
            $sql = "
            SELECT c.from_year
            FROM contact c
            WHERE c.from_year = '{$from_year}'
            ";
//              OR c.to_year = '{$to_year}'

            $result  = $db->sql_query($sql);
            $numRows = $db->sql_numrows($result);
		    if ($numRows > 0) {
                $validate->errorArray['from_year']['name'] = "from_year";
                //$validate->errorArray['to_year']['name'] = "to_year";
                $validate->errorArray['from_year']['msg']  = "Entered value already exists";
		    }
        }

        if (count($validate->errorArray) == 0) {
            return true;
        } else {
            return false;
        }
    }*/


    /**
		Using this update invoice code function is generated the invoice automatically.
     *
     */
    function getUpdateInvoiceCode() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        /* Updation of Quote Code */
        $nextInvoiceCode = $fn->getSettingsValueByKey("nextInvoiceCode");

        $current_year = date('y');
        $current_month = date('m');

        if($nextInvoiceCode < 10){
            $invoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . $nextInvoiceCode;
        }
        else if($nextInvoiceCode < 99){
            $invoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix'). $nextInvoiceCode;
        }
        else if($nextInvoiceCode > 99){
            $invoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix') . $nextInvoiceCode;
        }
        else{
            $invoiceCode = $fn->getSettingsValueByKey('invoiceCodePrefix')  . $nextInvoiceCode;
        }

        $SQL    = "UPDATE setting SET value = (value+1) WHERE key_text = 'nextInvoiceCode'";
        $result = $db->sql_query($SQL);

        return $invoiceCode;
    }


}
