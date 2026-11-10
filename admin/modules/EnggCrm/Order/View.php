<?
class CPL_Admin_Modules_EnggCrm_Order_View extends CP_Admin_Modules_EnggCrm_Order_View
{
    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = "";
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y');
            $currency = strtoupper($row['currency']);
            
            $order_amount = $row['order_amount'];
            $invoice_amount = $row['invoice_amount'];
            if($row['project_type'] == 'Manpower Supply'){
                $SQLOrderItems = "
                SELECT SUM(employee_ot_hours*ot_hourly_rate) AS totalOTAmount
                      ,SUM(employee_ph_hours*ph_hourly_rate) AS totalPHAmount
                FROM order_item
                WHERE order_id = {$row['order_id']}
                ";
                $resultOrderItems = $db->sql_query($SQLOrderItems);
                $rowOrderItems    = $db->sql_fetchrow($resultOrderItems);

                $order_amount = $row['order_amount'] + $rowOrderItems['totalOTAmount'] + $rowOrderItems['totalPHAmount'];
            }

            if($cpCfg['m.enggCrm.order.addGstAmountToOrderTotal']){
                $gsttaxperc   = $cpCfg['amtForGSTCalc'] ;
                $order_amount = $row['order_amount'] + ($row['order_amount'] * $gsttaxperc/100);
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($row['order_id'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($creation_date)}
            {$listObj->getListDataCell($row['project_code'])}
            {$listObj->getListDataCell($row['quote_code'])}
            {$listObj->getListDataCell($row['project_type'])}
            {$listObj->getListDataCell($currency.'&nbsp;'.number_format($invoice_amount, 3))}
            {$listObj->getListDataCell($row['order_status'])}
            {$listObj->getListRowEnd($row['order_id'])}
            ";
            $rowCounter++ ;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Order Id', 'o.order_id')}
        {$listObj->getListHeaderCell('Company Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Order Date', 'o.creation_date')}
        {$listObj->getListHeaderCell('Project Code', 'o.project_code')}
        {$listObj->getListHeaderCell('Quote code', 'o.quote_code')}
        {$listObj->getListHeaderCell('Project Type', 'o.project_type')}
        {$listObj->getListHeaderCell('Amount', '')}
        {$listObj->getListHeaderCell('Status', 'o.order_status')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');


        $formObj->mode = $tv['action'];

        $expStatus = array('sqlType' => 'OneField', 'isEditable' => 0);
        $expNoEdit = array('isEditable' => 0);
        $expCustAddress = array('isEditable' => 1);

        $sqlCountry = getCPModelObj('common_geoCountry')->getCountryDDSQL();
        $expCountry = array('detailValue' => $row['shipping_address_country']);

        $creation_date = $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY');
        $start_date = $dateUtil->formatDate($row['start_date'], 'DD-MM-YYYY');
        $end_date = $dateUtil->formatDate($row['end_date'], 'DD-MM-YYYY');

        $currency = strtoupper($row['currency']);

        $order_amount = $row['order_amount'];
        if($row['project_type'] == 'Manpower Supply'){
            $SQLOrderItems = "
            SELECT SUM(employee_ot_hours*ot_hourly_rate) AS totalOTAmount
                  ,SUM(employee_ph_hours*ph_hourly_rate) AS totalPHAmount
            FROM order_item
            WHERE order_id = {$row['order_id']}
            ";
            $resultOrderItems = $db->sql_query($SQLOrderItems);
            $rowOrderItems    = $db->sql_fetchrow($resultOrderItems);

            $order_amount = $row['order_amount'] + $rowOrderItems['totalOTAmount'] + $rowOrderItems['totalPHAmount'];
        }
        

        if($cpCfg['m.enggCrm.order.addGstAmountToOrderTotal']){
            $gsttaxperc   = $cpCfg['amtForGSTCalc'] ;
            $order_amount = $order_amount + ($order_amount * $gsttaxperc/100);
        }

        $discount = '';
        if ($cpCfg['m.enggCrm.order.hasDiscount']){
            $discount = $formObj->getTBRow('Discount', 'discount', $row['discount']);
        }

        $company_id = isset($row['company_id']) ? $row['company_id'] : 0;
        $sqlCompany = "
        SELECT company_id, company_name FROM company
        ORDER BY company_name
        ";

        $expComp = array(
             'detailValue' => $row['company_name']
            ,'autoSgstModule' => 'enggCrm_company'
            ,'autoSgstSrchFld' => 'company_name'
            ,'autoSgstActualFld' => 'company_id'
            ,'autoSgstActualFldVal' => $company_id
            ,'autoSgstCallBack' => 'cpm.enggCrm.order.loadContactsByCompany'
        );

        $project_code = "<a href='index.php?_topRm=project&module=enggCrm_project&record_id={$row['project_id']}&_action=edit' target='_blank'><u>{$row['project_code']}</u></a>";
        $quote_code = "<a href='index.php?_topRm=project&module=enggCrm_opportunity&record_id={$row['opportunity_id']}&_action=edit' target='_blank'><u>{$row['quote_code']}</u></a>";

        $fielset1 = "
        {$formObj->getTBRow('Order Id', 'order_id', $row['order_id'], $expNoEdit)}
        {$formObj->getTBRow('Project Code', 'project_id', $project_code, $expNoEdit)}
        {$formObj->getTBRow('Quote Code', 'quote_id', $quote_code, $expNoEdit)}
        {$formObj->getTBRow('Project Category', 'project_type', $row['project_type'], $expNoEdit)}
        {$formObj->getTBRow('Order Date', 'creation_date', $creation_date, $expNoEdit)}
        <!--{$formObj->getTBRow('Amount', 'amount', $currency.'&nbsp;'. number_format($order_amount, 3), $expNoEdit)}
        {$discount}-->
        {$formObj->getDDRowByArr('Status', 'order_status', $cpCfg['m.enggCrm.order.statusArr'], $row['order_status'], $expStatus)}
        {$formObj->getTARow('Terms', 'invoice_terms', $row['invoice_terms'])}
        {$formObj->getTARow('Notes', 'notes', $row['notes'])}
        ";

        $custAddress1 = isset($row['cust_address1']) ? $row['cust_address1'] : (isset($row['address_flat']) ? $row['address_flat'] : '');
        $custAddress2 = isset($row['cust_address2']) ? $row['cust_address2'] : (isset($row['address_street']) ? $row['address_street'] : '');
        $custAddressCountry = isset($row['cust_address_country']) ? $row['cust_address_country'] : (isset($row['address_country']) ? $row['address_country'] : '');
        $custAddressPoCode = isset($row['cust_address_po_code']) ? $row['cust_address_po_code'] : (isset($row['address_po_code']) ? $row['address_po_code'] : '');
        $expCustCountry = array('detailValue' => $custAddressCountry);

        $fielset2 = "
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany, $company_id, $expComp)}
        {$formObj->getTBRow('Address 1', 'cust_address1', $custAddress1, $expCustAddress)}
        {$formObj->getTBRow('Address 2', 'cust_address2', $custAddress2, $expCustAddress)}
        {$formObj->getDDRowBySQL('Country', 'cust_address_country', $sqlCountry, $custAddressCountry, $expCustCountry)}
        {$formObj->getTBRow('Postal Code', 'cust_address_po_code', $custAddressPoCode, $expCustAddress)}
        ";

        $fielset3 = "
        {$formObj->getTBRow('Company Name', 'shipping_first_name', $row['shipping_first_name'])}
        {$formObj->getTBRow('Address 1', 'shipping_address1', $row['shipping_address1'])}
        {$formObj->getTBRow('Address 2', 'shipping_address2', $row['shipping_address2'])}
        {$formObj->getTBRow('Country', 'shipping_address_country', $row['shipping_address_country'])}
        {$formObj->getTBRow('Postal Code', 'shipping_address_po_code', $row['shipping_address_po_code'])}
        {$formObj->getDateRow('Delivery Date', 'delivery_date', $row['delivery_date'])}
        {$formObj->getTARow('Delivery Terms', 'delivery_terms', $row['delivery_terms'])}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Main Details', $fielset1)}
        {$formObj->getFieldSetWrapped('Delivery Address', $fielset3)}
        {$formObj->getFieldSetWrapped('Customer Details', $fielset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

     /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $sqlCompany = "
        SELECT company_id, company_name FROM company
        ORDER BY company_name
        ";

        $newCompUrl = 'index.php?_spAction=new&lnkRoom=enggCrm_companyLink&showHTML=0';
        $newCompUrl = "<a class='jqui-dialog-form float_left newCompanyLink' formId='portalForm' title='New Company'
        w=800 href='' link='{$newCompUrl}' callback='cpm.enggCrm.order.afterNewCompany'>New</a>";

        $editCompUrl = 'index.php?module=enggCrm_order&_spAction=editCompanyName&showHTML=0';
        $editCompUrl = "<a class='jqui-dialog-form float_left editCompanyLink' formId='portalForm' title='Edit Company'
        w=800 href='' link='{$editCompUrl}' callback='cpm.enggCrm.order.afterEditCompany'>Edit</a>";

        $expComp  = array(
               'notesRight'  => $newCompUrl . '&nbsp;' . $editCompUrl
            ,'rowCls' => 'companyNameWithActions'
            ,'autoSgstModule' => 'enggCrm_company'
            ,'autoSgstSrchFld' => 'company_name'
            ,'autoSgstActualFld' => 'company_id'
            ,'autoSgstActualFldVal' => ''
            ,'autoSgstCallBack' => 'cpm.enggCrm.order.loadContactsByCompany'
        );

        $sqlContact = '';
        $sqlProject='';

        $newContactUrl = 'index.php?_spAction=new&lnkRoom=enggCrm_contactLink&showHTML=0';
        $newContactUrl = "<a class='jqui-dialog-form float_left newContactLink' formId='portalForm' title='New Contact'
        w=800 href='' link='{$newContactUrl}' callback='cpm.enggCrm.order.afterNewContact'>New</a>";

        $expCont  = array(
             'notesRight'  => $newContactUrl
        );

        $expVl   = array('sqlType' => 'OneField');
        $sqlCat  = $fn->getValueListSQL('projectCategory');
 

        $fieldset = "
        {$formObj->getDateRow('Order Date', 'order_date')}
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany, '', $expComp)}
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlContact, '', $expCont)}
        {$formObj->getDDRowBySQL('Quote', 'quote_id', $sqlProject)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    function getEditCompanyName(){
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $company_id = (int) $fn->getReqParam('company_id');
        $company = $fn->getRecordRowByID('company', 'company_id', $company_id);
        $formAction = "index.php?module=enggCrm_order&_spAction=editCompanyNameSubmit&showHTML=0";

        $sqlCompany = "
        SELECT company_id, company_name FROM company
        ORDER BY company_name
        ";

        $editCompUrl = "index.php?module=enggCrm_order&_spAction=editCompanyName&showHTML=0&company_id={$company_id}";
        $editCompUrl = "<a class='jqui-dialog-form float_left editCompanyLink' formId='portalForm' title='Edit Company'
        w=800 href='' link='{$editCompUrl}' callback='cpm.enggCrm.order.afterEditCompany' data-company-id='{$company_id}'>Edit</a>";

        $expComp  = array(
             'notesRight'  => $editCompUrl
            ,'rowCls' => 'companyNameWithActions'
            ,'autoSgstModule' => 'enggCrm_company'
            ,'autoSgstSrchFld' => 'company_name'
            ,'autoSgstActualFld' => 'company_id'
            ,'autoSgstActualFldVal' => $company_id
            ,'autoSgstCallBack' => 'cpm.enggCrm.order.loadContactsByCompany'
        );

        return "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany, $company_id, $expComp)}
            <input type='hidden' name='company_id' value='{$company_id}' />
            <input type='hidden' name='company_name' value='{$company['company_name']}' />
        </form>
        ";
    }

    /**
     *
     */
    function getPrintDeliveryOrder() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');

        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $order_id = $fn->getReqParam('order_id');

        $SQL = "
        SELECT oi.*
                ,o.order_id
                ,o.order_date
                ,o.order_code
                ,o.invoice_terms
                ,o.shipping_first_name
                ,o.shipping_address1
                ,o.shipping_address2
                ,o.shipping_address_country
                ,o.shipping_address_po_code
                ,o.delivery_date
                ,o.delivery_terms
                ,c.company_name
                ,c.address_street
                ,c.address_country
                ,c.address_po_code
                ,c.company_id
                ,co.first_name
                ,co.last_name
                ,(SELECT SUM(oit.qty * oit.unit_price) FROM order_item oit
                  WHERE oit.order_id = oi.order_id) AS sub_total
        FROM order_item oi
        LEFT JOIN `order` o ON (o.order_id = oi.order_id)
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        WHERE oi.order_id = '{$order_id}'
        ORDER BY oi.order_item_id
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $today = date("d-m-Y");
        $order_date    = $fn->getCPDate($company['order_date'], 'd-m-Y');
        $delivery_date = $fn->getCPDate($company['delivery_date'], 'd-m-Y');

        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold">DELIVERY ORDER</td>
            </tr>
        </table>
        ';

        $or_date = $fn->getCPDate($company['order_date'], 'ym/');
        $order_code = $or_date . $company['order_id'];
        $address2 = '';
        if($company['shipping_address2']) {
            $address2 = '
            <tr>
                <td style="font-size:12px;">'.$company['shipping_address2'].'</td>
                <td colspan="2"></td>
            </tr>
            ';
        }
        $tbl2 ='<table border="0" width="100%" cellpadding="">
                    <tr>
                        <td width="60%" style="font-size:12px; font-weight:bold;">TO: </td>
                        <td width="26%" align="right" style="font-size:12px; font-weight:bold;"><b>DO NO&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</b></td>
                        <td width="14%" align="right" style="font-size:12px; font-weight:bold;">DO-'.$order_code.'</td>
                    </tr>
                    <tr>
                        <td width="60%" style="font-size:12px; font-weight:bold;">'.$company['shipping_first_name'].'</td>
                        <td width="26%" align="right" style="font-size:12px; font-weight:bold;"><b>DATE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </b></td>
                        <td width="14%" align="right" style="font-size:12px; font-weight:bold;">'.$order_date.'</td>
                    </tr>
                    <tr>
                        <td style="font-size:12px;">'.strtoupper($company['shipping_address1']).'</td>
                        <td colspan="2"></td>
                    </tr>
                    '.$address2.'
                    <tr>
                        <td style="font-size:12px;">'.strtoupper($company['shipping_address_country']).' - '.strtoupper($company['shipping_address_po_code']).'</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td style="font-size:12px; font-weight:bold;">ATTN:&nbsp;'.$company['first_name'].' '.$company['last_name'].'</td>
                        <td colspan="2"></td>
                    </tr>
                </table>';

        $tbl3 ='<table border="1" cellpadding="2" width="100%">
                    <thead>
                        <tr>
                            <th width="5%" align="center" style="font-size:12px; font-weight:bold;">S/N</th>
                            <th width="60%" align="center" style="font-size:12px; font-weight:bold;">Description</th>
                            <th width="13%" align="center" style="font-size:12px; font-weight:bold;">QTY</th>
                            <th width="22%" align="center" style="font-size:12px; font-weight:bold;">REMARKS</th>
                        </tr>
                    </thead>';
        $sub_total = '';
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            if($row['item_title']) {
                $tbl3 = $tbl3.'<tr>
                                    <td width="5%"></td>
                                    <td width="60%" style="font-size:12px; font-weight:bold;"><u>'.strtoupper($row['item_title']).'</u></td>
                                    <td width="13%"></td>
                                    <td width="22%"></td>
                                </tr>
                        ';
            }
            $tbl3 = $tbl3.'<tr>
                                <td width="5%" style="font-size:12px;">'.$count.'</td>
                                <td width="60%" style="font-size:12px;">'.$row['description'].'</td>
                                <td width="13%" align="center" style="font-size:12px;">'.$row['qty'].'</td>
                                <td width="22%" style="font-size:12px;">'.$row['remarks'].'</td>
                            </tr>
                    ';
            $count++;
        }

        $tbl3 = $tbl3.'</table>';

        $tbl5 = '
        <table border="0" width="100%">
            <tr>
                <td style="height: 15px;"></td>
            </tr>
            <tr>
                <td align="left" style="font-size:12px; font-weight:bold;">DELIVERY DATE :</td>
            </tr>
            <tr>
                <td align="left" style="font-size:12px;">'. $delivery_date .'</td>
            </tr>
            <tr>
                <td style="height: 40px;"></td>
            </tr>
            <tr>
                <td style="font-size:12px; font-weight:bold;">REMARKS : </td>
            </tr>
            <tr>
                <td align="left" style="font-size:12px;">'. $company['delivery_terms'] .'</td>
            </tr>
        </table>
        ';

        $tbl6 = '
        <table border="0" width="100%">
            <tr>
                <td colspan="2" style="height: 20px;"></td>
            </tr>
            <tr>
                <td width="40%" style="font-size:12px;">Issued By :</td>
                <td width="60%" style="font-size:12px;" align="right">Above goods received in good condition</td>
            </tr>
            <tr>
                <td colspan="2" style="font-size:12px; font-weight:bold;">'.$cpCfg['cp.companyName'].'</td>
            </tr>
            <tr>
                <td colspan="2" style="height: 20px;"></td>
            </tr>
            <tr>
                <td width="67%"></td>
                <td width="30%" style="border-bottom:2px solid black"></td>
            </tr>
            <tr>
                <td></td>
                <td style="font-size:12px; font-weight:bold;">Authorised Signature & Name</td>
            </tr>
            <tr>
                <td></td>
                <td style="font-size:12px;">Date:</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');
        $pdf->Output('Delivery-Order.pdf', 'I');
    }

     /**
     *
     */
    function getPrintinvoice() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootquote.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(8, PDF_MARGIN_TOP, 8);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(4);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 5);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $quote_id         = $fn->getReqParam('quote_id');
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';
        $invoice_id       = $fn->getReqParam('invoice_id');
        $totalvalue       = 0;

        // Currency based on site
$site_id = $fn->getSessionParam('cp_site_id');

