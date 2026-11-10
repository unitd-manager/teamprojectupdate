<?
class CP_Admin_Modules_Project_Quote_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getAdd() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $valArr   = $this->getNewValidate();
        $hasError = $valArr[0];
        $xmlText  = $valArr[1];

        if ($hasError) {
            return $xmlText;
        }

        $recId   = $fn->getPostParam('recId');
        $recType = $fn->getPostParam('recType');

        $fa = array();


        if ($recType == 'proj') {
            $fa['project_id']  = $recId;
            $projRec           = $fn->getRecordRowByID('project', 'project_id', $recId);
            $fa['opportunity_id'] = $projRec['opportunity_id'];
        } else {
            $fa['opportunity_id'] = $recId;
            $oppRec           = $fn->getRecordRowByID('opportunity', 'opportunity_id', $recId);
            $fa['project_id'] = $oppRec['project_id'];
        }

        $quote_sequence          = $this->getNextQuoteSeq($fa['opportunity_id'], $fa['project_id']);

        $fa['quote_sequence']    = $quote_sequence;
        $fa['quote_code']        = $fn->getPostParam('quote_code');
        $fa['quote_date']        = $fn->getPostParam('quote_date');
        $fa['quote_type']        = $fn->getPostParam('quote_type');
        $fa['currency_item']     = $fn->getPostParam('currency_item');
        $fa['status']            = 'Draft';
        $fa['note']              = $fn->getPostParam('note');
        $fa['condition']         = $fn->getPostParam('condition');
        $fa['sign_staff_id']     = $fn->getPostParam('sign_staff_id');
        $fa['creation_date']     = date("Y-m-d H:i:s");

        $SQL                     = $dbUtil->getInsertSQLStringFromArray($fa, 'quote');
        $result                  = $db->sql_query($SQL);
        $quote_id                = $db->sql_nextid();

        $this->getUpdateQuoteCode($quote_id, $recType, $recId, $quote_sequence);

        return $xmlText;
    }

    /**
     *
     */
    function getNewValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('quote_date', 'Please enter the quote date');
        $validate->validateData('quote_type', 'Please enter the quote type');

        if (count($validate->errorArray) == 0) {
            return array(0, $validate->getSuccessMessageXML());
        } else {
            return array(1, $validate->getErrorMessageXML());
        }
    }

    /**
     *
     */
    function getAddFromTemplate() {
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');

        $valArr   = $this->getNewFromTemplateValidate();
        $hasError = $valArr[0];
        $xmlText  = $valArr[1];

        if ($hasError) {
            return $xmlText;
        }

        $recId       = $fn->getPostParam('recId');
        $recType     = $fn->getPostParam('recType');
        $template_id = $fn->getPostParam('template_id');
        $raiseFromTemplate = $fn->getPostParam('raiseFromTemplate');

        $this->getDuplicate($recId, $recType, $template_id, $raiseFromTemplate);

        return $xmlText;
    }

    /**
     *
     */
    function getNewFromTemplateValidate() {
        $validate = Zend_Registry::get('validate');

        $validate->resetErrorArray();

        $validate->validateData('template_id', 'Please choose the template');

        if (count($validate->errorArray) == 0) {
            return array(0, $validate->getSuccessMessageXML());
        } else {
            return array(1, $validate->getErrorMessageXML());
        }
    }

    /**
     *
     */
    function getDelete() {
        $db = Zend_Registry::get('db');

        $quote_id   = isset($_REQUEST['quote_id']) ? $_REQUEST['quote_id'] : '';

        if ($quote_id > 0) {
            $SQL    = "
            DELETE 
            FROM quote 
            WHERE quote_id = {$quote_id}
            ";
            $result = $db->sql_query($SQL);

            $SQL    = "
            DELETE 
            FROM quote_items
            WHERE quote_category_id IN
               (SELECT quote_category_id
                FROM quote_category
                WHERE quote_id = {$quote_id}
               )
            ";
            $result = $db->sql_query($SQL);

            $SQL    = "
            DELETE 
            FROM quote_category 
            WHERE quote_id = {$quote_id}
            ";
            $result = $db->sql_query($SQL);
        }
    }

    /**
     *
     */
    function getSave($tableName = '', $keyField = '', $keyFieldVal = '') {
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $validate = Zend_Registry::get('validate');

        $valArr   = $this->getNewValidate();
        $hasError = $valArr[0];
        $xmlText  = $valArr[1];

        if ($hasError) {
            return $xmlText;
        }

        $quote_id       = $fn->getPostParam('quote_id');
        $quote_date     = $fn->getPostParam('quote_date');
        $quote_type     = $fn->getPostParam('quote_type');
        $currency_item  = $fn->getPostParam('currency_item');
        $status         = $fn->getPostParam('status');
        $note           = $fn->getPostParam('note');
        $condition      = $fn->getPostParam('condition');

        $quoteRec       = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);

        if (trim($quote_id) == '') {
            return;
        }

        $fa = array();
        $fa['quote_id']          =  $quote_id;
        $fa['quote_date']        =  $quote_date;
        $fa['quote_type']        =  $quote_type;
        $fa['currency_item']     =  $currency_item;
        $fa['status']            =  $status;
        $fa['note']              =  $note;
        $fa['condition']         =  $condition;
        $fa['sign_staff_id']     =  $fn->getPostParam('sign_staff_id');
        $fa['modification_date'] =  date("Y-m-d H:i:s");

        $whereCondition = "
        WHERE quote_id = {$quote_id}
        ";
        $SQL            = $dbUtil->getUpdateSQLStringFromArray($fa, 'quote', $whereCondition);
        $result         = $db->sql_query($SQL);

        $json = array();

        $opp_id  = $quoteRec['opportunity_id'];
        $proj_id = $quoteRec['project_id'];

        if ($status == 'Agreed') {
            $SQL = "
            UPDATE quote
            SET status = 'Cancelled'
            WHERE (opportunity_id = '{$opp_id}' || project_id = '{$proj_id}')
              AND quote_id != {$quote_id}
			";
            $db->sql_query($SQL);
        }

        $fnMod = includeCPClass('ModuleFns', 'project_quote');
        $fnMod->refreshValuesBasedOnConfirmedQuote($quote_id);

        if ($quoteRec['status'] != $fa['status']) {
            $json = array('refreshPage' => 1);
        }

        $xmlText = $validate->getSuccessMessageXML('', $json);

        return $xmlText;
    }

    /**
     *
     */
    function getNextQuoteSeq($opportunity_id, $project_id) {
        $db = Zend_Registry::get('db');

        $SQL = "
        SELECT MAX(quote_sequence) 
        FROM quote 
        WHERE (opportunity_id = '{$opportunity_id}' OR project_id = '{$project_id}')
        ";
        $result = $db->sql_query($SQL);
        $row    = $db->sql_fetchrow($result);

        return $row[0]+1;
    }

    /**
     *
     */
    function getUpdateQuoteCode($quote_id, $recType, $recId, $sequence) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        if ($quote_id != '') {

            if ($recType == 'proj') {
                $SQL    = "
                SELECT project_code
                FROM project
                WHERE project_id = {$recId}
                ";
                $result = $db->sql_query($SQL);
                $row    = $db->sql_fetchrow($result);

                $quote_prefix = $fn->getSettingsValueByKey('quoteCodePrefix');
                $SQL    = "
                UPDATE quote
                SET quote_code = CONCAT_WS(
                						  ''
                                          ,'{$quote_prefix}'
                                          ,SUBSTRING('{$row['project_code']}' 
                                          FROM {$cpCfg['m.project.quote.codeStartIndexFromPro']})
                                          ,'-'
                                          ,'{$sequence}'
                                 )
                WHERE  quote_id = {$quote_id}
                ";
                $result = $db->sql_query($SQL);
            } else {
                $SQL = "
                SELECT opportunity_code
                FROM opportunity
                WHERE opportunity_id = {$recId}
                ";
                $result = $db->sql_query($SQL);
                $row    = $db->sql_fetchrow($result);

                $quote_prefix = $fn->getSettingsValueByKey('quoteCodePrefix');
                $SQL = "
                UPDATE quote
                SET quote_code = CONCAT_WS(''
                                          ,'{$quote_prefix}'
                                          ,SUBSTRING('{$row['opportunity_code']}' 
                                          FROM {$cpCfg['m.project.quote.CodeStartIndex']})
                                          ,'-'
                                          ,'{$sequence}'
                                 )
                WHERE quote_id = {$quote_id}
                ";
                $result = $db->sql_query($SQL);
            }
        }
    }

    /**
     *
     */
    function getDuplicate($recId = '', $recType = '', $quote_id = '', $raiseFromTemplate = 0){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        if ($recId == '') {
            $recId    = $fn->getGetParam('recId');
            $recType  = $fn->getGetParam('recType');
            $quote_id = $fn->getReqParam('quote_id');
        }

        $text = '';

        /*1) create a new quote and copy the values from quote id passed
        create all the categories for quote_id = 1 (passed) in the table quote_category
        create all the line items for quote_id = 1 (passed) in the table quote_items*/

        $row = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);

        $fa = array();

        if ($recType == 'proj') {
            $fa['project_id']  = $recId;
            $projRec           = $fn->getRecordRowByID('project', 'project_id', $recId);
            $fa['opportunity_id'] = $projRec['opportunity_id'];

        } else {
            $fa['opportunity_id'] = $recId;
            $oppRec = $fn->getRecordRowByID('opportunity', 'opportunity_id', $recId);
            $fa['project_id'] = $oppRec['project_id'];
        }

        if ($raiseFromTemplate == 1){
            if ($recType == 'proj') {
                $quote_sequence = $this->getNextQuoteSeq($projRec['opportunity_id'], $projRec['project_id']);
            } else {
                $quote_sequence = $this->getNextQuoteSeq($oppRec['opportunity_id'], $oppRec['project_id']);
            }
        } else {
            $quote_sequence = $this->getNextQuoteSeq($row['opportunity_id'], $row['project_id']);
        }

        $fa['quote_sequence']    = $quote_sequence;
        $fa['quote_date']        = date("Y-m-d H:i:s");
        $fa['quote_type']        = $row['quote_type'];
        $fa['currency_item']     = $row['currency_item'];
        $fa['status']            = 'Draft';
        $fa['note']              = $row['note'];
        $fa['condition']         = $row['condition'];
        $fa['sign_staff_id']     = $row['sign_staff_id'];
        $fa['creation_date']     = date("Y-m-d H:i:s");

        $SQLUpdate               = $dbUtil->getInsertSQLStringFromArray($fa, 'quote');
        $resultUpdate            = $db->sql_query($SQLUpdate);
        $new_quote_id            = $db->sql_nextid();

        $this->getUpdateQuoteCode($new_quote_id, $recType, $recId, $quote_sequence);

        $SQL = "
        SELECT a.*
        FROM quote_category a
        WHERE a.quote_id = {$quote_id}
        ";
        $result  = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {

            $fa = array();
            $fa['quote_id']         = $new_quote_id;
            $fa['valuelist_id']     = $row['valuelist_id'];
            $fa['category_type']    = $row['category_type'];
            $fa['sort_order']       = $row['sort_order'];
            $fa['creation_date']    = date("Y-m-d H:i:s");

            $SQLUpdate              = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_category');
            $resultUpdate           = $db->sql_query($SQLUpdate);
            $quote_category_id      = $db->sql_nextid();

            $SQLQuoteItem = "
            SELECT a.*
            FROM quote_items a
            WHERE a.quote_category_id = {$row['quote_category_id']}
            ";
            $resultQuoteItem  = $db->sql_query($SQLQuoteItem);

            while ($rowQuoteItem = $db->sql_fetchrow($resultQuoteItem)) {

                $fa = array();
                $fa['quote_category_id']    = $quote_category_id;
                $fa['quote_id']             = $new_quote_id;
                $fa['title']                = $rowQuoteItem['title'];
                $fa['item_type']            = $rowQuoteItem['item_type'];
                $fa['amount']               = $rowQuoteItem['amount'];
                $fa['amount_other']         = $rowQuoteItem['amount_other'];
                $fa['sort_order']           = $rowQuoteItem['sort_order'];
                $fa['creation_date']        = date("Y-m-d H:i:s");

                $SQLUpdate                  = $dbUtil->getInsertSQLStringFromArray($fa, 'quote_items');
                $resultUpdate               = $db->sql_query($SQLUpdate);
                $quote_items_id             = $db->sql_nextid();
            }
        }

        /** cancel the original quote **/
        $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        if ($quoteRec['status'] == 'Agreed') {
            $SQL = "
            UPDATE quote
            SET status = 'Cancelled'
            WHERE quote_id = {$quote_id}
            ";
            $db->sql_query($SQL);

            $fnMod = includeCPClass('ModuleFns', 'project_quote');
            $fnMod->refreshValuesBasedOnConfirmedQuote($quote_id);
        }

        return $text;
    }

    /**
     *
     */
    function getConfirmedQuoteValue() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        
        $opportunity_id   = $fn->getReqParam('recId');

        if ($opportunity_id == '') {
            return;
        }

        $total_value = 0;

        $SQL = "
        SELECT quote_id
        FROM quote
        WHERE opportunity_id = {$opportunity_id}
          AND status = 'Agreed'
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        if ($numRows > 0) {
            $row = $db->sql_fetchrow($result);
            $quote_id = $row['quote_id'];

            $SQL = "
            SELECT FORMAT(SUM(amount), 0) AS total
            FROM quote_items
            WHERE quote_id = {$quote_id}
            ";
            $result  = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);
            $total_value = $row['total'];
        }

        header('Content-type: application/json');
        $json = '';
        $arr = array('total_value' => $total_value);
        $json = json_encode($arr);

        return $json;
    }
     /**
     *
     */

    function getQuotePrintToFpdf() {
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
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/mc_table.php');

        $pdf = new FPDF();
        $pdf->SetFont('Arial','B',14);
        //$pdf->Cell(width, height,'text', border, lnbreak, align, fill);
        //$header = array('Term', 'Subject', 'Grade', 'Marks');


		$pdf=new PDF_MC_Table();
		$pdf->AddPage();
		$pdf->SetFont('Arial','',14);
		//Table with 20 rows and 4 columns
		$pdf->SetWidths(array(30,50,30,40));
		$pdf->Row(array('Ahmad','Syed','bs','muhyideen'));
		$pdf->Output();
		return;

        $record_id     = $fn->getReqParam('record_id');
		$invoice_terms = '';
		$notes  = '';
		$SQL1 = "
		SELECT a.*
			  ,DATE_FORMAT(a.quote_date, '%d %b %Y') AS quote_date
			  ,b.quote_category_id
			  ,b.valuelist_id
			  ,b.category_type;
			  ,c.quote_items_id
			  ,c.title AS item_title
			  ,c.amount
			  ,c.description AS item_description
			  ,c.amount_other
			  ,(
				  SELECT SUM(qi.amount)
				  FROM quote_items qi
				  WHERE qi.quote_category_id = c.quote_category_id
			   ) AS amount_sum
			  ,(
				  SELECT SUM(qi.amount_other)
				  FROM quote_items qi
				  WHERE qi.quote_category_id = c.quote_category_id
			   ) AS amount_other_sum
			  ,c.item_type
			  ,d.value AS quote_cat_title
			  ,(
				  SELECT SUM(qi.amount)
				  FROM quote_items qi
				  WHERE a.quote_id = qi.quote_id
			   ) AS total
			  ,(
				  SELECT SUM(qi.amount_other)
				  FROM quote_items qi
				  WHERE a.quote_id = qi.quote_id
			   ) AS total_other
			  ,(IF(ISNULL(a.project_id), $P!{opp_contact_sql}, $P!{proj_contact_sql})) AS contact_id
			  ,(IF(ISNULL(a.project_id), $P!{opp_currency_sql}, $P!{proj_currency_sql})) AS currency
			  ,(IF(ISNULL(a.project_id), $P!{opp_company_name_sql}, $P!{proj_company_name_sql})) AS client_company_name
			  ,b.sort_order AS quote_category_sort
			  ,c.sort_order AS quote_items_sort

		FROM quote a
		LEFT JOIN quote_category b ON (a.quote_id          = b.quote_id)
		LEFT JOIN quote_items c    ON (b.quote_category_id = c.quote_category_id)
		LEFT JOIN (valuelist d)    ON (b.valuelist_id      = d.valuelist_id)
		WHERE a.quote_id = $P!{record_id}
		ORDER BY a.quote_id
				,quote_category_sort
				,b.quote_category_id
				,quote_items_sort		";

		$SQL = "
		SELECT DATE_FORMAT(i.invoice_date, '%d %b %Y') AS invoice_date
			  ,i.notes
			  ,i.invoice_code
			  ,i.invoice_amount
			  ,i.invoice_terms
			  ,i.invoice_type
			  ,i.inv_currency
			  ,p.project_id
			  ,p.contact_id
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
			  ,cont.position as position
			  ,cont.salutation
			  ,cont.company_address_flat
			  ,cont.company_address_street
			  ,cont.company_address_town
			  ,cont.company_address_state
			  ,cont.company_address_country
			  ,c.company_name
			  ,c.phone
              ,c.address_flat    AS comp_mul_address_flat
              ,c.address_street  AS comp_mul_address_street
              ,c.address_state   AS comp_mul_address_state
              ,c.address_country AS comp_mul_address_country
              ,c.address_po_code AS comp_mul_address_po
			  ,qc.quote_category_id
			  ,qc.valuelist_id
			  ,qc.category_type
			  ,qi.quote_items_id
			  ,qi.title AS item_title
			  ,qi.amount
			  ,qi.amount_other
			  ,(
				  SELECT SUM(qi.amount)
				  FROM quote_items qi
				  WHERE qi.quote_category_id = qc.quote_category_id
			   ) AS amount_sum
			  ,(
				  SELECT SUM(qi.amount_other)
				  FROM quote_items qi
				  WHERE qi.quote_category_id = qc.quote_category_id
			   ) AS amount_other_sum
			  ,qi.item_type
			  ,v.value AS quote_cat_title
			  ,(
				  SELECT SUM(qi.amount)
				  FROM quote_items qi
				  WHERE p.confirmed_quote_id = qi.quote_id
			   ) AS total
			  ,(
				  SELECT SUM(qi.amount_other)
				  FROM quote_items qi
				  WHERE p.confirmed_quote_id = qi.quote_id
			   ) AS total_other
			  ,qc.sort_order AS quote_category_sort
			  ,qi.sort_order AS quote_items_sort

		FROM invoice i
		JOIN project p ON (i.project_id = p.project_id)
        LEFT JOIN contact cont ON (p.contact_id = cont.contact_id)
        LEFT JOIN company c    ON (p.company_id = c.company_id)
        LEFT JOIN company_address ca ON (cont.company_id = ca.company_id)
		JOIN quote_category qc ON (p.confirmed_quote_id = qc.quote_id)
		JOIN quote_items qi    ON (qc.quote_category_id = qi.quote_category_id)
		LEFT JOIN valuelist v  ON (qc.valuelist_id      = v.valuelist_id)
		WHERE i.invoice_id = $record_id
		ORDER BY quote_category_sort
				,qc.quote_category_id
				,quote_items_sort
		";
		
        $result = $db->sql_query($SQL);

        $numRows  = $db->sql_numrows($result);
		if ($numRows == 0){
            $pdf->SetXY(60,30);
            $pdf->Cell(50, 20, "Please set the values for your invoice and print the PDF");
			$pdf->Output();
			return;
		}
        $count = 0;
        $total = 0;
        //============================================================================= //
        while ($row = $db->sql_fetchrow($result)) {
            if ($count == 0){
                // Framed 
                $date = $fn->getCPDate($row['invoice_date'], 'd m Y');
                $code = 'Invoice # : '. $row['invoice_code'];
                $company_name = strtoupper ($cpCfg['cp.companyName']);
                
                // Company Name
                $pdf->SetXY(68,5);
                $pdf->Cell(40, 20, $company_name);
                $pdf->Ln(10);
                
                // Invoice Date & Code
                $pdf->SetFont('Arial','B',9);
                $pdf->SetXY(165, 25);
                $pdf->Cell(50, 20, "$code" );                
                $pdf->Ln(5);
                $pdf->SetX(165);
                $pdf->Cell(50, 20, "$date");

                $pdf->SetFont('Arial','',10);

                // To Address
                $pdf->SetXY(10, 30);
				$pdf->SetFillColor(131,181,231);
                $pdf->Rect(10 , 35, 85, 40, 'DF');
                $pdf->SetFont('Arial','B',10);
                $pdf->SetX(10);
                $pdf->Cell(20, 20, "Invoice To :");
                $pdf->Ln(5);
                $pdf->Cell(50, 20, $row['company_name']);
                $pdf->Ln(5);
                $pdf->SetFont('Arial','',10);
                $pdf->Cell(15,20,"Attn : " . $row['salutation']);
                $pdf->Cell(40,20,$row['contact_name']);
                $pdf->Ln(5);

				if ($row['comp_mul_address_flat']){
                    $pdf->Cell(50, 20, $row['comp_mul_address_flat']);
                    $pdf->Ln(5);
                }
                if ($row['comp_mul_address_street']){
                    $pdf->Cell(50, 20, $row['comp_mul_address_street']);
                    $pdf->Ln(5);
                }
                if ($row['comp_mul_address_state']){
                    $pdf->Cell(50, 20, $row['comp_mul_address_state']);
                    $pdf->Ln(5);
                }
                if ($row['comp_mul_address_country']){
                    $pdf->Cell(50, 20, $row['comp_mul_address_country']);
                    $pdf->Ln(5);
                }
				
                $pdf->Ln(30);

                // Table Heading
                $pdf->SetFont('Arial','B',10);
                $pdf->Ln(10);
                $pdf->SetX(10);
                $pdf->SetFillColor(142,182,212);
                $pdf->Cell(155,8,"Description",1 ,0, 'L', 1);
                $pdf->Cell(30,8,"Amount (SG$)",1 ,0, 'R', 1);
                $pdf->Ln();
            }
             //Table Content
            $pdf->SetFont('Arial','',10);
            $pdf->SetX(10);
            $pdf->Cell(155,10, " " . $row['item_title'] , 1);
			$amount = number_format($row['amount']);
            $pdf->Cell(30,10, " " . $amount, 1,  0, 'R');
            $total = $row['total'];
			$total = number_format($total);
            $pdf->Ln();
            $invoice_terms = $row['invoice_terms'];
            $notes = $row['notes'];
            $count++;
        }
            //Final Values
            $pdf->SetFont('Arial','B',10);
            //$pdf->SetFillColor(219,255,140);
			$pdf->SetFillColor(131,181,231);
            $pdf->Cell(155, 8,'Total',1, 0, 'C', 1);
            $pdf->Cell(30,8,$total,1,  0, 'R', 1);
            $pdf->Ln(20);
            $pdf->SetFont('Arial','',8);
            $pdf->Cell(150, 8, 'Terms :');
            $pdf->Ln(5);
            $pdf->Cell(150, 8, $invoice_terms);
            $pdf->Ln(8);
            $pdf->Cell(155, 8,'Note : ');
            $pdf->Ln(5);
            $pdf->Cell(155, 8, $notes);
			//$pdf->SetY(-15);
			//$pdf->SetFont('Arial','',6);
			//$pdf->Cell(0, 10,'10 Jalan Besar #17-02 ,Sim Lim Tower, Singapore 208787, Tel : +65 6396 7554, Email : razik@usoftsolutions.com, Web: www.usoftsolutions.com', 0 , 0, 'C');
        $pdf->Output();
    }
	
}