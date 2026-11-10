<?
class CP_Admin_Modules_EnterpriseIms_GiroPayment_View extends CP_Common_Lib_ModuleViewAbstract
{
    /**
     *
     */
    function getList($dataArray){
        $fn = Zend_Registry::get('fn');

        //$_SESSION['selectedInvoiceCodes'] = array();
        $selectedInvoiceCodes = isset($_SESSION['selectedInvoiceCodes']) ? $_SESSION[   'selectedInvoiceCodes'] : 0;
        //unset($_SESSION['selectedInvoiceCodesToPrint']);

        /*if ($selectedInvoiceCodes> 0) {
            $count = count($selectedInvoiceCodes);
            print $count . "no of invoice";    
        } else {
            $count = count($selectedInvoiceCodes);
            print $count . "no of invoice";    
        }
        
        $_SESSION['createdInvoice'] = '';
        if ($_SESSION['createdInvoice'] == 1) {
        }*/
        
        $mode_of_payment = $fn->getReqParam('mode_of_payment');
        $year            = $fn->getReqParam('year');
        $month           = $fn->getReqParam('month');
        
        if ($month == '') {
            return "{$this->getSearch()}";
        }

        switch ($month) {
            case 1: $prefix_month = 'Jan';
            break;
            case 2: $prefix_month = 'Feb';
            break;
            case 3: $prefix_month = 'Mar';
            break;
            case 4: $prefix_month = 'Apr';
            break;
            case 5: $prefix_month = 'May';
            break;
            case 6: $prefix_month = 'Jun';
            break;
            case 7: $prefix_month = 'Jul';
            break;
            case 8: $prefix_month = 'Aug';
            break;
            case 9: $prefix_month = 'Sep';
            break;
            case 10: $prefix_month = 'Oct';
            break;
            case 11: $prefix_month = 'Nov';
            break;
            case 12: $prefix_month = 'Dec';
            break;
        }

        $DBSTxtUrl = "index.php?_topRm=finance&module=pms_giroPayment&_spAction=generateDBSTxtFile&month={$month}&year={$year}&showHTML=0";
        
        $rows = '';
        $repeat_name = '';
        foreach ($dataArray as $row){
            $contact_name = $row['first_name'] . ' ' . $row['last_name'];

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$row['invoice_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $row['invoice_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=pms_invoice&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $ddaRow = "";
            if ($mode_of_payment == 'Giro') {
                $ddaRow = "<td class='mb5'>{$row['dda']}</td>";
            }
            
            $reportLink = "index.php?module=pms_order&_spAction=printInvoiceInFpdf&record_id={$row['invoice_id']}&showHTML=0";

            $rows .= "
            <tr>
                <td>{$row['dda']}</td>
                <td class='mb5'>{$row['parent_name']}</td>
                <td class='mb5'><strong>{$contact_name}</strong></td>
                {$ddaRow}
                <td>{$row['branch_name']}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], $month, 0)}</td>
                <!--<td>{$this->getPrintInvoiceCheckBox($row['contact_id'], $row['order_id'], $month, 0)}</td>-->
                <td>
                    <a class='button' target='_blank' href='index.php?_topRm=finance&module=pms_order&_action=edit&order_id={$row['order_id']}'  target=''><u>Goto Finance</u></a>
                    <a class='button' href='{$reportLink}' target='_blank'><u>Print Invoice</u></a>
                </td>
            </tr>
            ";
        }

        $formAction = "index.php?module=pms_giroPayment&_spAction=giroPaymentSubmit&month={$month}&mode_of_payment={$mode_of_payment}&enrollment_year={$year}&showHTML=0";
        $printAlLDueInvoiceLink = "index.php?module=pms_giroPayment&_spAction=printAllDueInvoiceForMonth&year={$year}&month=$month&showHTML=0";
        
        $text = "
        {$this->getSearch()}
        <form id='frmGiro' class='yform columnar giroForm' method='post' action='{$formAction}'>
            <table class='thinlist room-giroPayment-table'>
                <tr>
                    <thead>
                        <!--<div class='actBtns'>
                            <a id='giroSubmit' class='button giroSubmit' href='index.php?module=pms_giroPayment&_spAction=giroPaymentSubmit&showHTML=0'> 
                            <h3>Submit</h3>
                            </a>
                        </div>-->

                        <th></th>
                        <th>
                            <a href='{$DBSTxtUrl}'  id='btnGenerateDBSTxtFile1' class='button' >Export File for DBS</a>
                        </th>
                        <th></th>
                        <!--<th class='txtCenter click-all-top'>{$this->getCheckUncheckBtn()}</th>-->
                        <th></th>
                        <th>
                            <a href='{$printAlLDueInvoiceLink}'  id='printAllDueInvoice' class='button' target='_blank'>Print All Due Students</a>
                        </th>
                        <th>
                            <input id='btnGiroSubmit' class='button' type='submit' value='Update Payment failures'>
                        </th>
                    </thead>
                    
                    <tbody>
                        <tr>
                            <td><strong>DDA</strong></td>
                            <td><strong>Parent Name</strong></td>
                            <td><strong>Student Name</strong></td>
                            <td><strong>Branch Name</strong></td>
                            <td class='txtCenter'><strong>Check / Uncheck</strong></td>
                            <!--<td class='txtCenter'><strong>Print Invoice</strong></td>-->
                            <td></td>
                        </tr>
                        
                        {$rows}
                        
                    </tbody>
                </tr>
            </table>
            
        </form>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getCheckUncheckBtn() {
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = "
        <a href='#' class='check-all'>
        <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_checked.gif'></a>
        <a href='#' class='uncheck-all'><img src='{$cpCfg['cp.commonImagesPathAlias']}icons/checkbox_unchecked.gif'></a>
        ";
        
        return $text;
    }

    /**
     *
     */
    function getPrintInvoiceCheckBox($contact_id, $order_id, $month, $checked_val) {
        $db = Zend_Registry::get('db');

        $invoiceCodes = isset($_SESSION['selectedInvoiceCodesToPrint']) ? $_SESSION['selectedInvoiceCodesToPrint'] : 0;
        
        $SQL = "
        SELECT i.status
              ,i.invoice_code
        FROM invoice i
        WHERE i.contact_id = {$contact_id}
          AND i.invoice_month = {$month}
          AND i.order_id = {$order_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        $text = '';
        $checked = '';
        
        if ($checked_val == 1) {
            $checked = "checked='checked'";
        }
        
        if ($row['status'] == 'Due') {
            $text = "
            <div class='float_left'>
                <input type='checkbox' name='invoiceCodeToPrint[]' value='{$row['invoice_code']}' class='invoiceCodeToPrint' {$checked}'>
            </div>
            ";  
        } else if ($row['status'] == 'Paid') {
            $SQLRec = "
            SELECT r.receipt_code 
            FROM receipt r
            LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
            LEFT JOIN (invoice i) ON (i.invoice_id = irh.invoice_id)
            WHERE i.invoice_code = '{$row['invoice_code']}'
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($SQLRec);
            $rowRec = $db->sql_fetchrow($resultRec);
        
            $text = "
            <div>{$row['status']}</div> 
            ";
            
        }
        
        return $text;
    }

    /**
     *
     */
    function getInvoiceCheckBox($contact_id, $order_id, $month, $checked_val) {
        $db = Zend_Registry::get('db');

        $invoiceCodes = isset($_SESSION['selectedInvoiceCodes']) ? $_SESSION['selectedInvoiceCodes'] : 0;
        
        $SQL = "
        SELECT i.status
              ,i.invoice_code
        FROM invoice i
        WHERE i.contact_id = {$contact_id}
          AND i.invoice_month = {$month}
          AND i.order_id = {$order_id}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);
        
        $text = '';
        $checked = '';
        
        if ($checked_val == 1) {
            $checked = "checked='checked'";
        }
        
        if ($row['status'] == 'Due') {
            $text = "
            <div class='float_left'>
                <input type='checkbox' name='invoiceCode[]' value='{$row['invoice_code']}' class='invoiceCode' {$checked}'>
            </div>
            ";  
        } else if ($row['status'] == 'Paid') {
            $SQLRec = "
            SELECT r.receipt_code 
            FROM receipt r
            LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
            LEFT JOIN (invoice i) ON (i.invoice_id = irh.invoice_id)
            WHERE i.invoice_code = '{$row['invoice_code']}'
              AND r.receipt_status = 'Paid'
            ";
            $resultRec = $db->sql_query($SQLRec);
            $rowRec = $db->sql_fetchrow($resultRec);
        
            $text = "
            <div>{$row['status']} / <a href='#' class='cancelReceipt' receipt_code={$rowRec['receipt_code']}>Cancel Receipt</a>
            </div> 
            ";
            
        }
        
        return $text;
    }

    /**
     *
     */
    function getSearch() {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $site_id         = $fn->getReqParam('site_id');
        $invoice_status  = $fn->getReqParam('invoice_status');
        $year            = $fn->getReqParam('year');
        $month           = $fn->getReqParam('month');
        $userGroupType   = $fn->getSessionParam('userGroupType');
        
        if ($year == '') {
            $year = date('Y');
        }
        
        $sqlYear = "
        SELECT DISTINCT DATE_FORMAT(i.invoice_date, '%Y') AS invoice_year
        FROM invoice i
        ";
        
        if ($month == '') {
            $month = date('m');
        }
        
        $arr = array (
                '01' => 'January'
               ,'02' => 'February'
               ,'03' => 'March'
               ,'04' => 'April'
               ,'05' => 'May'
               ,'06' => 'June'
               ,'07' => 'July'
               ,'08' => 'August'
               ,'09' => 'September'
               ,'10' => 'October'
               ,'11' => 'November'
               ,'12' => 'December'
               );
               
        $branch = "";
        $colspan = 6;
        
        if ($userGroupType == 'Super Administrator') {
            $modObj   = getCPModuleObj('common_site');
            $sqlSites = $modObj->model->getSiteSQL();

            $branch = "
            <td>
                <select name='site_id' class='mr10'>
                    <option value=''>{$ln->gd('m.webBasic.content.lbl.site', 'Branch')}</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlSites, $site_id)}
                </select>
            </td>
            ";
            $colspan = 7;
        }
        
        $rows = "
        {$branch}

        <td class='fieldValue'>
            <select name='invoice_status' class='mr10'>
                <option value=''>Status</option>
                {$cpUtil->getDropDown1($cpCfg['m.ecommerce.order.statusArr'], $invoice_status)}
            </select>
        </td>

        <td>
            <select name='year' class='yearFilter'>
                <option value=''>Select Year</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlYear, $year)}
            </select>
        </td>

        <td>
            <select name='month' class='ml10 mr10'>
                {$cpUtil->getDropDownFromArr($arr, $month)}
            </select>
        </td>

        <td>
            Search by DDA or Parent / Student Name: <input class='w200' name='keyword' value='' />
        </td>
        ";
        
        $action = "index.php?_topRm=finance&module=enterpriseIms_giroPayment&_action=list";
        $failedStudentsLink = "index.php?module=enterpriseIms_giroPayment&_spAction=printGiroFailures&month={$month}";
        $reportLink = "index.php?module=enterpriseIms_order&_spAction=printSelectedInvoices&month_val={$month}&showHTML=0";
        
        $text = "
        <form id='giroSearch' class='yform columnar' method='post' action='{$action}'>
        <table class='search'>
            <tr>
                {$rows}
                <td>
                    <input type='submit' value='Search' class='button ml10'>
                </td>

                <td>
                    <a href='' id ='selectedStudents' class='button ml10 selectedStudents' month_val='{$month}'>Display Selected Students</a>
                </td>

                <!--<td>
                    <a href='{$failedStudentsLink}' id ='failedStudents' class='button ml10 failedStudents' month_val='{$month}'>Print GIRO failures</a>
                </td>-->
            </tr>
            
            <!--<tr>
                <td colspan='{$colspan}'></td>
                <td>
                    <a href='{$reportLink}' class='button mt10 ml10' target='_blank'>Print Group Invoices</a>
                </td>
            </tr>-->
        </table>
        </form>
        <script>
        </script>
        ";

        return $text;
    }

    /**
     *
     */
    function getSetInvoiceCodeForSession(){
        $fn = Zend_Registry::get('fn');

        $invoice_code = $fn->getReqParam('invoice_code'); 
        $is_checked = $fn->getReqParam('is_checked');
        
        $s = &$_SESSION['selectedInvoiceCodes'];
        
        $arryKey = array_search($invoice_code, $s);
        
        if ($is_checked == 'false'){
            unset($s[$arryKey]);
        } else {
            if (!in_array($invoice_code, $s)){
                $_SESSION['selectedInvoiceCodes'][] = $invoice_code;
            }
        }

        print_r($_SESSION['selectedInvoiceCodes']);
    }

    /**
     *
     */
    function getSetInvoiceCodeToPrintForSession(){
        $fn = Zend_Registry::get('fn');

        $invoice_code = $fn->getReqParam('invoice_code'); 
        $is_checked   = $fn->getReqParam('is_checked');
        
        $s = &$_SESSION['selectedInvoiceCodesToPrint'];        
        $arryKey = array_search($invoice_code, $s);
        
        if ($is_checked == 'false'){
            unset($s[$arryKey]);
        } else {
            if (!in_array($invoice_code, $s)){
                $_SESSION['selectedInvoiceCodesToPrint'][] = $invoice_code;
            }
        }

        print_r($_SESSION['selectedInvoiceCodesToPrint']);
    }

    /**
     * Displays the list of giro failure selected students in browser
     */
    function  getDisplayGiroFailures(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $rows = '';
        
        $month = $fn->getReqParam('month_val');
        
        $invoiceCodes = isset($_SESSION['selectedInvoiceCodes']) ? $_SESSION['selectedInvoiceCodes'] : 0;
        $count = count($invoiceCodes);
        
        if ($count == 0) {
            return "Please select the invoices";
        }
        
        $invoiceCodes = join(',', $invoiceCodes);
        $sessionExplode = explode(',', $invoiceCodes);
        
        $counter = 1;
        $count = count($sessionExplode);
        
        $invoice_code = '';
        foreach ($sessionExplode as $invoiceCode) {
            if ($count == $counter) {
                $invoice_code .= "'" . $invoiceCode . "'";
            } else {
                $invoice_code .= "'" . $invoiceCode . "',";
            }
            $counter++;
        }

        $sqlMain = $this->model->getSQL();

        $sqlWhere = "
        WHERE DATE_FORMAT(i.invoice_date, '%m') = '{$month}' 
          AND i.invoice_code IN
            (SELECT invoice_code FROM invoice
            WHERE invoice_code IN ($invoice_code))
        ";
        
        $sqlWhere = "
        WHERE DATE_FORMAT(i.invoice_date, '%m') = '{$month}' 
          AND i.invoice_code IN ($invoice_code)
        ";

        $SQL = $sqlMain . $sqlWhere;
        $result = $db->sql_query($SQL);
        while($row = $db->sql_fetchrow($result)) {
            $contact_name = $row['first_name'] . ' ' . $row['last_name'];
            $rows .= "
            <tr>
                <td class='mb5'><strong>{$contact_name}</strong></td>
                <td class='mb5'>{$row['parent_name']}</td>
                <td>{$row['course_title']}</td>
                <td>{$this->getInvoiceCheckBox($row['contact_id'], $row['order_id'], $month, 1)}</td>
                <td>
                    <a class='button' href='index.php?_topRm=finance&module=enterpriseIms_order&_action=edit&order_id={$row['order_id']}'  target=''><u>Goto Finance</u></a>
                </td>
            </tr>
            ";
        }
        
        $text = "
        <table class='thinlist room-giroPayment-table'>
            <tr>
                <tbody>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td><strong>Parent Name</strong></td>
                        <td><strong>Course Name</strong></td>
                        <td>Month</td>
                        <td></td>
                    </tr>
                    {$rows}
                    
                </tbody>
            </tr>
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getFailedStudentsDisplay(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $rows = '';
        
        $month = $fn->getReqParam('month_val');
        
        $sqlMain = $this->model->getSQL();
        $sqlWhere = "
        WHERE p.mode_of_payment = 'Giro'
         AND DATE_FORMAT(i.invoice_date, '%m') = '{$month}'
         AND (i.status = 'Due' OR i.status = 'Late')
        ";
        
        switch ($month) {
            case 1: $prefix_month = 'Jan';
            break;
            case 2: $prefix_month = 'Feb';
            break;
            case 3: $prefix_month = 'Mar';
            break;
            case 4: $prefix_month = 'Apr';
            break;
            case 5: $prefix_month = 'May';
            break;
            case 6: $prefix_month = 'Jun';
            break;
            case 7: $prefix_month = 'Jul';
            break;
            case 8: $prefix_month = 'Aug';
            break;
            case 9: $prefix_month = 'Sep';
            break;
            case 10: $prefix_month = 'Oct';
            break;
            case 11: $prefix_month = 'Nov';
            break;
            case 12: $prefix_month = 'Dec';
            break;
        }

        $SQL = $sqlMain . $sqlWhere;
        $result = $db->sql_query($SQL);
        while($row = $db->sql_fetchrow($result)) {
            $contact_name = $row['first_name'] . ' ' . $row['last_name'];
            $rows .= "
            <tr>
                <td class='mb5'><strong>{$contact_name}</strong></td>
                <td class='mb5'>{$row['parent_name']}</td>
                <td>{$row['branch_name']}</td>
                <td>{$prefix_month}</td>
                <td>
                    <a class='button' href='index.php?_topRm=finance&module=enterpriseIms_order&_action=edit&order_id={$row['order_id']}'  target=''><u>Goto Finance</u></a>
                </td>
            </tr>
            ";
        }
        
        $text = "
        <table class='thinlist room-giroPayment-table'>
            <tr>
                <tbody>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td><strong>Parent Name</strong></td>
                        <td><strong>Branch Name</strong></td>
                        <td><strong>Month</strong></td>
                        <td></td>
                    </tr>
                    {$rows}
                    
                </tbody>
            </tr>
        </table>
        ";

        return $text;
    }

    /**
     * Prints the list of giro failure students for the selected month in pdf
     */
    function getPrintGiroFailures() {
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
		$pdf->SetFont('Arial','',10);
        
        $rows = "";

        $month = $fn->getReqParam('month');
        
        $sqlMain = $this->model->getSQL();
        $sqlWhere = "
        WHERE p.mode_of_payment = 'Giro'
         AND DATE_FORMAT(i.invoice_date, '%m') = '{$month}'
         AND (i.status = 'Due' OR i.status = 'Late')
        ";
        
        $SQL = $sqlMain . $sqlWhere;
        $result = $db->sql_query($SQL);

        switch ($month) {
            case 1: $prefix_month = 'January';
            break;
            case 2: $prefix_month = 'February';
            break;
            case 3: $prefix_month = 'March';
            break;
            case 4: $prefix_month = 'April';
            break;
            case 5: $prefix_month = 'May';
            break;
            case 6: $prefix_month = 'June';
            break;
            case 7: $prefix_month = 'July';
            break;
            case 8: $prefix_month = 'August';
            break;
            case 9: $prefix_month = 'September';
            break;
            case 10: $prefix_month = 'October';
            break;
            case 11: $prefix_month = 'November';
            break;
            case 12: $prefix_month = 'December';
            break;
        }

        $numRows  = $db->sql_numrows($result);
        $today = date("Y-m-d");
		if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "All the students have paid their fees for the month");
			$pdf->Output('Filename.pdf', 'D');
			return;
		}
        //============================================================================= //
        $pdf->SetFont('Arial','',10);
        /* HEADER */
        $pdf->Image('images/logo-print.jpg',10,5,45);

        /* Institute company address */
        $pdf->SetXY(115,1);
        $pdf->SetFillColor(255,255,255);
        //$pdf->Rect(10 , 5, 80, 38, 'F');
        $pdf->SetFont('Arial','B',10);
        $pdf->SetX(115);
        $pdf->Cell(50, 20, $cpCfg['printCompanyName']);
        $pdf->SetFont('Arial','',7);
        $pdf->Ln(5);
        $pdf->SetX(115);
        $pdf->Cell(50, 20, $cpCfg['printRegistrationNo']);
        $pdf->SetFont('Arial','',10);
        $pdf->Ln(5);
        $pdf->SetX(115);
        $pdf->Cell(50, 20, $cpCfg['printAddressFlatAndStreet']);
        $pdf->Ln(5);
        $pdf->SetX(115);
        $pdf->Cell(50, 20, $cpCfg['printAddressCountryAndCode']);
        $pdf->Ln(5);
        $pdf->SetX(115);
        $pdf->Cell(50, 20, $cpCfg['printTelephoneAndFax']);

        $pdf->SetY(55);
        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(80, 8, "List of Giro Failures for Month of {$prefix_month}", 'B');
        $pdf->Ln(15);

        $pdf->Cell(80, 8, "Name",1);
        $pdf->Cell(60, 8, "Parent Name",1);
        $pdf->Cell(35, 8, "Class",1);
        $pdf->Cell(20, 8, "DDA",1);
        $pdf->Ln();
                    
        while ($row = $db->sql_fetchrow($result)) {

            $pdf->SetFont('Arial','', 10);
            $pdf->Cell(80, 8, $row['first_name'] . ' ' . $row['last_name'],1);
            $pdf->Cell(60, 8, $row['parent_name'],1);
            $pdf->Cell(35, 8, $row['course_title'],1);
            $pdf->Cell(20, 8, $row['dda'],1);
            $pdf->Ln(8);
        } 

        //===================END OF TABLE========================================== //
        
        /* Creation of media record of the invoice */
        $file_name = 'GIROFailures_' . date('Y-m-d') .'.pdf';
        $pdf->Output($file_name,'D');
    }
    /**
     * Invoice PDF in Fpdf for more than one student for a parent of IenterpriseIms
     */
    function  getPrintAllDueInvoiceForMonth(){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        ini_set('memory_limit', '512M');
        
        $month = $fn->getReqParam('month');
        $year  = $fn->getReqParam('year');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/fpdf/fpdf.php');
        include_once(CP_LIBRARY_PATH.'lib_php/fpdf-extra/html_table1.php');

        $pdf = new MYPDF();

		$pdf->AddPage();
		$pdf->SetFont('Arial','',10);

        $SQL = "
        SELECT DISTINCT p.parent_id
        FROM parent p
        LEFT JOIN (parent_contact pc) ON (p.parent_id   = pc.parent_id)
        JOIN (invoice i)              ON (pc.contact_id = i.contact_id)
        LEFT JOIN (contact c)         ON (i.contact_id  = c.contact_id)
        WHERE i.status = 'Due'
        AND c.status = 'Active'
        AND i.invoice_month = {$month}
        AND DATE_FORMAT(i.invoice_date, '%Y') = '{$year}'
        AND p.mode_of_payment = 'Giro'
        ORDER BY p.parent_id
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $count = 1;
        
        $modObj = getCPModuleObj('enterpriseIms_order');
        while($row = $db->sql_fetchrow($result)) {
            $invoice_code = '';
            
            $SQLInv = "
            SELECT i.invoice_code, pc.parent_id
            FROM invoice i
            LEFT JOIN (parent_contact pc) ON (pc.contact_id = i.contact_id)
            LEFT JOIN (contact c)         ON (i.contact_id  = c.contact_id)
            WHERE i.status = 'Due' AND i.invoice_month = {$month}
            AND DATE_FORMAT(i.invoice_date, '%Y') = '{$year}'
            AND pc.parent_id = {$row['parent_id']}
            AND c.status = 'Active'
            ORDER BY i.invoice_code
            ";
            $resultInv  = $db->sql_query($SQLInv);
            $numRowsInv = $db->sql_numrows($resultInv);
            if ($numRowsInv > 0) {
                while($rowInv = $db->sql_fetchrow($resultInv)) {
                    $invoice_code = $invoice_code . "'" . $rowInv['invoice_code'] . "'" .',';
                }
                
                $invoice_code = substr($invoice_code,0,-1);            
                $modObj->model->getPrintGroupInvoiceInFpdf($invoice_code, $pdf, 1);
                
                if ($count < $numRows) {
                    $pdf->AddPage();
                }
                
                $count++;
            }
        }
        $pdf->Output();
    }
}