switch ($site_id) {
    case 1:
        $currency = 'KWD';
        break;

    case 2:
        $currency = 'SAR';
        break;

    default:
        $currency = 'KWD';
        break;
}

        $SQL = "
        SELECT ini.*
                ,c.company_name
                ,c.address_flat
                ,o.cust_address1
                ,o.cust_address2
                ,o.cust_address_po_code
                ,o.cust_email
                ,o.cust_phone
                ,o.cust_fax
                ,gc.name AS cust_address_country
                ,c.company_id
                ,i.invoice_date
                ,ini.unit_price
                ,i.invoice_code
                ,i.invoice_type
                ,i.qty_text
                ,i.rate_text
                ,i.invoice_terms
                ,i.invoice_due_date
                ,i.notes
                ,i.gst_percentage
                ,i.discount
                ,i.project_location
                ,i.project_reference
                ,i.title AS invoice_title
                ,i.payment_terms
                ,i.apply_digital_signature
                ,i.signature_name
                ,i.po_number
                ,i.subject
                ,co.first_name
                ,co.salutation
              ,e.employee_id
              ,e.employee_name
              ,e.email AS employee_email
              ,e.mobile AS employee_mobile
        FROM invoice_item ini
        LEFT JOIN invoice i  ON (i.invoice_id  = ini.invoice_id)
        LEFT JOIN `order` o  ON (o.order_id    = i.order_id)
        LEFT JOIN company c  ON (c.company_id  = o.company_id)
        LEFT JOIN employee e  ON (e.employee_id  = i.employee_id)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        LEFT JOIN geo_country gc ON (o.cust_address_country = gc.country_code)
        WHERE i.invoice_id = '{$invoice_id}'
        ORDER BY ini.invoice_item_id
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $today = date("d-m-Y");
        $invoice_date = $fn->getCPDate($company['invoice_date'], 'd M Y');

        /*$sqlCompAdd = "
        SELECT ca.*
        FROM company_address ca
        WHERE ca.company_id = {$company['company_id']}
        LIMIT 0,1
        ";
        $resultCompAdd = $db->sql_query($sqlCompAdd);
        $rowCompAdd = $db->sql_fetchrow($resultCompAdd);*/


        $seal='';
        $signname='';

        if($company['apply_digital_signature'] == 1){
         $seal='<td width="10%"  style="font-size:15px;"><img src="images/teamseal.jpg" width="60"/></td>';
         if($company['signature_name'] == "Jassim"){  
        $signname='<td width="25%"  align="left"><img src="images/jassim.jpg" width="80" /></td>';
         } else if($company['signature_name'] == "Ibrahim"){  
            $signname='<td width="25%"  align="left"><img src="images/ibrahim.jpg" width="80" /></td>';
             } else if($company['signature_name'] == "Wassim"){  
                $signname='<td width="25%"  align="left"><img src="images/wasim.jpg" width="80" /></td>';
                 }else{
                    $signname='<td width="25%"  align="left"></td>';
                 }
        }else{
            $seal='<td width="10%"  style="font-size:15px;"></td>';
        }


        $tbl1 = '
        <table border="0" width="100%" style="" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#14213d; text-decoration:underline; line-height:35px;">INVOICE</td>
            </tr>
        </table>
        ';

        $tbl2 ='<table border="0" width="100%" cellpadding="2" cellspacing="0">
                   
                    <tr><td width="40%" style="border:1px solid #000;"><table border="0" cellpadding="0">
                     <tr>
                        <td width="33%" style="font-size:10px; font-weight:bold; line-height:16px;">To : </td>
                        <td width="34%"></td>
                    </tr>
                                <tr>
                                    <td width="100%" style="font-size:10px;line-height:16px;"> '.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                
                                    <td width="75%" style="font-size:10px;line-height:16px;"> '.$company['address_flat'].'</td>
                                </tr>
                               
                            </table>
                        </td>
                        <td width="10%"></td>
                        <td width="40%" style="border:1px solid #000;"><table border="0">
                               
                                <tr>
                                    <td width="25%" style="font-size:10px;font-weight:bold;line-height:16px;"> Date</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$invoice_date.'</td>
                                </tr>
                                <br/>
                                 <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;font-weight:bold;"> Ref :</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$company['invoice_code'].'</td>
                                </tr>
                                  <br/>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;font-weight:bold;"> PO</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$company['po_number'].'</td>
                                </tr>
                               
                            </table>
                        </td>
                    </tr>
                </table>
               
               ';

                 $tbl7 = '
        <table border="0">
         <tr>
                <td width="100%" align="left" style="font-size:11px;"><b>Subject</b> : '.$company['subject'].'</td>
            </tr>
          
           
        </table>';
 
        $tbl3 ='<table border="0"  cellpadding="4"  width="100%">
                    <thead>
                        <tr >
                            <th width="6%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                            <th width="55%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">JOB DESCRIPTION</th>
                            <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UOM</th>
                            <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                            <th width="12%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT PRICE('.$currency.')</th>
                            <th width="13%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">TOTAL PRICE('.$currency.')</th>
                        </tr>
                    </thead>';
        $subtotalValue   = 0;
        $subtotalValueMinus   = 0;
        $count      = 1;
        $countCheck = 1;
        $subtotal_amount_minus = 0;
        while ($row = $db->sql_fetchrow($result)) {

            if ($row['item_title']) {
                $countCheck++;
                $tbl3 = $tbl3.'<tr>
                                    <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="55%" style="font-size:10px;  border-left:1px solid #000;border-right:1px solid #000;">'.nl2br($row['item_title']).'<br/></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="13%" style="border-right:1px solid #000;"></td>
                                </tr>
                        ';
            }

            if($row['add_minus'] == 'Minus'){
            	if ($row['amount'] != "") {
                	$subtotal_amount_minus = round($row['amount'], 3);
            	} else if($row['unit_price'] > 0 && $row['qty'] > 0) {
                	$subtotal_amount_minus = round($row['qty'] * $row['unit_price'], 3);
            	} else if ($row['unit_price'] > 0 && $row['qty'] == 0) {
                	$subtotal_amount_minus = round($row['unit_price'], 3);
            	}
            	$subtotal_amount_formatted = number_format(($subtotal_amount_minus) , 3);
            	$subtotalValueMinus += $subtotal_amount_minus;
            } else {
            	if ($row['amount'] != "") {
                	$subtotal_amount = round($row['amount'], 3);
            	} else if($row['unit_price'] > 0 && $row['qty'] > 0) {
                	$subtotal_amount = round($row['qty'] * $row['unit_price'], 3);
            	} else if ($row['unit_price'] > 0 && $row['qty'] == 0) {
                	$subtotal_amount = round($row['unit_price'], 3);
            	}
            	$subtotal_amount_formatted = number_format(($subtotal_amount) , 3);
            	$subtotalValue += $subtotal_amount;
            }


            if($row['qty'] == 0) {
                $row['qty'] = "";
            }

            if($row['unit_price'] == 0) {
                $row['unit_price'] = '';
            }

            if($subtotal_amount_formatted == "0.00") {
                $subtotal_amount_formatted = "";
            }

            $tbl3 = $tbl3.'<tr>
                                <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit'].'</td>
                                <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['qty'].'</td>
                                <td width="12%" align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit_price'].'</td>
                                <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;">'.$subtotal_amount_formatted.'</td>
                            </tr>
                    ';
      
            $totalvalue = $subtotalValue - $subtotalValueMinus;            

            $count++;
            $countCheck++;
        }

        $totalvalue        = round($totalvalue - $company['discount'], 3);
        $amount_in_words   = $fn->getConvertNumber($totalvalue);
      
        $discountRow = '';
        if($company['discount'] > 0) {
            $discountRow = '
            <tr>
              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border:1px solid #000;"></td>
              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; border-top:1px solid #000; font-weight:bold;">DISCOUNT</td>
              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000; border-top:1px solid #000;">'.number_format($company['discount'], 3).'</td>
            </tr>
            ';
        } else {
            $discountRow = '
            <tr>
              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border:1px solid #000;"></td>
              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; border-top:1px solid #000; font-weight:bold;">DISCOUNT</td>
              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000; border-top:1px solid #000;">-&nbsp;&nbsp;&nbsp;</td>
            </tr>
            ';
        }

        if($company['gst_percentage'] > 0) {
            $tbl3 = $tbl3.''.$discountRow.'<tr>
                              <td colspan="2" style="font-size:10px; border:1px solid #000;border-right:1px solid #000;">Invoice Amount In '.$currency.' : '.$amount_in_words.'</td>
                            
                              <td align="right" colspan="4" style="font-size:10px; font-weight:bold; border:1px solid #000;">'.number_format($totalvalue, 3, '.', '').'</td>
                          </tr>
                         
                          
                        </table>';
        } else {
            $tbl3 = $tbl3.''.$discountRow.'<tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border:1px solid #000;border-right:1px solid #000;">Invoice Amount In '.$currency.' : '.$amount_in_words.'</td>
                              <td colspan="3" align="right" style="font-size:10px; border-top:1px solid #000;border-right:1px solid #000; border-top:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">Total Excluding Gst</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000; border-right:1px solid #000; border-top:1px solid #000; border-bottom:1px solid #000;">'.number_format($totalvalue, 3, '.', '').'</td>
                           </tr>
                        </table>';
        }

        $tbl4 = '
        <table border="0" width="100%" cellpadding="2">
            <tr>
                <td align="left" width="60%" style="font-size:10px;line-height:8px;">'.nl2br($company['payment_terms']).'</td>
            </tr>
        </table>';

        $SQLMedia = "
        SELECT file_name, record_type
        FROM media
        WHERE record_id = '{$company['employee_id']}'
        AND room_name   = 'enggCrm_employee'
        AND record_type = 'picture'
        ";
        $resultMedia  = $db->sql_query($SQLMedia);

        $imageAttached = '';
        while($rowMedia = $db->sql_fetchrow($resultMedia)) {
            $imageAttached = realpath($cpCfg['cp.mediaFolder']).'/normal/'.$rowMedia['file_name'];
        }

    	if($company['apply_digital_signature'] == 1){
            $seal='<td width="10%"  style="font-size:15px;"><img src="images/teamseal.jpg" width="60"/></td>';
            $signname='<td width="25%"  align="left"><img src="'.$imageAttached.'"></td>';
        }else{
            $seal='<td width="10%"  style="font-size:15px;"></td>';
        }

        $tbl5 = '
        <table border="0" width="100%" cellpadding="3">
        <tr>
        '.$signname.'
        '.$seal.'
        </tr>
            <tr>
            <br/>
                <td width="40%" style=" font-weight:bold; font-size:10px;">For <br/>'.$cpCfg['cp.companyName'].'</td>
                <td width="30%"></td>
                <td width="30%"></td>
            </tr>
          
        </table>
        ';

        $tbl6 = '
        <table border="0" width="100%" cellpadding="3">
         <tr>
            <br/><br/>
                <td width="40%" style=" font-weight:bold; font-size:13px;">Bank Details</td>
              
            </tr>
            <tr>
                <td width="100%" style="font-size:12px;">'.$company['payment_terms'].'</td>
               
            </tr>
          
        </table>
        ';
        
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-6);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
          $pdf->ln(4);
        $pdf->writeHTML($tbl7, true, false, false, false, '');
        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->ln(5);
        //$pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $company['invoice_code'] .'-A Team'. '-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintinvoiceOld() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(8, PDF_MARGIN_TOP, 8);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(4);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 5);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $quote_id         = $fn->getReqParam('quote_id');
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';
        $invoice_id       = $fn->getReqParam('invoice_id');
        $totalvalue       = 0;

        $SQL = "
        SELECT ini.*
                ,c.company_name
                ,o.cust_address1
                ,o.cust_address2
                ,o.cust_address_po_code
                ,o.cust_email
                ,o.cust_phone
                ,o.cust_fax
                ,gc.name AS cust_address_country
                ,c.company_id
                ,i.invoice_date
                ,ini.unit_price
                ,i.invoice_code
                ,i.invoice_type
                ,i.qty_text
                ,i.rate_text
                ,i.invoice_terms
                ,i.invoice_due_date
                ,i.notes
                ,i.gst_percentage
                ,i.discount
                ,i.project_location
                ,i.project_reference
                ,i.title AS invoice_title
                ,i.payment_terms
                ,i.po_number
                ,co.first_name
                ,co.salutation
        FROM invoice_item ini
        LEFT JOIN invoice i  ON (i.invoice_id  = ini.invoice_id)
        LEFT JOIN `order` o  ON (o.order_id    = i.order_id)
        LEFT JOIN company c  ON (c.company_id  = o.company_id)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        LEFT JOIN geo_country gc ON (o.cust_address_country = gc.country_code)
        WHERE i.invoice_id = '{$invoice_id}'
        ORDER BY ini.invoice_item_id
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $today = date("d-m-Y");
        $invoice_date = $fn->getCPDate($company['invoice_date'], 'd M Y');

        /*$sqlCompAdd = "
        SELECT ca.*
        FROM company_address ca
        WHERE ca.company_id = {$company['company_id']}
        LIMIT 0,1
        ";
        $resultCompAdd = $db->sql_query($sqlCompAdd);
        $rowCompAdd = $db->sql_fetchrow($resultCompAdd);*/

        $tbl1 = '
        <table border="0" width="100%" style="border-top: 1px solid #0e502a;" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#14213d; text-decoration:underline; line-height:35px;">TAX INVOICE</td>
            </tr>
        </table>
        ';

        $tbl2 ='<table border="0" width="100%" cellpadding="2" cellspacing="0">
                    <tr>
                        <td width="33%" style="font-size:10px; font-weight:bold; background-color:#ededf0; border:1px solid #000; line-height:16px;"> Bill To : </td>
                        <td width="34%"></td>
                        <td width="33%" style="font-size:10px; font-weight:bold; background-color:#ededf0; border:1px solid #000; line-height:16px;"> Bill From : </td>
                    </tr>
                    <tr><td width="33%" style="border:1px solid #000;"><table border="0" cellpadding="0">
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> Name</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$company['first_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> CO. Name</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> Address</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$company['cust_address1'].',<br/>: '.$company['cust_address2'].', <br/>: '.$company['cust_address_country'].' - '.$company['cust_address_po_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> Email</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: email</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> Tel</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$company['cust_phone'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> Fax</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$company['cust_fax'].'</td>
                                </tr>
                            </table>
                        </td>
                        <td width="34%"></td>
                        <td width="33%" style="border:1px solid #000;"><table border="0">
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> Invoice No</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$company['invoice_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> Date</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$invoice_date.'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> PO. NO</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$company['po_number'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> Terms</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$company['invoice_terms'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> Email</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: info@cubosale.com</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> Tel</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: 6666 6666</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;"> Fax</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: 6666 7777</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <br/><br/>
                <table border="0">
                    <tr>
                        <td width="13%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Project location</td>
                        <td width="2%"  align="left" style="font-weight:bold; font-size:10px; line-height:20px;">:</td>
                        <td width="85%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">'.$company['project_location'].'</td>
                    </tr>
                    <tr>
                        <td width="13%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Project Reference</td>
                        <td width="2%"  align="left" style="font-weight:bold; font-size:10px; line-height:20px;">:</td>
                        <td width="85%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">'.$company['project_reference'].'</td>
                    </tr>
                </table>';

        $tbl3 ='<table border="0"  cellpadding="4"  width="100%">
                    <thead>
                        <tr bgcolor="#ededf0">
                            <th width="6%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                            <th width="55%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DESCRIPTION</th>
                            <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                            <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT</th>
                            <th width="12%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT PRICE($)</th>
                            <th width="13%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">TOTAL PRICE($)</th>
                        </tr>
                    </thead>';
        $subtotalValue   = 0;
        $count      = 1;
        $countCheck = 1;
        while ($row = $db->sql_fetchrow($result)) {

            if ($row['item_title']) {
                $countCheck++;
                $tbl3 = $tbl3.'<tr>
                                    <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="55%" style="font-size:10px; font-weight:bold; border-left:1px solid #000;border-right:1px solid #000;"><u>'.nl2br($row['item_title']).'</u><br/></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="13%" style="border-right:1px solid #000;"></td>
                                </tr>
                        ';
            }

            if ($row['amount'] != "") {
                $subtotal_amount = round($row['amount'], 3);
            } else if($row['unit_price'] > 0 && $row['qty'] > 0) {
                $subtotal_amount = round($row['qty'] * $row['unit_price'], 3);
            } else if ($row['unit_price'] > 0 && $row['qty'] == 0) {
                $subtotal_amount = round($row['unit_price'], 3);
            }

            $subtotal_amount_formatted = number_format($subtotal_amount, 3);

            if($row['qty'] == 0) {
                $row['qty'] = "";
            }

            if($row['unit_price'] == 0) {
                $row['unit_price'] = '';
            }

            if($subtotal_amount_formatted == "0.00") {
                $subtotal_amount_formatted = "";
            }

            $tbl3 = $tbl3.'<tr>
                                <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['qty'].'</td>
                                <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit'].'</td>
                                <td width="12%" align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit_price'].'</td>
                                <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;">'.$subtotal_amount_formatted.'</td>
                            </tr>
                    ';

            $subtotalValue += $subtotal_amount;

            if($company['gst_percentage'] > 0) {
                $gsttaxvalue    = $cpCfg['cp.gstPercentage'] ;
                $gstvalue       = $subtotalValue * $gsttaxvalue / 100;
                /* Taking two decimal values for gst amount */
                $fraction_length = strlen(substr(strrchr($gstvalue, "."), 1)); // Checking the lingth of the fraction value
                if ($fraction_length > 2) {
                    list($integer, $fraction) = explode(".", (string) $gstvalue);

                    /* Checking whether 3rd decimal point is more than or equal to 5
                       If Yes, add 1 to 2nd decimal point
                     */
                    $gstDecimalMore = substr($fraction, 2, 1);
                    $fraction = substr($fraction, 0, 2);
                    if ($gstDecimalMore >= 5) {
                        $fraction = $fraction + 1;
                    }

                    $gstvalue = $integer . "." . $fraction;
                }

                $totalvalue = $gstvalue + $subtotalValue;
            } else {
                $totalvalue = $subtotalValue;
            }

            $count++;
            $countCheck++;
        }

        $totalvalue        = $totalvalue - $company['discount'];
        $amount_in_words   = $fn->getConvertNumber($totalvalue);

        if($company['gst_percentage'] > 0) {
          $emptyRow = 7 - $countCheck;
        } else {
          $emptyRow = 8 - $countCheck;
        }

        for($i = 0; $i <= $emptyRow; $i++) {
          $tbl3 = $tbl3.'<tr>
                            <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;"></td>
                            <td width="55%" style="font-size:10px; border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                            <td width="13%" style="font-size:10px;border-right:1px solid #000;" align="right"></td>
                        </tr>
                  ';
        }

        $discountRow = '';
        if($company['discount'] > 0) {
            $discountRow = '
            <tr>
              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-right:1px solid #000;"></td>
              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; border-top:1px solid #000; font-weight:bold;">DISCOUNT</td>
              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000; border-top:1px solid #000;">'.number_format($company['discount'], 3).'</td>
            </tr>
            ';
        } else {
            $discountRow = '
            <tr>
              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-right:1px solid #000;"></td>
              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; border-top:1px solid #000; font-weight:bold;">DISCOUNT</td>
              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000; border-top:1px solid #000;">-&nbsp;&nbsp;&nbsp;</td>
            </tr>
            ';
        }

        if($company['gst_percentage'] > 0) {
            $tbl3 = $tbl3.'<tr>
                              <td colspan="2" style="font-size:10px; border-top:1px solid #000;border-right:1px solid #000;">('.$amount_in_words.')</td>
                              <td align="right" colspan="3" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;">Total</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;">'.number_format($subtotalValue, 3).'</td>
                          </tr>
                          '.$discountRow.'
                          <tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; border-top:1px solid #000; font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000; border-top:1px solid #000;">'.number_format($gstvalue, 3).'</td>
                           </tr>
                           <tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; border-top:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">Total Including GST</td>
                              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000; border-top:1px solid #000; border-bottom:1px solid #000;">'.number_format($totalvalue, 3).'</td>
                           </tr>
                        </table>';
        } else {
            $tbl3 = $tbl3.''.$discountRow.'<tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-top:1px solid #000;border-right:1px solid #000; border-top:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">Total Excluding Gst</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000; border-right:1px solid #000; border-top:1px solid #000; border-bottom:1px solid #000;">'.number_format($totalvalue, 3).'</td>
                           </tr>
                        </table>';
        }

        $tbl4 = '
        <table border="0" width="100%" cellpadding="2">
            <tr>
                <td align="left" width="60%" style="font-size:10px;line-height:8px;">'.nl2br($company['payment_terms']).'</td>
            </tr>
        </table>';

        $tbl5 = '
        <table border="0" width="100%" cellpadding="3">
            <tr>
                <td width="40%" style="border-bottom:1px dotted black; font-weight:bold; font-size:10px;">For '.$cpCfg['cp.companyName'].'<br/><br/><br/><br/><br/></td>
                <td width="30%"></td>
                <td width="30%"></td>
            </tr>
            <tr>
                <td style="font-size:10px; line-height:20px;">Authorised Signatory</td>
                <td></td>
                <td style="font-size:10px;"></td>
            </tr>
        </table>
        ';
        
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-6);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->ln(-16);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl5, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['invoice_code'] . '-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintinvoice1() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $quote_id         = $fn->getReqParam('quote_id');
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';
        $invoice_id       = $fn->getReqParam('invoice_id');
        $totalvalue       = 0;

        $SQL = "
        SELECT ini.*
                ,c.company_name
                ,o.cust_address1
                ,o.cust_address2
                ,o.cust_address_country
                ,o.cust_address_po_code
                ,c.company_id
                ,i.invoice_date
                ,ini.unit_price
                ,i.invoice_code
                ,i.invoice_type
                ,i.qty_text
                ,i.rate_text
                ,i.invoice_terms
                ,i.invoice_due_date
                ,i.notes
                ,i.discount
                ,i.title AS invoice_title
                ,i.payment_terms
                ,i.po_number
                ,co.first_name
                ,co.salutation
                ,ROUND((ini.qty * ini.unit_price), 3) AS amount
                ,(SELECT ROUND(SUM(init.qty * init.unit_price), 3)
                  FROM invoice_item init
                  WHERE init.invoice_id = ini.invoice_id) AS sub_total
        FROM invoice_item ini
        LEFT JOIN invoice i  ON (i.invoice_id  = ini.invoice_id)
        LEFT JOIN `order` o  ON (o.order_id    = i.order_id)
        LEFT JOIN company c  ON (c.company_id  = o.company_id)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        WHERE i.invoice_id = '{$invoice_id}'
        ORDER BY ini.invoice_item_id
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $today = date("d-m-Y");
        $invoice_date = $fn->getCPDate($company['invoice_date'], 'd/m/Y');

        $tbl1 = '
        <table border="0" width="100%" style="font-size:17px;">
            <tr>
                <td align="center" style="font-weight:bold;">TAX INVOICE</td>
            </tr>
        </table>
        ';

        $address2 = '';
        if($company['cust_address2']) {
            $address2 = '
            <span>'.strtoupper($company['cust_address2']).'</span><br/>
            ';
        }

        /* Find quote number only */
        $inv_date = $fn->getCPDate($company['invoice_date'], 'yy');
        $invoice_code = $cpCfg['invoiceCodePrefix'] . substr($company['invoice_code'], 4) . '/' . $inv_date;

        /* Company name prefix */
        $company_prefix = explode(' ', $company['company_name']);
        $length = strlen($company_prefix[0]);
        if ($length > 10) {
            $company_short = substr($company_prefix[0], 0, 10);
            $company_short = strtoupper($company_short);
        } else {
            $company_short = strtoupper($company_prefix[0]);
        }

        /* Span tag is used in tbl2 because when we use table or div it is giving more padding */
        $tbl2 ='<table border="0" width="100%" cellpadding="0" style="font-size:15px;">
                    <tr>
                        <td width="65%" style="line-height:20px;"><br/>
                            <span style="font-weight:bold;">'.strtoupper($company['company_name']).'</span><br/>
                            <span>'.strtoupper($company['cust_address1']).'</span><br/>
                            '.$address2.'
                            <span>'.strtoupper($company['cust_address_country']).' - '.$company['cust_address_po_code'].'</span>
                        </td>
                        <td width="35%" style="line-height:20px;">
                            <table border="0" width="100%" cellpadding="0">
                                <tr>
                                    <td style="text-align:right;">DATE&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="text-align:center;border:1px solid #000;">'.$invoice_date.'</td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;">Invoice No&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="text-align:center;border:1px solid #000;">'.$invoice_code.'</td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;">Payment Terms&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="text-align:center;border:1px solid #000;">'.$company['payment_terms'].'</td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;">PO Number&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="text-align:center;border:1px solid #000;">'.$company['po_number'].'</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table border="0" width="100%" cellpadding="0" style="font-size:15px;">
                    <tr>
                        <td style="line-height:15px;"></td>
                    </tr>
                    <tr>
                        <td style="line-height:28px;">Attn.  :&nbsp;'.$company['salutation'].'. '.strtoupper($company['first_name']).'</td>
                    </tr>
                    <tr>
                        <td>Location  :&nbsp;'.$company['invoice_title'].'</td>
                    </tr>
                </table>
                ';

        if($company['invoice_type'] == 'LOT'){
            $tbl3 ='<table border="1" nobr="true" cellpadding="2" width="100%" style="font-size:15px;">
                        <thead>
                            <tr>
                                <th width="49%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">DESCRIPTION</th>
                                <th width="13%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">QTY</th>
                                <th width="19%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">RATE</th>
                                <th width="19%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">AMOUNT (S$)</th>
                            </tr>
                        </thead>';
        }else{
            $tbl3 ='<table border="1" nobr="true" cellpadding="2" width="100%" style="font-size:15px;">
                        <thead>
                            <tr>
                                <th width="5%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">S/N</th>
                                <th width="44%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">DESCRIPTION</th>
                                <th width="13%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">QTY</th>
                                <th width="19%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">UNIT PRICE (S$)</th>
                                <th width="19%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">TOTAL AMT (S$)</th>
                            </tr>
                        </thead>';
        }

        $sub_total = 0;
        $gstvalue  = 0;
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            if($company['invoice_type'] != 'LOT'){
                if($row['item_title']) {
                    $tbl3 = $tbl3.'<tr>
                                        <td width="5%"></td>
                                        <td width="44%" style="font-weight:bold;"><u>'.$row['item_title'].'</u></td>
                                        <td width="13%"></td>
                                        <td width="19%"></td>
                                        <td width="19%"></td>
                                    </tr>
                            ';
                }

                $tbl3 = $tbl3.'<tr>
                                    <td width="5%" align="center">'.$count.'</td>
                                    <td width="44%">'.nl2br($row['description']).'</td>
                                    <td width="13%" align="center">'.$row['qty'].'</td>
                                    <td width="19%" align="center">'.$row['unit_price'].'</td>
                                    <td width="19%" align="right">'.number_format($row['amount'], 3).'</td>
                                </tr>
                        ';
            }

            $sub_total = $row['sub_total'];
            $gsttaxvalue = $cpCfg['cp.gstPercentage'] ;
            $gstvalue = $sub_total * $gsttaxvalue / 100;
            $totalvalue = $fn->getAmountFractionFormattedForGst($row['sub_total'], $gsttaxvalue);
            $count++;
        }

        $colspan = '4';
        if($company['invoice_type'] == 'LOT'){
            $tbl3 = $tbl3.'<tr>
                                    <td width="49%">'.nl2br($company['description']).'</td>
                                    <td width="13%" align="center">'.$company['qty_text'].'</td>
                                    <td width="19%" align="center">'.$company['rate_text'].'</td>
                                    <td width="19%" align="right">'.number_format($sub_total, 3).'</td>
                                </tr>
                        ';

            $colspan = '3';
        }
        $amount_in_words = $fn->getConvertNumber($totalvalue);
        $tbl3 = $tbl3.'<tr>
                          <td align="right" colspan="'.$colspan.'" style="font-weight:bold;">Sub Total</td>
                          <td align="right" style="font-weight:bold;">'.number_format($sub_total, 3).'</td>
                      </tr>
                      <tr>
                          <td colspan="'.$colspan.'" align="right" style="font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                          <td align="right" style="font-weight:bold;">'.number_format($gstvalue, 3).'</td>
                       </tr>
                       <tr>
                          <td colspan="'.$colspan.'" align="right" style="font-weight:bold;">Net Total</td>
                          <td align="right" style="font-weight:bold;">'.number_format($totalvalue, 3).'</td>
                       </tr>
                    </table>
                    <table border="0" width="100%" style="font-size:15px;text-align:center;">
                        <tr>
                            <td style="line-height:20px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>TOTAL = <span style="font-weight:bold;font-style:italic;"><i>'.strtoupper($amount_in_words).'</i></span></td>
                        </tr>
                    </table>';

        //$pdf->SetFont($andalus, 'BI');
        $tbl4 = '
        <table border="0" width="100%" style="font-size:15px;">
            <tr>
                <td style="height:10px;"></td>
            </tr>
            <tr>
                <td style="line-height:40px;"><u>Terms and Conditions</u>:</td>
            </tr>
            <tr>
                <td style="line-height:23px;">'.nl2br($company['invoice_terms']).'</td>
            </tr>
            <tr>
                <td style="height:10px;"></td>
            </tr>
            <tr>
                <td style="text-align:center;">"Thank you for your business support"</td>
            </tr>
            <tr>
                <td style="height:15px;"></td>
            </tr>
            <tr>
                <td>Yours Truly</td>
            </tr>
            <tr>
                <td style="line-height:20px;">'.$cpCfg['cp.companyProjectManagerName'].'</td>
            </tr>
            <tr>
                <td style="height:10px;"></td>
            </tr>
            <tr>
                <td>Project Manager</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        //$pdf->writeHTML($tbl4, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['invoice_code'] . '-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintReceipt() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $site_id = $fn->getSessionParam('cp_site_id');
        if ($site_id == 1) {
    // Kuwait
    $currency = 'KWD';
    $decimalPlaces = 3;
} elseif ($site_id == 2) {
    // Saudi Arabia
    $currency = 'SAR';
    $decimalPlaces = 2;
} else {
    // Default
    $currency = 'KWD';
    $decimalPlaces = 3;
}
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $searchVar = Zend_Registry::get('searchVar');
        $media = Zend_Registry::get('media');
        $cpPaths = Zend_Registry::get('cpPaths');
        $dbUtil = Zend_Registry::get('dbUtil');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);
        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootinvoice.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Green City Scape');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();
        /*
        This fucntions requires
        1.total invoice amount for thie receipt
        2.Amount already paid for this invoice
        3. Amount Paid now
        4. Balance to be calculated.
        */

        $receipt_code = $fn->getReqParam('receipt_code');
        $order_id = $fn->getReqParam('order_id');

        $SQL = "
        SELECT c.company_name
              ,o.cust_address1
              ,o.cust_address2
              ,o.cust_address_country
              ,o.cust_address_po_code
              ,i.creation_date
              ,i.invoice_id AS invoice_id_main
              ,i.invoice_code
              ,i.invoice_amount
              ,i.invoice_date
              ,r.receipt_id
              ,r.amount AS receipt_amount
              ,r.receipt_code
              ,r.mode_of_payment
              ,r.cheque_no
              ,r.bank_name
              ,r.remarks
              ,r.date AS receipt_date
        FROM receipt r
        LEFT JOIN invoice_receipt_history irh ON (r.receipt_id = irh.receipt_id)
        LEFT JOIN invoice i ON (i.invoice_id = irh.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        WHERE r.receipt_code = '{$receipt_code}'
          AND i.order_id = {$order_id}
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $rowRec  = $db->sql_fetchrow($result);

        $today = date("Y-m-d");
        if ($numRows == 0){
            $pdf->SetXY(30,30);
            $pdf->Cell(50, 20, "Please set the values for your Order and print the PDF");
            $pdf->Output();
            return;
        }

        //============================================================================= //
        $tbl1 = '
        <table border="0" width="100%">
            <tr>
                <td align="center" style="font-size:15px; font-weight:bold;">RECEIPT</td>
            </tr>
        </table>
        ';

        $receipt_date = $fn->getCPDate($rowRec['receipt_date'], 'd-m-Y');
        $address_street = "";
        if ($rowRec['cust_address2']) {
            $address_street = '
            <tr>
                <td style="font-size:12px;">'.strtoupper($rowRec['cust_address2']).'</td>
                <td colspan="3"></td>
            </tr>
            ';
        }
        $tbl2 ='
        <table border="0" width="100%" cellpadding="">
            <tr>
                <td width="6%" style="font-size:12px; font-weight:bold;"></td>
                <td width="54%"></td>
                <td width="26%" align="right" style="font-size:12px; font-weight:bold;">RECEIPT NO: </td>
                <td width="14%" align="right" style="font-size:12px; font-weight:bold;">'.$rowRec['receipt_code'].'</td>
            </tr>
            <tr>
                <td width="54%" style="font-size:12px; font-weight:bold;"></td>
                <td width="6%"></td>
                <td width="26%" align="right" style="font-size:12px; font-weight:bold;">DATE &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: </td>
                <td width="14%" align="right" style="font-size:12px; font-weight:bold;">'.$receipt_date.'</td>
            </tr>
           
        </table>
        ';

        $tbl3 ='
        <table border="1" cellpadding="6" width="100%">
            <thead>
                <tr bgcolor="#DDE4FF">
                    <th width="50%" align="center" style="font-size:12px; font-weight:bold;">DESCRIPTION</th>
                    <th width="50%" align="right" style="font-size:12px; font-weight:bold;"></th>
                </tr>
            </thead>
            ';

            $count = 0;
            $previous_paid_amount = 0;
            $total_amount = 0;
            $invoice_code = '';
            while ($row = $db->sql_fetchrow($result2)) {
                $count++;
                //===================================MAIN TABLE============================= //
               /*This sql used to find the previous amount paid for the invoice */
                $sqlPreviousPayment = "
                SELECT SUM(irhist.amount) AS total_amount_paid
                FROM invoice_receipt_history irhist
                LEFT JOIN receipt r ON (irhist.receipt_id = r.receipt_id)
                WHERE irhist.invoice_id = {$row['invoice_id_main']}
                  AND irhist.receipt_id != {$row['receipt_id']}
                  AND r.receipt_status != 'Cancelled'
                ";
                $resultPreviousPayment = $db->sql_query($sqlPreviousPayment);
                $rowPreviousPayment    = $db->sql_fetchrow($resultPreviousPayment);
                $previous_paid_amount += $rowPreviousPayment['total_amount_paid'];

                $sqlInvoiceAmount = "
                SELECT i.invoice_amount
                      ,i.gst_percentage
                FROM invoice i
                WHERE i.invoice_id = {$row['invoice_id_main']}
                ";
                $resultInvAmount = $db->sql_query($sqlInvoiceAmount);
                $rowInvoiceAmount= $db->sql_fetchrow($resultInvAmount);

                if ($rowInvoiceAmount['gst_percentage'] > 0) {
                    $total_amount += $fn->getAmountFractionFormattedForGst($rowInvoiceAmount['invoice_amount'], $rowInvoiceAmount['gst_percentage']);
                } else {
                    $total_amount += $rowInvoiceAmount['invoice_amount'];
                }

                if ($numRows == $count) {
                    $inv_date = $fn->getCPDate($row['invoice_date'], 'ym/');
                    $invoice_code .= $inv_date . substr($row['invoice_code'], 3);
                } else {
                    $inv_date = $fn->getCPDate($row['invoice_date'], 'ym/');
                    $invoice_code .= $inv_date . substr($row['invoice_code'], 3) . ', ';
                }
            }

            $balance_due = $total_amount - $previous_paid_amount - $rowRec['receipt_amount'];

            if ($balance_due > 0) {
                $balance_due = $balance_due;
            } else {
                $balance_due = 0.00;
            }

            $amount_in_words   = $fn->getConvertNumber($rowRec['receipt_amount']);


            $tbl3 = $tbl3.'
            <tr>
                <td width="50%" style="font-size:12px;">Received From Mr. / Ms.</td>
                <td width="50%" align="right" style="font-size:12px; font-weight:bold;">'.$rowRec['company_name'].'</td>
            </tr>
            <tr>
                <td width="50%" style="font-size:12px;">The Sum of '.$currency.'</td>
                <td width="50%" align="right" style="font-size:12px; font-weight:bold;">'.$amount_in_words.'</td>
            </tr>
            <tr>
                <td width="50%" style="font-size:12px;">On Account / Description</td>
                <td width="50%" align="right" style="font-size:12px; font-weight:bold;">'.$rowRec['invoice_code'].'</td>
            </tr>
            <tr>
                <td width="50%" style="font-size:12px;">Cash / Cheque</td>
                <td width="50%" align="right" style="font-size:12px; font-weight:bold;">'.$rowRec['mode_of_payment'].'</td>
            </tr>
            <tr>
                <td width="50%" style="font-size:12px;">Cheque No.</td>
                <td width="50%" align="right" style="font-size:12px; font-weight:bold;">'.$rowRec['cheque_no'].'</td>
            </tr>
            <tr>
                <td width="50%" style="font-size:12px;">Bank</td>
                <td width="50%" align="right" style="font-size:12px; font-weight:bold;">'.$rowRec['bank_name'].'</td>
            </tr>
            <tr>
            <td width="50%" style="font-size:12px;">'.$currency.'</td>
            <td width="50%" align="right" style="font-size:12px; font-weight:bold;">'.number_format($rowRec['receipt_amount'], $decimalPlaces).'</td>
        </tr>
        </table>
        ';

        $tbl4 = '
        <table border="0" width="100%">
            <tr>
                <td width="25%" border="0" align="left"  style="font-size:12px;  font-weight:bold;">Accountant:</td>
          
                <td width="25%" align="left" style="font-size:12px;"></td>
           
                <td width="25%" border="0" align="right"  style="font-size:12px;  font-weight:bold;">Received By:</td>
           
                <td width="25%" align="left" style="font-size:12px;"></td>
            </tr>
        </table>';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');

        $download_title =  $rowRec['receipt_code'] . '-A Team'.'-Receipt.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getRightPanel($row){
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $db = Zend_Registry::get('db');

        $printText = "";
        $actionButtons = "";
        $summaryAction = "";
        $captainCopy = "";

        $links ='';
        if ($cpCfg['m.enggCrm.order.showAttachment'] == 1) {
            $links .= $media->getRightPanelMediaDisplay('Attachments', 'enggCrm_order', 'attachment', $row);
        }

        $printTextButton ='';

        if ($cpCfg['m.enggCrm.order.showReceiptButton']){
            $actionButtons .="
            <div class='float_right button mb5'>
                <a href='#' class='generateReceipt' order_id={$row['order_id']}>CREATE RECEIPT</a>
            </div>
            ";
        }

        if ($cpCfg['m.enggCrm.order.showInvoiceButton']){
            $actionButtons .="
            <div class='float_right button mb5'>
                <a href='#' class='generateInvoiceorder' order_id={$row['order_id']}>CREATE INVOICE</a>
            </div>
          
            ";
        }

        if($row['record_type'] == 'POS') {
            $urlPrint  = "index.php?_topRm=pos&module=enggCrm_pos&_spAction=printBill&printOnly=1&orderNo={$row['order_id']}&showHTML=0";
            $actionButtons .="
            <div class='float_right button mb5'>
                <a href='{$urlPrint}' target='_blank'>PRINT INVOICE</a>
            </div>
            ";
        }

        $urlPrintDeliveryOrder  = "index.php?_topRm=order&module=enggCrm_order&_spAction=PrintDeliveryOrder&order_id={$row['order_id']}&showHTML=0";
        $print ="
        <div class='float_right mb5'>
        	<a href='#' id='cancelBill' class='btn btn-danger' order_id={$row['order_id']}>CANCEL FINANCE</a>
        </div>
        <div class='floatbox actionBtnsDetail'>
            <div class='orderbtnbackground floatbox'>
                {$actionButtons}
            </div>
        </div>
        <div class='floatbox actionBtnsDetail orderbtnbackground'>
            <!--<div class='float_right button mb5 mt5'>
                <a href='{$urlPrintDeliveryOrder}' target='_blank' class='printLink' order_id='{$row['order_id']}'>DELIVERY ORDER</a>
            </div>-->
            <div class='float_right button mb5 mt5'>
                <a href='#' class='generateCreditNote' order_id={$row['order_id']}>CREDIT NOTE</a>
            </div>
        </div>
        ";

        if ($cpCfg['m.enggCrm.order.showInvoicePortalDisplay']){
            $links .= $this->getInvoicePortalDisplay($row['order_id']);
        }

        if ($cpCfg['m.enggCrm.order.showReceiptPortalDisplay']){
            //$links .= $displayLinkData->getLinkPortalMain('enggCrm_order', 'enggCrm_receiptLink', 'Receipt Linked', $row);
            $links .= "
            <div class='mt10'></div>
            {$this->getReceiptPortalDisplay($row['order_id'])}
            ";
        }

        $links .= $this->getCreditNotePortalDisplay($row);

        $summaryTableOrder = '' ;
        if($row['record_type'] != 'POS') {
            $summaryTableOrder = $this->getSummaryInOrder($row);
        }

        $orderItem = '';
        if ($cpCfg['m.enggCrm.order.showOrderItemDisplay']){
            if($row['record_type'] == 'Manpower Supply'){
                $orderItem = $this->getOrderItemRightpanel($row);
                //$orderItem .= $displayLinkData->getLinkPortalMain('enggCrm_order', 'enggCrm_orderItemLink', 'Order Items', $row);
            }
            else{
                $orderItem = $displayLinkData->getLinkPortalMain('enggCrm_order', 'enggCrm_orderItemLink', 'Order Items', $row);
            }
        }

        $text = "
        {$print}
        {$summaryTableOrder}
       <!-- {$orderItem}-->
        {$links}
        ";

        return $text;
    }

    /**
     *
     */
    function getOrderItemRightpanel($row){
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        $SQLOrderItems = "
        SELECT *
        FROM order_item
        WHERE order_id = {$row['order_id']}
        ORDER BY order_item_id ASC
        ";
        $resultOrderItems = $db->sql_query($SQLOrderItems);
        $numOrderItems    = $db->sql_numrows($resultOrderItems);

        $orderItemRows = '';
        if($numOrderItems > 0){
            $count = 1;
            while($rowOrderItems = $db->sql_fetchrow($resultOrderItems)){

                $total_amount    = $rowOrderItems['qty'] * $rowOrderItems['unit_price'];
                $total_ot_amount = $rowOrderItems['employee_ot_hours'] * $rowOrderItems['ot_hourly_rate'];
                $total_ph_amount = $rowOrderItems['employee_ph_hours'] * $rowOrderItems['ph_hourly_rate'];
                $total_admin_transport_amount = $rowOrderItems['admin_charges'] + $rowOrderItems['transport_charges'];

                $overallTotalAmount = $total_amount + $total_ot_amount + $total_ph_amount + $total_admin_transport_amount;
                $overallTotalAmount = number_format($overallTotalAmount, 3);

                $SQLEmployee = "
                SELECT first_name
                FROM employee
                WHERE employee_id = {$rowOrderItems['record_id']}
                ";
                $resultEmployee = $db->sql_query($SQLEmployee);
                $rowEmployee    = $db->sql_fetchrow($resultEmployee);

                $total_amount_formatted = number_format($total_amount, 3);
                $total_ot_amoumt_formatted = number_format($total_ot_amount, 3);
                $total_ph_amoumt_formatted = number_format($total_ph_amount, 3);
                $total_admin_transport_amount_formatted = number_format($total_admin_transport_amount, 3);

                $orderItemRows .= "
                <tr>
                    <td width='5'>{$count}</td>
                    <td class='description'>{$rowOrderItems['item_title']}</td>
                    <td>{$rowEmployee['first_name']}</td>
                    <td class='quantity txtRight'>{$total_amount_formatted}</td>
                    <td class='unit-price txtRight'>{$total_ot_amoumt_formatted}</td>
                    <td class='unit-price txtRight'>{$total_ph_amoumt_formatted}</td>
                    <td class='unit-price txtRight'>{$total_admin_transport_amount_formatted}</td>
                    <td class='total txtRight'>{$overallTotalAmount}</td>
                </tr>
                ";
                $count++;
            }

        }else{
            $orderItemRows = "
                <tr>
                    <td colspan='5' class='txtCenter No_Records_linked'>No Records Linked</td>
                </tr>
            ";
        }

        $rows = "
        <div id='enggCrm_order#enggCrm_orderItemLink' class='linkPortalWrapper enggCrm_order__enggCrm_orderItemLink'>
            <div class='header' expanded='1'>
                <div class='floatbox'>
                    <div class='float_left'>
                        Order Items
                    </div>

                    <div class='toggle'>&nbsp;</div>
                    <div class='txtRight'><span class='count'>({$numOrderItems})</span></div>

                </div>
            </div>
            <div class='linkPortalDataWrapper'>
                <form>
                    <table class='grid'>
                        <thead>
                            <tr>
                                <th width='5'>#</th>
                                <th class='description'>Month</th>
                                <th>Employee Name</th>
                                <th class='quantity txtRight'>Normal Amount</th>
                                <th class='unit-price txtRight'>OT Amount</th>
                                <th class='unit-price txtRight'>Sunday/PH Amount</th>
                                <th class='unit-price txtRight'>Admin & Transport</th>
                                <th class='total txtRight'>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$orderItemRows}
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
        ";

        $text = "
        {$rows}
        ";

        return $text;
    }


    /**
     */
    function getInvoicePortalDisplayDetail($order_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $rows          = "";
        $rowsPvt       = "";
        $links         = "";
        $sqlAppend     = "";
        $rowsCancelled = "";

        $status = $fn->getReqParam('status');

        if ($status) {
            $sqlAppend .= "AND i.status = '{$status}'";
        }

        $_SESSION['selectedInvoiceIds'] = array();
        $exp = array('isEditable' => 1);

        $SQL = "
        SELECT i.*
            ,(
            SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
            FROM receipt r, invoice_receipt_history invrecpt
            WHERE r.receipt_id = invrecpt.receipt_id
            AND i.invoice_id = invrecpt.invoice_id
            ) AS receipt_codes_history
            {$sqlAppend}
        FROM invoice i
        WHERE i.order_id = {$order_id}
        ORDER BY i.invoice_id DESC
        ";
        $result   = $db->sql_query($SQL);

        $total = 0;
        while ($rowInvoice = $db->sql_fetchrow($result)) {
            $rowORder = $fn->getRecordRowByID('order', 'order_id', $order_id);

            //$urlPrint  = "index.php?_topRm=pos&module=enggCrm_pos&_spAction=printBill&invoice_code={$rowInvoice['invoice_code']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            if($rowORder['record_type'] == 'Manpower Supply'){
                $PrintFunctionName = 'PrintinvoiceManpowerNormal';
                if($rowInvoice['invoice_type'] == 'LOT'){
                    $PrintFunctionName = "PrintinvoiceManpowerLot";
                }
                
                $urlPrintinvoice  = "index.php?_topRm=finance&module=enggCrm_order&_spAction={$PrintFunctionName}&invoice_id={$rowInvoice['invoice_id']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            }else{
                $urlPrintinvoice  = "index.php?_topRm=finance&module=enggCrm_order&_spAction=Printinvoice&invoice_id={$rowInvoice['invoice_id']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            }

            $editInvoiceLink = '';
            $cancelInvoiceLink = '';
            if ($rowInvoice['status'] != 'Cancelled'){
                $editURL = "index.php?_topRm=finance&module=enggCrm_order&_spAction=editInvoiceForm&showHTML=0&invoice_id={$rowInvoice['invoice_id']}&order_id={$order_id}";
                $editInvoiceLink = "<a href='{$editURL}' order_id='{$rowInvoice['order_id']}'  class='editInvoicess'><u>Edit</u></a>";

                $cancelInvoiceLink = "<a href='#' order_id='{$rowInvoice['order_id']}' class='cancelInvoicess' invoice_code='{$rowInvoice['invoice_code']}' invoice_id='{$rowInvoice['invoice_id']}'><u>Cancel Invoice</u></a>";
            }

            $invoice_date = $fn->getCPDate($rowInvoice['invoice_date'], 'd-m-Y');

            // if ($rowInvoice['gst_percentage'] > 0) {
            //     $total = $fn->getAmountFractionFormattedForGst($rowInvoice['invoice_amount'], $rowInvoice['gst_percentage']);
            // } else {
            //     $total = $rowInvoice['invoice_amount'];
            // }
            $total = $rowInvoice['invoice_amount'];
            $totalvalueRounded = number_format((float)($total - $rowInvoice['discount']), 3, '.', '');

            $add_class = '';
            if ($rowInvoice['status'] == 'Cancelled') {
                $add_class = 'highlightCell';
            }

            $inv_date = $fn->getCPDate($rowInvoice['invoice_date'], 'yy');
            $invoice_code = $rowInvoice['invoice_code'];
            
            if ($rowInvoice['status'] != 'Cancelled'){
                $rows .= "
                <tr>
                    <td>{$invoice_code}</td>
                    <!--<td>{$rowInvoice['invoice_code']}</td>-->
                    <td class='{$add_class}'>{$rowInvoice['status']}</td>
                    <td>{$invoice_date}</td>
                    <td align='right'>{$totalvalueRounded}</td>
                    <td><a href='{$urlPrintinvoice}' target='_blank'><u>Print Invoice</u></a></td>
                    <td>{$editInvoiceLink}</td>
                    <td>{$cancelInvoiceLink}</td>
                </tr>
                ";
            }
        }

        
        $SQLCancelledInvoice = "
        SELECT i.*
            ,(
            SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
            FROM receipt r, invoice_receipt_history invrecpt
            WHERE r.receipt_id = invrecpt.receipt_id
            AND i.invoice_id = invrecpt.invoice_id
            ) AS receipt_codes_history
            {$sqlAppend}
        FROM invoice i
        WHERE i.order_id = {$order_id}
        AND i.status = 'Cancelled'
        ORDER BY i.invoice_sent_out DESC
        ";
        $resultCancelledInvoice   = $db->sql_query($SQLCancelledInvoice);

        $total = 0;
        while ($rowCancelledInvoice = $db->sql_fetchrow($resultCancelledInvoice)) {
            $rowORder = $fn->getRecordRowByID('order', 'order_id', $order_id);

            if($rowORder['record_type'] == 'Manpower Supply'){
                $PrintFunctionName = 'PrintinvoiceManpowerNormal';
                if($rowCancelledInvoice['invoice_type'] == 'LOT'){
                    $PrintFunctionName = "PrintinvoiceManpowerLot";
                }
                
                $urlPrintinvoice  = "index.php?_topRm=finance&module=enggCrm_order&_spAction={$PrintFunctionName}&invoice_code={$rowCancelledInvoice['invoice_code']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            }else{
                $urlPrintinvoice  = "index.php?_topRm=finance&module=enggCrm_order&_spAction=Printinvoice&invoice_code={$rowCancelledInvoice['invoice_code']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            }

            $editInvoiceLink = '';
            $cancelInvoiceLink = '';
            if ($rowCancelledInvoice['status'] != 'Cancelled'){
                $editURL = "index.php?_topRm=finance&module=enggCrm_order&_spAction=editInvoiceForm&showHTML=0&invoice_id={$rowCancelledInvoice['invoice_id']}&order_id={$order_id}";
                $editInvoiceLink = "<a href='{$editURL}' order_id='{$rowInvoice['order_id']}' class='editInvoicess'><u>Edit</u></a>";

                $cancelInvoiceLink = "<a href='#' class='cancelInvoicess' invoice_code='{$rowCancelledInvoice['invoice_code']}' invoice_id='{$rowCancelledInvoice['invoice_id']}'><u>Cancel Invoice</u></a>";
            }

            $invoice_date = $fn->getCPDate($rowCancelledInvoice['invoice_date'], 'd-m-Y');

            // if ($rowCancelledInvoice['gst_percentage'] > 0) {
            //     $total = $fn->getAmountFractionFormattedForGst($rowCancelledInvoice['invoice_amount'], $rowCancelledInvoice['gst_percentage']);
            // } else {
            //     $total = $rowCancelledInvoice['invoice_amount'];
            // }
            $total = $rowCancelledInvoice['invoice_amount'];
            $totalvalueRounded = number_format((float)($total - $rowInvoice['discount']), 3, '.', '');

            $add_class = '';
            if ($rowCancelledInvoice['status'] == 'Cancelled') {
                $add_class = 'highlightCell';
            }

            $inv_date = $fn->getCPDate($rowCancelledInvoice['invoice_date'], 'ym/');

            $inv_year = $fn->getCPDate($rowCancelledInvoice['invoice_date'], 'yy');
            $invoice_code = $rowCancelledInvoice['invoice_code'] . '/' . $inv_year;

            $rowsCancelled .= "
            <tr class='cancelledInvoiceTableOrder'>
                <td>{$invoice_code}</td>
                <td class='{$add_class}'>{$rowCancelledInvoice['status']}</td>
                <td>{$invoice_date}</td>
                <td align='right'>{$totalvalueRounded}</td>
                <td><a href='{$urlPrintinvoice}' target='_blank'><u>Print Invoice</u></a></td>
                <td>{$editInvoiceLink}</td>
                <td>{$cancelInvoiceLink}</td>
            </tr>
            ";
        }

        $rowsCancelledHeader = "
        <tr style='background-color:#EAEAE8;' class='cancelledInvoiceTableOrder'>
            <th>Invoice Code</th>
            <th>Status</th>
            <th>Invoice Date</th>
            <th class='txtRight'>Amount</th>
            <th>Print</th>
            <th>Edit</th>
            <th>Cancel</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            <tr style='background-color:#EAEAE8;'>
                <th>Invoice Code</th>
                <th>Status</th>
                <th>Invoice Date</th>
                <th class='txtRight'>Amount</th>
                <th>Print</th>
                <th>Edit</th>
                <th>Cancel</th>
            </tr>
            {$rows}
            <tr>
                <th colspan='7' class='txtRight'>
                    <a class='showHideCancelledInvoice1 mr10'>(+) Click to Hide Cancelled Invoice(s)</a>
                </th>
            </tr>
            {$rowsCancelledHeader}
            {$rowsCancelled}
            {$rowsPvt}
        </table>
        ";

        return $text;
    }

     /**
     */
    function getInvoicePortalDisplayDetail1($order_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $rows          = "";
        $rowsPvt       = "";
        $links         = "";
        $sqlAppend     = "";
        $rowsCancelled = "";

        $status = $fn->getReqParam('status');

        if ($status) {
            $sqlAppend .= "AND i.status = '{$status}'";
        }

        $_SESSION['selectedInvoiceIds'] = array();
        $exp = array('isEditable' => 1);

        $SQL = "
        SELECT i.*
            ,(
            SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
            FROM receipt r, invoice_receipt_history invrecpt
            WHERE r.receipt_id = invrecpt.receipt_id
            AND i.invoice_id = invrecpt.invoice_id
            ) AS receipt_codes_history
            {$sqlAppend}
        FROM invoice i
        WHERE i.order_id = {$order_id}
        ORDER BY i.invoice_id DESC
        ";
        $result   = $db->sql_query($SQL);

        $total = 0;
        while ($rowInvoice = $db->sql_fetchrow($result)) {
            $rowORder = $fn->getRecordRowByID('order', 'order_id', $order_id);

            //$urlPrint  = "index.php?_topRm=pos&module=enggCrm_pos&_spAction=printBill&invoice_code={$rowInvoice['invoice_code']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            if($rowORder['record_type'] == 'Manpower Supply'){
                $PrintFunctionName = 'PrintinvoiceManpowerNormal';
                if($rowInvoice['invoice_type'] == 'LOT'){
                    $PrintFunctionName = "PrintinvoiceManpowerLot";
                }
                
                $urlPrintinvoice  = "index.php?_topRm=finance&module=enggCrm_order&_spAction={$PrintFunctionName}&invoice_id={$rowInvoice['invoice_id']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            }else{
                $urlPrintinvoice  = "index.php?_topRm=finance&module=enggCrm_order&_spAction=Printinvoice&invoice_id={$rowInvoice['invoice_id']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            }

            $editInvoiceLink = '';
            $cancelInvoiceLink = '';
            if ($rowInvoice['status'] != 'Cancelled'){
                $editURL = "index.php?_topRm=finance&module=enggCrm_order&_spAction=editInvoiceForm1&showHTML=0&invoice_id={$rowInvoice['invoice_id']}&order_id={$order_id}";
                $editInvoiceLink = "<a href='{$editURL}' order_id='{$rowInvoice['order_id']}'  class='editInvoice1'><u>Edit</u></a>";

                $cancelInvoiceLink = "<a href='#' order_id='{$rowInvoice['order_id']}' class='cancelInvoice1' invoice_code='{$rowInvoice['invoice_code']}' invoice_id='{$rowInvoice['invoice_id']}'><u>Cancel Invoice</u></a>";
            }

            $invoice_date = $fn->getCPDate($rowInvoice['invoice_date'], 'd-m-Y');

            // if ($rowInvoice['gst_percentage'] > 0) {
            //     $total = $fn->getAmountFractionFormattedForGst($rowInvoice['invoice_amount'], $rowInvoice['gst_percentage']);
            // } else {
            //     $total = $rowInvoice['invoice_amount'];
            // }
            $total = $rowInvoice['invoice_amount'];
            $totalvalueRounded = number_format((float)($total - $rowInvoice['discount']), 3, '.', '');

            $add_class = '';
            if ($rowInvoice['status'] == 'Cancelled') {
                $add_class = 'highlightCell';
            }

            $inv_date = $fn->getCPDate($rowInvoice['invoice_date'], 'yy');
            $invoice_code = $rowInvoice['invoice_code'];
            
            if ($rowInvoice['status'] != 'Cancelled'){
                $rows .= "
                <tr>
                    <td>{$invoice_code}</td>
                    <!--<td>{$rowInvoice['invoice_code']}</td>-->
                    <td class='{$add_class}'>{$rowInvoice['status']}</td>
                    <td>{$invoice_date}</td>
                    <td align='right'>{$totalvalueRounded}</td>
                    <td><a href='{$urlPrintinvoice}' target='_blank'><u>Print Invoice</u></a></td>
                    <td>{$editInvoiceLink}</td>
                    <td>{$cancelInvoiceLink}</td>
                </tr>
                ";
            }
        }

        
        $SQLCancelledInvoice = "
        SELECT i.*
            ,(
            SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
            FROM receipt r, invoice_receipt_history invrecpt
            WHERE r.receipt_id = invrecpt.receipt_id
            AND i.invoice_id = invrecpt.invoice_id
            ) AS receipt_codes_history
            {$sqlAppend}
        FROM invoice i
        WHERE i.order_id = {$order_id}
        AND i.status = 'Cancelled'
        ORDER BY i.invoice_sent_out DESC
        ";
        $resultCancelledInvoice   = $db->sql_query($SQLCancelledInvoice);

        $total = 0;
        while ($rowCancelledInvoice = $db->sql_fetchrow($resultCancelledInvoice)) {
            $rowORder = $fn->getRecordRowByID('order', 'order_id', $order_id);

            if($rowORder['record_type'] == 'Manpower Supply'){
                $PrintFunctionName = 'PrintinvoiceManpowerNormal';
                if($rowCancelledInvoice['invoice_type'] == 'LOT'){
                    $PrintFunctionName = "PrintinvoiceManpowerLot";
                }
                
                $urlPrintinvoice  = "index.php?_topRm=finance&module=enggCrm_order&_spAction={$PrintFunctionName}&invoice_code={$rowCancelledInvoice['invoice_code']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            }else{
                $urlPrintinvoice  = "index.php?_topRm=finance&module=enggCrm_order&_spAction=Printinvoice&invoice_code={$rowCancelledInvoice['invoice_code']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            }

            $editInvoiceLink = '';
            $cancelInvoiceLink = '';
            if ($rowCancelledInvoice['status'] != 'Cancelled'){
                $editURL = "index.php?_topRm=finance&module=enggCrm_order&_spAction=editInvoiceForm1&showHTML=0&invoice_id={$rowCancelledInvoice['invoice_id']}&order_id={$order_id}";
                $editInvoiceLink = "<a href='{$editURL}' order_id='{$rowInvoice['order_id']}' class='editInvoice1'><u>Edit</u></a>";

                $cancelInvoiceLink = "<a href='#' class='cancelInvoice1' invoice_code='{$rowCancelledInvoice['invoice_code']}' invoice_id='{$rowCancelledInvoice['invoice_id']}'><u>Cancel Invoice</u></a>";
            }

            $invoice_date = $fn->getCPDate($rowCancelledInvoice['invoice_date'], 'd-m-Y');

            // if ($rowCancelledInvoice['gst_percentage'] > 0) {
            //     $total = $fn->getAmountFractionFormattedForGst($rowCancelledInvoice['invoice_amount'], $rowCancelledInvoice['gst_percentage']);
            // } else {
            //     $total = $rowCancelledInvoice['invoice_amount'];
            // }
            $total = $rowCancelledInvoice['invoice_amount'];
            $totalvalueRounded = number_format((float)($total - $rowInvoice['discount']), 3, '.', '');

            $add_class = '';
            if ($rowCancelledInvoice['status'] == 'Cancelled') {
                $add_class = 'highlightCell';
            }

            $inv_date = $fn->getCPDate($rowCancelledInvoice['invoice_date'], 'ym/');

            $inv_year = $fn->getCPDate($rowCancelledInvoice['invoice_date'], 'yy');
            $invoice_code = $rowCancelledInvoice['invoice_code'] . '/' . $inv_year;

            $rowsCancelled .= "
            <tr class='cancelledInvoiceTableOrder'>
                <td>{$invoice_code}</td>
                <td class='{$add_class}'>{$rowCancelledInvoice['status']}</td>
                <td>{$invoice_date}</td>
                <td align='right'>{$totalvalueRounded}</td>
                <td><a href='{$urlPrintinvoice}' target='_blank'><u>Print Invoice</u></a></td>
                <td>{$editInvoiceLink}</td>
                <td>{$cancelInvoiceLink}</td>
            </tr>
            ";
        }

        $rowsCancelledHeader = "
        <tr style='background-color:#EAEAE8;' class='cancelledInvoiceTableOrder'>
            <th>Invoice Code</th>
            <th>Status</th>
            <th>Invoice Date</th>
            <th class='txtRight'>Amount</th>
            <th>Print</th>
            <th>Edit</th>
            <th>Cancel</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            <tr style='background-color:#EAEAE8;'>
                <th>Invoice Code</th>
                <th>Status</th>
                <th>Invoice Date</th>
                <th class='txtRight'>Amount</th>
                <th>Print</th>
                <th>Edit</th>
                <th>Cancel</th>
            </tr>
            {$rows}
            <tr>
                <th colspan='7' class='txtRight'>
                    <a class='showHideCancelledInvoice mr10'>(+) Click to View Cancelled Invoice(s)</a>
                </th>
            </tr>
            {$rowsCancelledHeader}
            {$rowsCancelled}
            {$rowsPvt}
        </table>
        ";

        return $text;
    }

  /**
     */
    function getInvoicePortalDisplayDetail2($order_id){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dbUtil = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $rows          = "";
        $rowsPvt       = "";
        $links         = "";
        $sqlAppend     = "";
        $rowsCancelled = "";

        $status = $fn->getReqParam('status');

        if ($status) {
            $sqlAppend .= "AND i.status = '{$status}'";
        }

        $_SESSION['selectedInvoiceIds'] = array();
        $exp = array('isEditable' => 1);

        $SQL = "
        SELECT i.*
            ,(
            SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
            FROM receipt r, invoice_receipt_history invrecpt
            WHERE r.receipt_id = invrecpt.receipt_id
            AND i.invoice_id = invrecpt.invoice_id
            ) AS receipt_codes_history
            {$sqlAppend}
        FROM invoice i
        WHERE i.order_id = {$order_id}
        ORDER BY i.invoice_id DESC
        ";
        $result   = $db->sql_query($SQL);

        $total = 0;
        while ($rowInvoice = $db->sql_fetchrow($result)) {
            $rowORder = $fn->getRecordRowByID('order', 'order_id', $order_id);

            //$urlPrint  = "index.php?_topRm=pos&module=enggCrm_pos&_spAction=printBill&invoice_code={$rowInvoice['invoice_code']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            if($rowORder['record_type'] == 'Manpower Supply'){
                $PrintFunctionName = 'PrintinvoiceManpowerNormal';
                if($rowInvoice['invoice_type'] == 'LOT'){
                    $PrintFunctionName = "PrintinvoiceManpowerLot";
                }
                
                $urlPrintinvoice  = "index.php?_topRm=finance&module=enggCrm_order&_spAction={$PrintFunctionName}&invoice_id={$rowInvoice['invoice_id']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            }else{
                $urlPrintinvoice  = "index.php?_topRm=finance&module=enggCrm_order&_spAction=Printinvoice&invoice_id={$rowInvoice['invoice_id']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            }

            $editInvoiceLink = '';
            $cancelInvoiceLink = '';
            if ($rowInvoice['status'] != 'Cancelled'){
                $editURL = "index.php?_topRm=finance&module=enggCrm_order&_spAction=editInvoiceForm2&showHTML=0&invoice_id={$rowInvoice['invoice_id']}&order_id={$order_id}";
                $editInvoiceLink = "<a href='{$editURL}' order_id='{$rowInvoice['order_id']}'  class='editInvoice2'><u>Edit</u></a>";

                $cancelInvoiceLink = "<a href='#' order_id='{$rowInvoice['order_id']}' class='cancelInvoice2' invoice_code='{$rowInvoice['invoice_code']}' invoice_id='{$rowInvoice['invoice_id']}'><u>Cancel Invoice</u></a>";
            }

            $invoice_date = $fn->getCPDate($rowInvoice['invoice_date'], 'd-m-Y');

            // if ($rowInvoice['gst_percentage'] > 0) {
            //     $total = $fn->getAmountFractionFormattedForGst($rowInvoice['invoice_amount'], $rowInvoice['gst_percentage']);
            // } else {
            //     $total = $rowInvoice['invoice_amount'];
            // }
            $total = $rowInvoice['invoice_amount'];
            $totalvalueRounded = number_format($total - $rowInvoice['discount'], 3);

            $add_class = '';
            if ($rowInvoice['status'] == 'Cancelled') {
                $add_class = 'highlightCell';
            }

            $inv_date = $fn->getCPDate($rowInvoice['invoice_date'], 'yy');
            $invoice_code = $rowInvoice['invoice_code'];
            
            if ($rowInvoice['status'] != 'Cancelled'){
                $rows .= "
                <tr>
                    <td>{$invoice_code}</td>
                    <!--<td>{$rowInvoice['invoice_code']}</td>-->
                    <td class='{$add_class}'>{$rowInvoice['status']}</td>
                    <td>{$invoice_date}</td>
                    <td align='right'>{$totalvalueRounded}</td>
                    <td><a href='{$urlPrintinvoice}' target='_blank'><u>Print Invoice</u></a></td>
                    <td>{$editInvoiceLink}</td>
                    <td>{$cancelInvoiceLink}</td>
                </tr>
                ";
            }
        }

        
        $SQLCancelledInvoice = "
        SELECT i.*
            ,(
            SELECT GROUP_CONCAT(r.receipt_code ORDER BY r.receipt_code SEPARATOR ', ')
            FROM receipt r, invoice_receipt_history invrecpt
            WHERE r.receipt_id = invrecpt.receipt_id
            AND i.invoice_id = invrecpt.invoice_id
            ) AS receipt_codes_history
            {$sqlAppend}
        FROM invoice i
        WHERE i.order_id = {$order_id}
        AND i.status = 'Cancelled'
        ORDER BY i.invoice_sent_out DESC
        ";
        $resultCancelledInvoice   = $db->sql_query($SQLCancelledInvoice);

        $total = 0;
        while ($rowCancelledInvoice = $db->sql_fetchrow($resultCancelledInvoice)) {
            $rowORder = $fn->getRecordRowByID('order', 'order_id', $order_id);

            if($rowORder['record_type'] == 'Manpower Supply'){
                $PrintFunctionName = 'PrintinvoiceManpowerNormal';
                if($rowCancelledInvoice['invoice_type'] == 'LOT'){
                    $PrintFunctionName = "PrintinvoiceManpowerLot";
                }
                
                $urlPrintinvoice  = "index.php?_topRm=finance&module=enggCrm_order&_spAction={$PrintFunctionName}&invoice_code={$rowCancelledInvoice['invoice_code']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            }else{
                $urlPrintinvoice  = "index.php?_topRm=finance&module=enggCrm_order&_spAction=Printinvoice&invoice_code={$rowCancelledInvoice['invoice_code']}&printOnly=1&orderNo={$order_id}&showHTML=0";
            }

            $editInvoiceLink = '';
            $cancelInvoiceLink = '';
            if ($rowCancelledInvoice['status'] != 'Cancelled'){
                $editURL = "index.php?_topRm=finance&module=enggCrm_order&_spAction=editInvoiceForm2&showHTML=0&invoice_id={$rowCancelledInvoice['invoice_id']}&order_id={$order_id}";
                $editInvoiceLink = "<a href='{$editURL}' order_id='{$rowInvoice['order_id']}' class='editInvoice2'><u>Edit</u></a>";

                $cancelInvoiceLink = "<a href='#' class='cancelInvoice2' invoice_code='{$rowCancelledInvoice['invoice_code']}' invoice_id='{$rowCancelledInvoice['invoice_id']}'><u>Cancel Invoice</u></a>";
            }

            $invoice_date = $fn->getCPDate($rowCancelledInvoice['invoice_date'], 'd-m-Y');

            // if ($rowCancelledInvoice['gst_percentage'] > 0) {
            //     $total = $fn->getAmountFractionFormattedForGst($rowCancelledInvoice['invoice_amount'], $rowCancelledInvoice['gst_percentage']);
            // } else {
            //     $total = $rowCancelledInvoice['invoice_amount'];
            // }
            $total = $rowCancelledInvoice['invoice_amount'];
            $totalvalueRounded = number_format($total - $rowInvoice['discount'], 3);

            $add_class = '';
            if ($rowCancelledInvoice['status'] == 'Cancelled') {
                $add_class = 'highlightCell';
            }

            $inv_date = $fn->getCPDate($rowCancelledInvoice['invoice_date'], 'ym/');

            $inv_year = $fn->getCPDate($rowCancelledInvoice['invoice_date'], 'yy');
            $invoice_code = $rowCancelledInvoice['invoice_code'] . '/' . $inv_year;

            $rowsCancelled .= "
            <tr class='cancelledInvoiceTableOrder'>
                <td>{$invoice_code}</td>
                <td class='{$add_class}'>{$rowCancelledInvoice['status']}</td>
                <td>{$invoice_date}</td>
                <td align='right'>{$totalvalueRounded}</td>
                <td><a href='{$urlPrintinvoice}' target='_blank'><u>Print Invoice</u></a></td>
                <td>{$editInvoiceLink}</td>
                <td>{$cancelInvoiceLink}</td>
            </tr>
            ";
        }

        $rowsCancelledHeader = "
        <tr style='background-color:#EAEAE8;' class='cancelledInvoiceTableOrder'>
            <th>Invoice Code</th>
            <th>Status</th>
            <th>Invoice Date</th>
            <th class='txtRight'>Amount</th>
            <th>Print</th>
            <th>Edit</th>
            <th>Cancel</th>
        </tr>
        ";

        $text = "
        <table class='thinlist'>
            <tr style='background-color:#EAEAE8;'>
                <th>Invoice Code</th>
                <th>Status</th>
                <th>Invoice Date</th>
                <th class='txtRight'>Amount</th>
                <th>Print</th>
                <th>Edit</th>
                <th>Cancel</th>
            </tr>
            {$rows}
            <tr>
                <th colspan='7' class='txtRight'>
                    <a class='showHideCancelledInvoice mr10'>(+) Click to View Cancelled Invoice(s)</a>
                </th>
            </tr>
            {$rowsCancelledHeader}
            {$rowsCancelled}
            {$rowsPvt}
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintinvoiceManpowerNormalOld() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $quote_id = $fn->getReqParam('quote_id');
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';
        $invoice_code = $fn->getReqParam('invoice_code');

        $SQL = "
        SELECT ini.*
                ,c.company_name
                ,o.cust_address1
                ,o.cust_address2
                ,o.cust_address_country
                ,o.cust_address_po_code
                ,c.company_id
                ,i.invoice_date
                ,ini.unit_price
                ,i.qty_text
                ,i.rate_text
                ,i.invoice_type
                ,i.invoice_code
                ,i.invoice_code_user
                ,i.invoice_terms
                ,i.invoice_due_date
                ,i.notes
                ,i.discount
                ,i.title AS invoice_title
                ,i.reference_no
                ,i.CBF_Ref_No
                ,i.gst_percentage
                ,q.quote_code
                ,q.quote_date
                ,co.first_name
                ,co.salutation
                ,ROUND((ini.qty * ini.unit_price), 3) AS amount
              ,(SELECT ROUND(SUM(init.qty * init.unit_price), 3) FROM invoice_item init
               WHERE init.invoice_id = ini.invoice_id) AS sub_total
        FROM invoice_item ini
        LEFT JOIN invoice i ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        WHERE i.invoice_code = '{$invoice_code}'
        ORDER BY ini.invoice_item_id
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        //$andalus = TCPDF_FONTS::addTTFfont(CP_LIBRARY_PATH.'/fonts/Andalus/andalus.ttf', 'TrueTypeUnicode', '', 96);
        $pdf->SetFont('andalus');
        //$pdf->SetFont($andalus);

        $today = date("d-m-Y");
        $invoice_date = $fn->getCPDate($company['invoice_date'], 'd/m/Y');

        $tbl1 = '
        <table border="0" width="100%" style="font-size:17px;">
            <tr>
                <td align="center" style="font-weight:bold;">TAX INVOICE</td>
            </tr>
        </table>
        ';

        $address2 = '';
        if($company['cust_address2']) {
            $address2 = '
            <span>'.strtoupper($company['cust_address2']).'</span><br/>
            ';
        }

        $invoice_code = 'PT/' . substr($company['invoice_code'], 3) . '/' . $company['invoice_code_user'];

        /* Company name prefix */
        $company_prefix = explode(' ', $company['company_name']);
        $length = strlen($company_prefix[0]);
        if ($length > 10) {
            $company_short = substr($company_prefix[0], 0, 10);
            $company_short = strtoupper($company_short);
        } else {
            $company_short = strtoupper($company_prefix[0]);
        }

        /* Span tag is used in tbl2 because when we use table or div it is giving more padding */
        $tbl2 ='<table border="0" width="100%" cellpadding="0" style="font-size:15px;">
                    <tr>
                        <td width="65%" style="line-height:20px;"><br/>
                            <span style="font-weight:bold;">'.strtoupper($company['company_name']).'</span><br/>
                            <span>'.strtoupper($company['cust_address1']).'</span><br/>
                            '.$address2.'
                            <span>'.strtoupper($company['cust_address_country']).' - '.$company['cust_address_po_code'].'</span>
                        </td>
                        <td width="35%" style="line-height:20px;">
                            <table border="0" width="100%" cellpadding="0">
                                <tr>
                                    <td style="text-align:right;">DATE&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="text-align:center;border:1px solid #000;">'.$invoice_date.'</td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;">Invoice No&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="text-align:center;border:1px solid #000;">'.$invoice_code.'</td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;">Customer ID&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="text-align:center;border:1px solid #000;">'.$company_short.'</td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;">Ref No&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="text-align:center;border:1px solid #000;">'.$company['reference_no'].'</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table border="0" width="100%" cellpadding="0" style="font-size:15px;">
                    <tr>
                        <td style="line-height:15px;"></td>
                    </tr>
                    <tr>
                        <td style="line-height:28px;">Attn.  :&nbsp;'.$company['salutation'].'. '.strtoupper($company['first_name']).'</td>
                    </tr>
                    <tr>
                        <td>Location  :&nbsp;'.$company['invoice_title'].'</td>
                    </tr>
                </table>
                ';

        $tbl3 ='<table border="1" cellpadding="2" width="100%" style="font-size:15px;">
                    <thead>
                        <tr>
                            <th width="5%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">S/N</th>
                            <th width="44%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">DESCRIPTION</th>
                            <th width="13%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">RATE</th>
                            <th width="19%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">HOURS</th>
                            <th width="19%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">AMOUNT (S$)</th>
                        </tr>
                    </thead>';

        $sub_total = '';
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            if($row['item_title']) {
                $tbl3 = $tbl3.'<tr>
                                    <td width="5%"></td>
                                    <td width="44%" style="font-weight:bold;"><u>'.$row['item_title'].'</u></td>
                                    <td width="13%"></td>
                                    <td width="19%"></td>
                                    <td width="19%"></td>
                                </tr>
                        ';
            }

            $uom = '';
            if ($row['unit']) {
                $uom = '/' . $row['unit'];
            }

            if ($row['qty'] == 0) {
                $row['qty'] = '';
            }

            if ($row['unit_price'] == 0) {
                $row['unit_price'] = '';
            }

            if ($row['total_cost'] == '') {
                $row['total_cost'] = $row['qty'] * $row['unit_price'];
            }

            $tbl3 = $tbl3.'<tr>
                                <td width="5%" align="center">'.$count.'</td>
                                <td width="44%">'.nl2br($row['description']).'</td>
                                <td width="13%" align="center">'.$row['qty']. $uom .'</td>
                                <td width="19%" align="center">'.$row['unit_price'].'</td>
                                <td width="19%" align="right">'.number_format($row['total_cost'], 3).'</td>
                            </tr>
                    ';

            $sub_total += $row['total_cost'];
            $count++;

            $gsttaxvalue = $row['gst_percentage'];
        }

        //$gsttaxvalue = $cpCfg['cp.gstPercentage'] ;
        if ($gsttaxvalue > 0) {
            $totalvalue = $fn->getAmountFractionFormattedForGst($sub_total, $gsttaxvalue);
            $gstvalue = $sub_total * $gsttaxvalue / 100;
        } else {
            $totalvalue = $sub_total;
            $gstvalue = 0.00;
        }

        $colspan = '4';

        $amount_in_words = $fn->getConvertNumber($totalvalue);
        $tbl3 = $tbl3.'<tr>
                          <td align="right" colspan="'.$colspan.'" style="font-weight:bold;">Sub Total</td>
                          <td align="right" style="font-weight:bold;">'.number_format($sub_total, 3).'</td>
                      </tr>
                      <tr>
                          <td colspan="'.$colspan.'" align="right" style="font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                          <td align="right" style="font-weight:bold;">'.number_format($gstvalue, 3).'</td>
                       </tr>
                       <tr>
                          <td colspan="'.$colspan.'" align="right" style="font-weight:bold;">Net Total</td>
                          <td align="right" style="font-weight:bold;">'.number_format($totalvalue, 3).'</td>
                       </tr>
                    </table>
                    <table border="0" width="100%" style="font-size:15px;text-align:center;">
                        <tr>
                            <td style="line-height:20px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>TOTAL = <span style="font-weight:bold;font-style:italic;"><i>'.strtoupper($amount_in_words).'</i></span></td>
                        </tr>
                    </table>';

        //$pdf->SetFont($andalus, 'BI');
        $tbl4 = '
        <table border="0" width="100%" style="font-size:15px;">
            <tr>
                <td style="height:10px;"></td>
            </tr>
            <tr>
                <td style="line-height:40px;"><u>Terms and Conditions</u>:</td>
            </tr>
            <tr>
                <td style="line-height:23px;">'.nl2br($company['invoice_terms']).'</td>
            </tr>
            <tr>
                <td style="height:10px;"></td>
            </tr>
            <tr>
                <td style="text-align:center;">"Thank you for your business support"</td>
            </tr>
            <tr>
                <td style="height:15px;"></td>
            </tr>
            <tr>
                <td>Yours Truly</td>
            </tr>
            <tr>
                <td style="line-height:20px;">'.$cpCfg['cp.companyProjectManagerName'].'</td>
            </tr>
            <tr>
                <td style="height:10px;"></td>
            </tr>
            <tr>
                <td>Project Manager</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['invoice_code'] . '-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintinvoiceManpowerNormal() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot3.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(28);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $quote_id = $fn->getReqParam('quote_id');
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';
        $invoice_code = $fn->getReqParam('invoice_code');

        $SQL = "
        SELECT ini.*
                ,c.company_name
                ,o.cust_address1
                ,o.cust_address2
                ,o.cust_address_country
                ,o.cust_address_po_code
                ,c.company_id
                ,i.invoice_date
                ,ini.unit_price
                ,i.qty_text
                ,i.rate_text
                ,i.invoice_type
                ,i.invoice_code
                ,i.invoice_code_user
                ,i.invoice_terms
                ,i.invoice_due_date
                ,i.notes
                ,i.discount
                ,i.title AS invoice_title
                ,i.payment_terms
                ,i.po_number
                ,i.gst_percentage
                ,q.quote_code
                ,q.site_address
                ,q.quote_date
                ,q.quote_id
                ,co.first_name
                ,co.salutation
                ,ROUND((ini.qty * ini.unit_price), 3) AS amount
              ,(SELECT ROUND(SUM(init.qty * init.unit_price), 3) FROM invoice_item init
               WHERE init.invoice_id = ini.invoice_id) AS sub_total
        FROM invoice_item ini
        LEFT JOIN invoice i ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        WHERE i.invoice_code = '{$invoice_code}'
        ORDER BY ini.invoice_item_id
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        //$andalus = TCPDF_FONTS::addTTFfont(CP_LIBRARY_PATH.'/fonts/Andalus/andalus.ttf', 'TrueTypeUnicode', '', 96);
        //$pdf->SetFont('andalus');
        //$pdf->SetFont($andalus);

        $today = date("d-m-Y");
        $invoice_date = $fn->getCPDate($company['invoice_date'], 'd/m/Y');

        $tbl1 = '
        <table width="100%" border="1" cellpadding="2" cellspacing="2">
            <tr>
                <td>
                    <table>
                        <tr>
                            <td align="center" style="font-weight:bold;font-size:20px;"><u>TAX INVOICE</u></td>
                        </tr>
                        <tr>
                            <td align="center" style="font-weight:bold;font-size:10px;">'.$cpCfg['cp.gstNumber'].'</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        ';

        $address2 = '';
        if($company['cust_address2']) {
            $address2 = '
            <span>'.strtoupper($company['cust_address2']).'</span><br/>
            ';
        }

        $inv_date = $fn->getCPDate($company['invoice_date'], 'yy');
        $invoice_code = $company['invoice_code'] . '/' . $inv_date;

        /* Company name prefix */
        $company_prefix = explode(' ', $company['company_name']);
        $length = strlen($company_prefix[0]);
        if ($length > 10) {
            $company_short = substr($company_prefix[0], 0, 10);
            $company_short = strtoupper($company_short);
        } else {
            $company_short = strtoupper($company_prefix[0]);
        }

        /* Span tag is used in tbl2 because when we use table or div it is giving more padding */
        $tbl2 ='<table border="0" width="100%" cellpadding="0" style="font-size:15px;">
                    <tr>
                        <td style="line-height:10px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="line-height:10px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td width="55%" style="font-size:11px;">To: </td>
                    </tr>
                    <tr>
                        <td width="66%" style="line-height:20px;font-size:11px;"><br/>
                            <span style="font-weight:bold;">'.strtoupper($company['company_name']).'</span><br/>
                            <span>'.strtoupper($company['cust_address1']).'</span><br/>
                            '.$address2.'
                            <span>'.strtoupper($company['cust_address_country']).' - '.$company['cust_address_po_code'].'</span>
                        </td>
                        <td width="36%" style="line-height:20px;font-size:14px;">
                            <table border="0" width="100%" cellpadding="0">
                                <tr>
                                    <td>Invoice No&nbsp;&nbsp;</td>
                                    <td>: '.$invoice_code.'</td>
                                </tr>
                                <tr>
                                    <td>Invoice Date&nbsp;&nbsp;</td>
                                    <td>: '.$invoice_date.'</td>
                                </tr>
                                <tr>
                                    <td>Payment Terms&nbsp;&nbsp;</td>
                                    <td>: '.$company['payment_terms'].'</td>
                                </tr>
                                <tr>
                                    <td>PO Number&nbsp;&nbsp;</td>
                                    <td>: '.$company['po_number'].'</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                ';

        $tbl3 ='<table border="1" cellspacing="2" cellpadding="2" width="100%" style="font-size:12px;">
                    <thead>
                        <tr>
                            <th width="10%" align="center" style="font-size:13px;font-weight:bold;font-style:italic;">S.No</th>
                            <th width="70%" align="center" style="font-size:13px;font-weight:bold;font-style:italic;">Description</th>
                            <th width="20%" align="center" style="font-size:13px;font-weight:bold;font-style:italic;">Amount (S$)</th>
                        </tr>
                    </thead>';

        $sub_total = '';
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $uom = '';
            if ($row['unit']) {
                $uom = '/' . $row['unit'];
            }

            if ($row['qty'] == 0) {
                $row['qty'] = '';
            }

            if ($row['unit_price'] == 0) {
                $row['unit_price'] = '';
            }

            if ($row['total_cost'] == '') {
                $row['total_cost'] = $row['qty'] * $row['unit_price'];
            }

            $tbl3 = $tbl3.'<tr>
                                <td style="border-top:1px solid #000000;border-left:1px solid #000000;border-right:1px solid #000000;font-style:italic;" width="10%" align="center">'.$count.'</td>
                                <td style="border-top:1px solid #000000;border-left:1px solid #000000;border-right:1px solid #000000;font-style:italic;" width="70%">'.nl2br($row['description']).'</td>
                                <td style="border-top:1px solid #000000;border-left:1px solid #000000;border-right:1px solid #000000;font-style:italic;" width="20%" align="right">'.number_format($row['total_cost'], 3).'</td>
                            </tr>
                    ';

            $sub_total += $row['total_cost'];
            $count++;

            $gsttaxvalue = $row['gst_percentage'];
        }

        //$gsttaxvalue = $cpCfg['cp.gstPercentage'] ;
        if ($gsttaxvalue > 0) {
            $totalvalue = $fn->getAmountFractionFormattedForGst($sub_total, $gsttaxvalue);
            $gstvalue = $sub_total * $gsttaxvalue / 100;
        } else {
            $totalvalue = $sub_total;
            $gstvalue = 0.00;
        }

        $colspan = '2';

        $amount_in_words = $fn->getConvertNumber($totalvalue);

        $SQLQuoteItems = "
        SELECT title
        FROM quote_items
        WHERE quote_id = '{$company['quote_id']}'
        ";
        $resultQuoteItems = $db->sql_query($SQLQuoteItems);
        $jobTitle = "";
        while($rowQuoteItems = $db->sql_fetchrow($resultQuoteItems)) {
            if($rowQuoteItems['title'] != "") {
                $jobTitle .= $rowQuoteItems['title'].', ';
            }
        }

        $jobTitle = rtrim($jobTitle, ', ');

        $tbl3 = $tbl3.'<!--<tr>
                          <td align="right" colspan="'.$colspan.'" style="font-weight:bold;">Sub Total</td>
                          <td align="right" style="font-weight:bold;">'.number_format($sub_total, 3).'</td>
                      </tr>-->
                      <tr>
                          <td style="border-left:1px solid #000000;border-right:1px solid #000000;"></td>
                          <td style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;line-height:25px;">Added '.$cpCfg['cp.gstPercentage'].'% GST</td>
                          <td align="right" style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;line-height:25px;">'.number_format($gstvalue, 3).'</td>
                       </tr>
                       <tr>
                          <td style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;"></td>
                          <td style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;line-height:70px;">(Herewith Attached Invoices Amount Details)</td>
                          <td align="right" style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;"></td>
                       </tr>
                       <tr>
                          <td style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;"></td>
                          <td style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;line-height:25px;"><b>Site Address: </b>'.$company['site_address'].'</td>
                          <td align="right" style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;"></td>
                       </tr>
                       <tr>
                          <td style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;"></td>
                          <td style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;line-height:40px;"><b>Job: </b>'.$jobTitle.'</td>
                          <td align="right" style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;"></td>
                       </tr>
                       <tr>
                          <td style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000;"></td>
                          <td style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000;line-height:25px;"></td>
                          <td align="right" style="font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;border-bottom:1px solid #000000;"></td>
                       </tr>
                       <tr>
                          <td colspan="'.$colspan.'" align="right" style="font-size:13px;font-weight:bold;font-style:italic;border-left:1px solid #000000;border-right:1px solid #000000;border-top:1px solid #000000;border-bottom:1px solid #000000;">Total Amount SGD</td>
                          <td align="right" style="font-size:13px;font-weight:bold;font-style:italic;border-right:1px solid #000000;border-top:1px solid #000000;border-left:1px solid #000000;border-bottom:1px solid #000000;">'.number_format($totalvalue, 3).'</td>
                       </tr>
                        <tr>
                            <td colspan="3" style="border:1px solid #000000;font-weight:bold;font-style:italic;">SGD : '.$amount_in_words.'</td>
                        </tr>
                    </table>';

        //$pdf->SetFont($andalus, 'BI');
        $tbl4 = '
        <table border="0" width="100%" style="font-size:11px;">
            <tr>
                <td style="height:10px;"></td>
            </tr>
            <tr>
                <td style="line-height:40px;"><u>Terms and Conditions</u>:</td>
            </tr>
            <tr>
                <td style="line-height:23px;">'.nl2br($company['invoice_terms']).'</td>
            </tr>
            <tr>
                <td style="height:10px;"></td>
            </tr>
            <tr>
                <td style="text-align:center;">"Thank you for your business support"</td>
            </tr>
            <tr>
                <td style="height:15px;"></td>
            </tr>
            <tr>
                <td style="height:10px;"></td>
            </tr>
            <tr>
                <td>Project Manager</td>
            </tr>
        </table>
        ';

        $tbl5 = '
        <table border="0" width="100%">
            <tr>
                <td width="30%" style=""></td>
                <td width="30%"></td>
                <td width="30%" style="font-size:12px; font-weight:bold;font-style:italic;" align="center">'.$cpCfg['cp.companyNameForManPowerInvoice'].'</td>
                <td width="10%"></td>
            </tr>
            <tr>
                <td style="line-height:20px;">&nbsp;</td>
            </tr>
            <tr>
                <td width="30%" style="border-bottom:1px solid black"></td>
                <td width="30%"></td>
                <td width="30%" style="border-bottom:1px solid black"></td>
                <td width="10%"></td>
            </tr>
            <tr>
                <td style="font-size:12px;font-weight:bold;font-style:italic;" align="center">Acknowledge Received</td>
                <td></td>
                <td style="font-size:12px; font-weight:bold;font-style:italic;" align="center">Authorised Signature</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        //$pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['invoice_code'] . '-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintinvoiceManpowerLot() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfoot1.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $quote_id = $fn->getReqParam('quote_id');
        $session_order_id = isset($_SESSION['order_id']) ? $_SESSION['order_id']  : '';
        $invoice_code = $fn->getReqParam('invoice_code');

        $SQL = "
        SELECT ini.*
                ,c.company_name
                ,o.cust_address1
                ,o.cust_address2
                ,o.cust_address_country
                ,o.cust_address_po_code
                ,c.company_id
                ,i.invoice_date
                ,ini.unit_price
                ,i.qty_text
                ,i.rate_text
                ,i.invoice_type
                ,i.invoice_code
                ,i.invoice_code_user
                ,i.invoice_terms
                ,i.invoice_due_date
                ,i.notes
                ,i.discount
                ,i.title AS invoice_title
                ,i.reference_no
                ,i.CBF_Ref_No
                ,i.gst_percentage
                ,q.quote_code
                ,q.quote_date
                ,co.first_name
                ,co.salutation
                ,ROUND((ini.qty * ini.unit_price), 3) AS amount
              ,(SELECT ROUND(SUM(init.qty * init.unit_price), 3) FROM invoice_item init
               WHERE init.invoice_id = ini.invoice_id) AS sub_total
        FROM invoice_item ini
        LEFT JOIN invoice i ON (i.invoice_id = ini.invoice_id)
        LEFT JOIN `order` o ON (o.order_id = i.order_id)
        LEFT JOIN company c ON (c.company_id = o.company_id)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        LEFT JOIN quote q ON (q.quote_id = o.quote_id)
        WHERE i.invoice_code = '{$invoice_code}'
        ORDER BY ini.invoice_item_id
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        //============================================================================= //

        //$andalus = TCPDF_FONTS::addTTFfont(CP_LIBRARY_PATH.'/fonts/Andalus/andalus.ttf', 'TrueTypeUnicode', '', 96);
        $pdf->SetFont('andalus');
        //$pdf->SetFont($andalus);

        $today = date("d-m-Y");
        $invoice_date = $fn->getCPDate($company['invoice_date'], 'd/m/Y');

        $tbl1 = '
        <table border="0" width="100%" style="font-size:17px;">
            <tr>
                <td align="center" style="font-weight:bold;">TAX INVOICE</td>
            </tr>
        </table>
        ';

        $address2 = '';
        if($company['cust_address2']) {
            $address2 = '
            <span>'.strtoupper($company['cust_address2']).'</span><br/>
            ';
        }

        $invoice_code = 'PT/' . substr($company['invoice_code'], 3) . '/' . $company['invoice_code_user'];

        /* Company name prefix */
        $company_prefix = explode(' ', $company['company_name']);
        $length = strlen($company_prefix[0]);
        if ($length > 10) {
            $company_short = substr($company_prefix[0], 0, 10);
            $company_short = strtoupper($company_short);
        } else {
            $company_short = strtoupper($company_prefix[0]);
        }

        /* Span tag is used in tbl2 because when we use table or div it is giving more padding */
        $tbl2 ='<table border="0" width="100%" cellpadding="0" style="font-size:15px;">
                    <tr>
                        <td width="65%" style="line-height:20px;"><br/>
                            <span style="font-weight:bold;">'.strtoupper($company['company_name']).'</span><br/>
                            <span>'.strtoupper($company['cust_address1']).'</span><br/>
                            '.$address2.'
                            <span>'.strtoupper($company['cust_address_country']).' - '.$company['cust_address_po_code'].'</span>
                        </td>
                        <td width="35%" style="line-height:20px;">
                            <table border="0" width="100%" cellpadding="0">
                                <tr>
                                    <td style="text-align:right;">DATE&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="text-align:center;border:1px solid #000;">'.$invoice_date.'</td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;">Invoice No&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="text-align:center;border:1px solid #000;">'.$invoice_code.'</td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;">Customer ID&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="text-align:center;border:1px solid #000;">'.$company_short.'</td>
                                </tr>
                                <tr>
                                    <td style="text-align:right;">Ref No&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td style="text-align:center;border:1px solid #000;">'.$company['reference_no'].'</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table border="0" width="100%" cellpadding="0" style="font-size:15px;">
                    <tr>
                        <td style="line-height:15px;"></td>
                    </tr>
                    <tr>
                        <td style="line-height:28px;">Attn.  :&nbsp;'.$company['salutation'].'. '.strtoupper($company['first_name']).'</td>
                    </tr>
                    <tr>
                        <td>Location  :&nbsp;'.$company['invoice_title'].'</td>
                    </tr>
                </table>
                ';

        $tbl3 ='<table border="1" cellpadding="2" width="100%" style="font-size:15px;">
                    <thead>
                        <tr>
                            <th width="49%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">DESCRIPTION</th>
                            <th width="13%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">RATE</th>
                            <th width="19%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">HOURS</th>
                            <th width="19%" align="center" style="font-weight:bold;background-color:#5A9BD5;color:#fff;font-weight:bold;">AMOUNT (S$)</th>
                        </tr>
                    </thead>';

        $sub_total = '';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            if ($row['qty'] == 0) {
                $row['qty'] = '';
            }

            if ($row['unit_price'] == 0) {
                $row['unit_price'] = '';
            }

            if ($row['total_cost'] == '') {
                $row['total_cost'] = $row['qty'] * $row['unit_price'];
            }

            $sub_total += $row['total_cost'];
            $count++;
            $gsttaxvalue = $row['gst_percentage'];
        }

        if ($gsttaxvalue > 0) {
            $totalvalue = $fn->getAmountFractionFormattedForGst($sub_total, $gsttaxvalue);
            $gstvalue = $sub_total * $gsttaxvalue / 100;
        } else {
            $totalvalue = $sub_total;
            $gstvalue = 0.00;
        }
        
        /*$gsttaxvalue = $cpCfg['cp.gstPercentage'] ;        
        $gstvalue    = $sub_total * $gsttaxvalue / 100;
         //Taking two decimal values for gst amount 
        $fraction_length = strlen(substr(strrchr($gstvalue, "."), 1)); // Checking the lingth of the fraction value
        if ($fraction_length > 2) {
            list($integer, $fraction) = explode(".", (string) $gstvalue);

              //Checking whether 3rd decimal point is more than or equal to 5
              //If Yes, add 1 to 2nd decimal point
             
            $gstDecimalMore = substr($fraction, 2, 1);
            $fraction = substr($fraction, 0, 2);
            if ($gstDecimalMore >= 5) {
                $fraction = $fraction + 1;
            }

            $gstvalue = $integer . "." . $fraction;
        }

        $totalvalue = $gstvalue + $sub_total;
        

        if($totalvalue == 0 || $totalvalue == ''){
            $sub_total   = $company['sub_total'];
            $gstvalue    = $sub_total * $gsttaxvalue / 100;
            //Taking two decimal values for gst amount 
            $fraction_length = strlen(substr(strrchr($gstvalue, "."), 1)); // Checking the lingth of the fraction value
            if ($fraction_length > 2) {
                list($integer, $fraction) = explode(".", (string) $gstvalue);
                $fraction = substr($fraction, 0, 2);
                $gstvalue = $integer . "." . $fraction;
            }
        }

        $totalvalue = $gstvalue + $sub_total;
        */

        $tbl3 = $tbl3.'<tr>
                            <td width="49%">'.nl2br($company['description']).'</td>
                            <td width="13%" align="center">'.$company['rate_text'].'</td>
                            <td width="19%" align="center">'.$company['qty_text'].'</td>
                            <td width="19%" align="right">'.number_format($sub_total, 3).'</td>
                        </tr>
                        ';
        
        $colspan = '3';

        $amount_in_words = $fn->getConvertNumber($totalvalue);
        $tbl3 = $tbl3.'<tr>
                          <td align="right" colspan="'.$colspan.'" style="font-weight:bold;">Sub Total</td>
                          <td align="right" style="font-weight:bold;">'.number_format($sub_total, 3).'</td>
                      </tr>
                      <tr>
                          <td colspan="'.$colspan.'" align="right" style="font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                          <td align="right" style="font-weight:bold;">'.number_format($gstvalue, 3).'</td>
                       </tr>
                       <tr>
                          <td colspan="'.$colspan.'" align="right" style="font-weight:bold;">Net Total</td>
                          <td align="right" style="font-weight:bold;">'.number_format($totalvalue, 3).'</td>
                       </tr>
                    </table>
                    <table border="0" width="100%" style="font-size:15px;text-align:center;">
                        <tr>
                            <td style="line-height:20px;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>TOTAL = <span style="font-weight:bold;font-style:italic;"><i>'.strtoupper($amount_in_words).'</i></span></td>
                        </tr>
                    </table>';

        //$pdf->SetFont($andalus, 'BI');
        $tbl4 = '
        <table border="0" width="100%" style="font-size:15px;">
            <tr>
                <td style="height:10px;"></td>
            </tr>
            <tr>
                <td style="line-height:40px;"><u>Terms and Conditions</u>:</td>
            </tr>
            <tr>
                <td style="line-height:23px;">'.nl2br($company['invoice_terms']).'</td>
            </tr>
            <tr>
                <td style="height:10px;"></td>
            </tr>
            <tr>
                <td style="text-align:center;">"Thank you for your business support"</td>
            </tr>
            <tr>
                <td style="height:15px;"></td>
            </tr>
            <tr>
                <td>Yours Truly</td>
            </tr>
            <tr>
                <td style="line-height:20px;">'.$cpCfg['cp.companyProjectManagerName'].'</td>
            </tr>
            <tr>
                <td style="height:10px;"></td>
            </tr>
            <tr>
                <td>Project Manager</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['invoice_code'] . '-Invoice.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getSummaryInOrder ($row) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        $rows  = "";

        /* Finding average gst percentage for the invoices chosen */
        $sqlGstCalc = "
        SELECT SUM(gst_percentage) AS total_gst_percentage
        FROM invoice
        WHERE order_id = {$row['order_id']}
        ";
        $resultGstCalc  = $db->sql_query($sqlGstCalc);
        $rowGstCalc     = $db->sql_fetchrow($resultGstCalc);

        $rowsGst = $fn->getRecordCount("invoice", "order_id = {$row['order_id']}");
        $gst_percentage = 0;
        if ($rowsGst) {
            $gst_percentage = ($rowGstCalc['total_gst_percentage']/$rowsGst);
        }

        $SQL = "
        SELECT o.*
              ,(SELECT SUM(round((oi.unit_price * oi.qty), 3))
               FROM order_item oi
               WHERE oi.order_id = {$row['order_id']}
               ) AS order_amount
              ,(SELECT SUM(round((oi.ot_hourly_rate * oi.employee_ot_hours), 3))
               FROM order_item oi
               WHERE oi.order_id = {$row['order_id']}
               ) AS ot_amount
              ,(SELECT SUM(round((oi.ph_hourly_rate * oi.employee_ph_hours), 3))
               FROM order_item oi
               WHERE oi.order_id = {$row['order_id']}
               ) AS ph_amount
              ,(SELECT SUM(i.invoice_amount) FROM invoice i
                WHERE i.order_id = o.order_id
                AND i.status != 'Cancelled'
                ) AS invoice_amount
              ,(SELECT SUM(i.invoice_amount)*{$gst_percentage}/100 FROM invoice i
                WHERE i.order_id = o.order_id
                AND i.status != 'Cancelled'
                ) AS gst_amount
              ,(SELECT SUM(r.amount)
                FROM receipt r
                WHERE o.order_id = r.order_id
                AND r.receipt_status != 'Cancelled'
                )AS receipt_amount
              ,(SELECT SUM(admin_charges + transport_charges)
               FROM order_item oi
               WHERE oi.order_id = {$row['order_id']}
               )AS other_amount
        FROM `order`o
        WHERE o.order_id = {$row['order_id']}
        ";
        $result = $db->sql_query($SQL);
        $row  = $db->sql_fetchrow($result);

        $row['order_amount'] = $row['order_amount'] + $row['ot_amount'] + $row['ph_amount'] + $row['other_amount'];

        $orderAmt   = number_format((float)$row['order_amount'], 3, '.', '');
        $receiptAmt = number_format((float)$row['receipt_amount'], 3, '.', '');

        if ($row['gst_amount']) {
            /* Taking two decimal values for gst amount */
            $gst_amount = $row['gst_amount'];
            $fraction_length = strlen(substr(strrchr($gst_amount, "."), 1)); // Checking the lingth of the fraction value
            if ($fraction_length > 2) {
                list($integer, $fraction) = explode(".", (string) $gst_amount);


                /* Checking whether 3rd decimal point is more than or equal to 5
                   If Yes, add 1 to 2nd decimal point
                 */
                $gstDecimalMore = substr($fraction, 2, 1);
                $fraction = substr($fraction, 0, 2);
                if ($gstDecimalMore >= 5) {
                    $fraction = $fraction + 1;
                }

                $gst_amount = $integer . "." . $fraction;
            }

            $invoiceAmt = number_format((float)$row['invoice_amount'], 3, '.', '');
            $outstandingInvoiceAmt = number_format((float)($row['invoice_amount'] - $row['receipt_amount']), 3, '.', '');
            $overallBalanceAmt     = number_format((float)($row['order_amount'] - $row['receipt_amount']), 3, '.', '');

            $gst_amount = number_format((float)$gst_amount, 3, '.', '');
            $gstAmtRow = "
            <tr>
                <td class='totalOrderAmountLabel'>TOTAL GST AMOUNT</td>
                <td class='totalOrderAmountValue txtRight'>{$gst_amount}</td>
            </tr>
            ";
        } else {
            $gstAmtRow = '';
            $invoiceAmt = number_format((float)$row['invoice_amount'], 3, '.', '');
            $outstandingInvoiceAmt = number_format((float)($row['invoice_amount'] - $row['receipt_amount']), 3, '.', '');
            $overallBalanceAmt     = number_format((float)($row['order_amount'] - $row['receipt_amount']), 3, '.', '');
        }

        $rows = "
        <table class='summaryAmountDetails'>
            <tr class= 'summaryTitle'>
                <th>SUMMARY</th>
                <th></th>
            </tr>
            <!--<tr>
                <td class='totalOrderAmountLabel'>TOTAL ORDER AMOUNT</td>
                <td class='totalOrderAmountValue txtRight'>{$orderAmt}</td>
            </tr>-->
            <tr>
            <td class='totalOrderAmountLabel'>TOTAL INVOICE RAISED</td>
            <td class='totalInvoiceAmountValue txtRight'>{$invoiceAmt}</td>
             </tr>
           <!-- {$gstAmtRow}-->
           
            <tr>
                <td class='totalOrderAmountLabel'>AMOUNT PAID</td>
                <td class='totalReciptAmountValue txtRight'>{$receiptAmt}</td>
            </tr>
            <tr>
                <td class='totalOrderAmountLabel'>OUTSTANDING INVOICE</td>
                <td class='totalOutstandingInvoiceAmtValue txtRight'>{$outstandingInvoiceAmt}</td>
            </tr>
            <!--<tr>
                <td class='totalOrderAmountLabel'>OVERALL BALANCE</td>
                <td class='totalOverallAmountValue txtRight'>{$overallBalanceAmt}</td>
            </tr>-->
        </table>
        ";

        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getCreditNotePortalDisplay($row){
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $rows = "";
        $receiptRec = $fn->getRecordRowByID('receipt', 'order_id', $row['order_id']);

        $SQL = "
        SELECT DISTINCT cn.credit_note_id
              ,cn.*
        FROM credit_note cn
        WHERE cn.order_id = {$row['order_id']}
        ORDER BY cn.credit_note_id DESC
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        while ($rowCn = $db->sql_fetchrow($result)) {

            $urlPrint = "index.php?_topRm=finance&module=enggCrm_order&_spAction=printCreditNote&credit_note_id={$rowCn['credit_note_id']}&order_id={$row['order_id']}&showHTML=0";
            $editCreditNoteURL = "index.php?_topRm=finance&module=enggCrm_order&_spAction=editCreditNoteForm&showHTML=0&credit_note_id={$rowCn['credit_note_id']}&order_id={$row['order_id']}";
            $creditNoteEditLink = "<a href='{$editCreditNoteURL}' order_id='{$row['order_id']}' class='editCreditNote'><u>Edit</u></a>";

            $credit_note_date = $fn->getCPDate($rowCn['date'], 'd-m-Y');

            $gst_amount = 0;
            if ($rowCn['gst_percentage'] > 0) {
                $gst_amount = (($rowCn['amount'] * $rowCn['gst_percentage'])/100);
            }

            $credit_note_amt = $rowCn['amount'] + $gst_amount;
            $totalvalueRounded = number_format($credit_note_amt, 3);
            
            $rows .= "
            <tr>
                <td>{$rowCn['credit_note_code']}</td>
                <td>{$credit_note_date}</td>
                <td class='txtRight'>{$totalvalueRounded}</td>
                <td><a href='{$urlPrint}' target='_blank'><u>Print Credit Note</u></a></td>
                <td>{$creditNoteEditLink}</td>
            </tr>
            ";
        }

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Code</th>
            <th>Date</th>
            <th class='txtRight'>Amount</th>
            <th>Print</th>
            <th>Edit</th>
        </tr>
        ";

        $text = "
        <h1>CREDIT NOTE(S)</h1>
        <tr class=''>
            <td>
                <div id='' class='linkPortalWrapper pms_company__pms_orderLink'>
                    <table class='thinlist creditNoteDisplay'>
                        {$header}
                        {$rows}
                    </table>
                </div>
            </td>
        </tr>
        ";

        return $text;
    }

    /**
     *
     */
    function getReceiptPortalDisplay($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        if($order_id == "") {
            $order_id = $fn->getReqParam('order_id');
        }

        $rows = "";
        $links= "";
        $sqlAppend = '';
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('receipt', 'order_id', $order_id);

        $SQL = "
        SELECT DISTINCT r.receipt_id
              ,r.*
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        WHERE r.order_id = {$order_id}
              {$sqlAppend}
        ORDER BY r.receipt_id DESC
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $count = 1;

        while ($rowReceipt = $db->sql_fetchrow($result)) {

            $urlPrint = "index.php?_topRm=finance&module=enggCrm_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&order_id={$order_id}&showHTML=0";

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowReceipt['receipt_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowReceipt['receipt_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=pms_receipt&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');

            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceiptes' order_id =
                '{$order_id}' receipt_code='{$rowReceipt['receipt_code']}' receipt_id='{$rowReceipt['receipt_id']}'><u>Cancel Receipt</u></a>";
            }

            $add_class = '';
            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $add_class = 'highlightCell';
                $cancelReceiptLink = "Cancelled";
            }

            $receipt_amount = number_format($rowReceipt['amount'], 3);
            $urlReceiptDetails = "index.php?module=enggCrm_order&_spAction=viewReceiptDetails&receipt_id={$rowReceipt['receipt_id']}&showHTML=0";
            $urlPrint = "index.php?module=enggCrm_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&order_id={$order_id}&showHTML=0";

            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td class='{$add_class}'>{$rowReceipt['receipt_status']}</td>
                <td>{$receipt_date}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$receipt_amount}</td>
                <td><a href='{$urlPrint}' target='_blank'>Print</a></td>
                <td><a class='viewReceiptDetails jqui-dialog' href='{$urlReceiptDetails}'><u>Details</u></a></td>
                <td>{$cancelReceiptLink}</td>
            </tr>
            ";
            if($rowReceipt['receipt_status'] == 'Paid'){
                $total += $rowReceipt['amount'];
            }
            $count++;
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=7>Total : $total</td>
            </tr>
        ";

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Receipt Code</th>
            <th>Status</th>
            <th>Receipt Date</th>
            <th>Mode of Payment</th>
            <th class='txtRight'>Receipt Amount</th>
            <th>Print Receipt</th>
            <th>View</th>
            <th>Cancel</th>
        </tr>
        ";

        $text = "
        <h1>RECEIPT(S)</h1>
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper pms_company__pms_orderLink'>
                <table class='thinlist receiptDisplay'>
                    {$header}
                    {$rows}
                </table>
            </div>
        </td>
        </tr>
        ";

        return $text;
    }


     /**
     *
     */
    function getReceiptPortalDisplay1($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        if($order_id == "") {
            $order_id = $fn->getReqParam('order_id');
        }

        $rows = "";
        $links= "";
        $sqlAppend = '';
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('receipt', 'order_id', $order_id);

        $SQL = "
        SELECT DISTINCT r.receipt_id
              ,r.*
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        WHERE r.order_id = {$order_id}
              {$sqlAppend}
        ORDER BY r.receipt_id DESC
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $count = 1;

        while ($rowReceipt = $db->sql_fetchrow($result)) {

            $urlPrint = "index.php?_topRm=finance&module=enggCrm_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&order_id={$order_id}&showHTML=0";

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowReceipt['receipt_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowReceipt['receipt_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=pms_receipt&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');

            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt1' order_id =
                '{$order_id}' receipt_code='{$rowReceipt['receipt_code']}' receipt_id='{$rowReceipt['receipt_id']}'><u>Cancel Receipt</u></a>";
            }

            $add_class = '';
            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $add_class = 'highlightCell';
                $cancelReceiptLink = "Cancelled";
            }

            $receipt_amount = number_format($rowReceipt['amount'], 3);
            $urlReceiptDetails = "index.php?module=enggCrm_order&_spAction=viewReceiptDetails&receipt_id={$rowReceipt['receipt_id']}&showHTML=0";
            $urlPrint = "index.php?module=enggCrm_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&order_id={$order_id}&showHTML=0";

            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td class='{$add_class}'>{$rowReceipt['receipt_status']}</td>
                <td>{$receipt_date}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$receipt_amount}</td>
                <td><a href='{$urlPrint}' target='_blank'>Print</a></td>
                <td><a class='viewReceiptDetails jqui-dialog' href='{$urlReceiptDetails}'><u>Details</u></a></td>
                <td>{$cancelReceiptLink}</td>
            </tr>
            ";
            if($rowReceipt['receipt_status'] == 'Paid'){
                $total += $rowReceipt['amount'];
            }
            $count++;
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=7>Total : $total</td>
            </tr>
        ";

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Receipt Code</th>
            <th>Status</th>
            <th>Receipt Date</th>
            <th>Mode of Payment</th>
            <th class='txtRight'>Receipt Amount</th>
            <th>Print Receipt</th>
            <th>View</th>
            <th>Cancel</th>
        </tr>
        ";

        $text = "
        <h1>RECEIPT(S)</h1>
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper pms_company__pms_orderLink'>
                <table class='thinlist receiptDisplay'>
                    {$header}
                    {$rows}
                </table>
            </div>
        </td>
        </tr>
        ";

        return $text;
    }


     /**
     *
     */
    function getReceiptPortalDisplay2($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');

        if($order_id == "") {
            $order_id = $fn->getReqParam('order_id');
        }

        $rows = "";
        $links= "";
        $sqlAppend = '';
        $exp = array('isEditable' => 1);

        $receiptRec = $fn->getRecordRowByID('receipt', 'order_id', $order_id);

        $SQL = "
        SELECT DISTINCT r.receipt_id
              ,r.*
        FROM receipt r
        LEFT JOIN (invoice_receipt_history irh) ON (r.receipt_id = irh.receipt_id)
        WHERE r.order_id = {$order_id}
              {$sqlAppend}
        ORDER BY r.receipt_id DESC
        ";
        $result   = $db->sql_query($SQL);
        $numRows  = $db->sql_numrows($result);

        $total = '';
        $discount = '';
        $tdCheckBox = '';
        $count = 1;

        while ($rowReceipt = $db->sql_fetchrow($result)) {

            $urlPrint = "index.php?_topRm=finance&module=enggCrm_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&order_id={$order_id}&showHTML=0";

            $expMedia = array('condn' => " AND media_type = 'attachment' AND actual_file_name LIKE '%{$rowReceipt['receipt_code']}%'");
            $mediaRec = $fn->getRecordRowByID('media', 'record_id', $rowReceipt['receipt_id'], $expMedia);
            $mediaLink = "index.php?plugin=common_media&_spAction=saveMedia&room=pms_receipt&recordType=attachment&media_id={$mediaRec['media_id']}&showHTML=0";

            $receipt_date = $fn->getCPDate($rowReceipt['date'], 'd-m-Y');

            $cancelReceiptLink = '';
            if ($rowReceipt['receipt_status'] != 'Cancelled') {
                $cancelReceiptLink = "<a href='#' class='cancelReceipt2' order_id =
                '{$order_id}' receipt_code='{$rowReceipt['receipt_code']}' receipt_id='{$rowReceipt['receipt_id']}'><u>Cancel Receipt</u></a>";
            }

            $add_class = '';
            if ($rowReceipt['receipt_status'] == 'Cancelled') {
                $add_class = 'highlightCell';
                $cancelReceiptLink = "Cancelled";
            }

            $receipt_amount = number_format($rowReceipt['amount'], 3);
            $urlReceiptDetails = "index.php?module=enggCrm_order&_spAction=viewReceiptDetails&receipt_id={$rowReceipt['receipt_id']}&showHTML=0";
            $urlPrint = "index.php?module=enggCrm_order&_spAction=printReceipt&receipt_code={$rowReceipt['receipt_code']}&order_id={$order_id}&showHTML=0";

            $rows .= "
            <tr>
                <td>{$rowReceipt['receipt_code']}</td>
                <td class='{$add_class}'>{$rowReceipt['receipt_status']}</td>
                <td>{$receipt_date}</td>
                <td>{$rowReceipt['mode_of_payment']}</td>
                <td align='right'>{$receipt_amount}</td>
                <td><a href='{$urlPrint}' target='_blank'>Print</a></td>
                <td><a class='viewReceiptDetails jqui-dialog' href='{$urlReceiptDetails}'><u>Details</u></a></td>
                <td>{$cancelReceiptLink}</td>
            </tr>
            ";
            if($rowReceipt['receipt_status'] == 'Paid'){
                $total += $rowReceipt['amount'];
            }
            $count++;
        }
        $total = "
            <tr style='background-color:#EAEAE8;text-align:center;font-weight:bold;'>
                <td colspan=7>Total : $total</td>
            </tr>
        ";

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th>Receipt Code</th>
            <th>Status</th>
            <th>Receipt Date</th>
            <th>Mode of Payment</th>
            <th class='txtRight'>Receipt Amount</th>
            <th>Print Receipt</th>
            <th>View</th>
            <th>Cancel</th>
        </tr>
        ";

        $text = "
        <h1>RECEIPT(S)</h1>
        <tr class=''>
        <td>
            <div id='' class='linkPortalWrapper pms_company__pms_orderLink'>
                <table class='thinlist receiptDisplay'>
                    {$header}
                    {$rows}
                </table>
            </div>
        </td>
        </tr>
        ";

        return $text;
    }

    /**
     */
    function getInvoicePortalDisplay1($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($order_id == "") {
            $order_id = $fn->getReqParam('order_id');
        }

        $formAction = '';

        $text = "
        <div id='' class='invoiceDisplay'>
            <h1>INVOICE(S)</h1>
            <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                <div id='invoicePortalOuter'>
                    {$this->getInvoicePortalDisplayDetail1($order_id)}
                </div>
            </form>
        </div>
        ";

        return $text;
    }


     /**
     */
    function getInvoicePortalDisplay2($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($order_id == "") {
            $order_id = $fn->getReqParam('order_id');
        }

        $formAction = '';

        $text = "
        <div id='' class='invoiceDisplay'>
            <h1>INVOICE(S)</h1>
            <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                <div id='invoicePortalOuter'>
                    {$this->getInvoicePortalDisplayDetail2($order_id)}
                </div>
            </form>
        </div>
        ";

        return $text;
    }

    /**
     */
    function getInvoicePortalDisplay($order_id = ''){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $formObj = Zend_Registry::get('formObj');
        $dbUtil = Zend_Registry::get('dbUtil');

        if($order_id == "") {
            $order_id = $fn->getReqParam('order_id');
        }

        $formAction = '';

        $text = "
        <div id='' class='invoiceDisplay'>
            <h1>INVOICE(S)</h1>
            <form id='orderItemPrint' class='' method='post' action='{$formAction}'>
                <div id='invoicePortalOuter'>
                    {$this->getInvoicePortalDisplayDetail($order_id)}
                </div>
            </form>
        </div>
        ";

        return $text;
    }

     /**
     *
     */
    function getPrintCreditNote() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootquote.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(8, PDF_MARGIN_TOP, 8);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER,10);
        $pdf->SetFooterMargin(4);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 5);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        /*HEADER PART AND FOOTER PART FUNCTIONS HAS BEEN ADDED IN (headfoot.php) PATH INCLUDE: (admin/lib/headfoot.php)*/
        $pdf->AddPage();

        $credit_note_id   = $fn->getReqParam('credit_note_id');
        $order_id       = $fn->getReqParam('order_id');
        $site_id = $fn->getSessionParam('cp_site_id');

if ($site_id == 1) {
    $currency = 'KWD';
} else if ($site_id == 2) {
    $currency = 'SAR';
} else {
    $currency = 'KWD';
}
        $totalvalue       = 0;

        $SQL = "
        SELECT cn.*
                ,c.company_name
                ,c.address_flat
                ,cr.credit_note_code
                ,cr.date
                ,cr.amount AS credit_note_amount
                ,cr.apply_digital_signature
                ,cr.signature_name
                ,o.cust_address1
                ,o.cust_address2
                ,o.cust_address_po_code
                ,o.cust_email
                ,o.cust_phone
                ,o.cust_fax
                ,gc.name AS cust_address_country
                ,c.company_id
                ,co.first_name
                ,co.salutation
        FROM invoice_credit_note_history  cn
        LEFT JOIN credit_note cr  ON (cr.credit_note_id  = cn.credit_note_id)
        LEFT JOIN `order` o  ON (o.order_id    = cr.order_id)
        LEFT JOIN company c  ON (c.company_id  = o.company_id)
        LEFT JOIN contact co ON (co.contact_id = o.contact_id)
        LEFT JOIN geo_country gc ON (o.cust_address_country = gc.country_code)
        WHERE cn.credit_note_id = {$credit_note_id}
        ORDER BY cn.invoice_credit_note_history_id
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $today = date("d-m-Y");
        $invoice_date = $fn->getCPDate($company['date'], 'd M Y');

        /*$sqlCompAdd = "
        SELECT ca.*
        FROM company_address ca
        WHERE ca.company_id = {$company['company_id']}
        LIMIT 0,1
        ";
        $resultCompAdd = $db->sql_query($sqlCompAdd);
        $rowCompAdd = $db->sql_fetchrow($resultCompAdd);*/


        $seal='';
        $signname='';

        if ($company['apply_digital_signature'] == 1) {
            $seal='<td width="10%"  style="font-size:15px;"><img src="images/teamseal.jpg" width="60"/></td>';
            if ($company['signature_name'] == "Jassim") {
                $signname='<td width="25%"  align="left"><img src="images/jassim.jpg" width="80" /></td>';
            } else if ($company['signature_name'] == "Ibrahim") {
                $signname='<td width="25%"  align="left"><img src="images/ibrahim.jpg" width="80" /></td>';
            } else if ($company['signature_name'] == "Wassim") {
                $signname='<td width="25%"  align="left"><img src="images/wasim.jpg" width="80" /></td>';
            } else {
                $signname='<td width="25%"  align="left"></td>';
            }
        } else {
            $seal='<td width="10%"  style="font-size:15px;"></td>';
        }

        $tbl1 = '
        <table border="0" width="100%" style="" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#14213d; text-decoration:underline; line-height:35px;">CREDIT NOTE</td>
            </tr>
        </table>
        ';

        $tbl2 ='<table border="0" width="100%" cellpadding="2" cellspacing="0">
                   
                    <tr><td width="40%" style="border:1px solid #000;"><table border="0" cellpadding="0">
                     <tr>
                        <td width="33%" style="font-size:10px; font-weight:bold; line-height:16px;">To : </td>
                        <td width="34%"></td>
                    </tr>
                                <tr>
                                    <td width="75%" style="font-size:10px;line-height:16px;">'.$company['first_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="100%" style="font-size:10px;line-height:16px;"> '.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                
                                    <td width="75%" style="font-size:10px;line-height:16px;"> '.$company['address_flat'].'</td>
                                </tr>
                               
                            </table>
                        </td>
                        <td width="10%"></td>
                        <td width="40%" style="border:1px solid #000;"><table border="0">
                               
                                <tr>
                                    <td width="25%" style="font-size:10px;font-weight:bold;line-height:16px;"> Date</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$invoice_date.'</td>
                                </tr>
                                <br/>
                                 <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;font-weight:bold;"> Ref :</td>
                                    <td width="75%" style="font-size:10px;line-height:16px;">: '.$company['credit_note_code'].'</td>
                                </tr>
                                  <br/>
                                <tr>
                                    <td width="25%" style="font-size:10px;line-height:16px;font-weight:bold;"></td>
                                    <td width="75%" style="font-size:10px;line-height:16px;"></td>
                                </tr>
                               
                            </table>
                        </td>
                    </tr>
                </table>
               
               ';

        $tbl3 ='<table border="0"  cellpadding="4"  width="100%">
                    <thead>
                        <tr >
                            <th width="6%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                            <th width="55%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">JOB DESCRIPTION</th>
                            <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                            <th width="12%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT PRICE('.$currency.')</th>
                            <th width="13%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">TOTAL PRICE('.$currency.')</th>
                        </tr>
                    </thead>';
        $subtotalValue   = 0;
        $count      = 1;
        $countCheck = 1;
        while ($row = $db->sql_fetchrow($result)) {

            if ($row['item_title']) {
                $countCheck++;
                $tbl3 = $tbl3.'<tr>
                                    <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="55%" style="font-size:10px;  border-left:1px solid #000;border-right:1px solid #000;">'.nl2br($row['item_title']).'<br/></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="13%" style="border-right:1px solid #000;"></td>
                                </tr>
                        ';
            }

            if ($row['amount'] != "") {
                $subtotal_amount = round($row['amount'], 3);
            }

            $subtotal_amount_formatted = number_format($subtotal_amount, 3);

            if($subtotal_amount_formatted == "0.00") {
                $subtotal_amount_formatted = "";
            }

            $tbl3 = $tbl3.'<tr>
                                <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">1</td>
                                <td width="12%" align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['amount'].'</td>
                                <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;">'.$subtotal_amount_formatted.'</td>
                            </tr>
                    ';

            $subtotalValue += $subtotal_amount;
      
                $totalvalue = $subtotalValue;
            

            $count++;
            $countCheck++;
        }

        $amount_in_words   = $fn->getConvertNumber($totalvalue);
       
        $tbl3 = $tbl3.'<tr>
                          <td colspan="2" style="font-size:10px; border:1px solid #000;border-right:1px solid #000;">Invoice Amount In '.$currency.' : '.$amount_in_words.'</td>
                        
                          <td align="right" colspan="3" style="font-size:10px; font-weight:bold; border:1px solid #000;">'.number_format($totalvalue, 3).'</td>
                      </tr>
                     
                      
                    </table>';

        $tbl5 = '
        <table border="0" width="100%" cellpadding="3">
        <tr>
        '.$signname.'
        '.$seal.'
        </tr>
            <tr>
            <br/>
                <td width="40%" style=" font-weight:bold; font-size:10px;">For <br/>'.$cpCfg['cp.companyName'].'</td>
                <td width="30%"></td>
                <td width="30%"></td>
            </tr>
          
        </table>
        ';
        
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-6);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->ln(5);
        $pdf->writeHTML($tbl5, true, false, false, false, '');

        $download_title = $company['credit_note_code'] .'-A Team-Credit Note.pdf';
        $pdf->Output($download_title, 'I');
    }
}
