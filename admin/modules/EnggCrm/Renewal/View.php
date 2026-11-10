<?
class CPL_Admin_Modules_EnggCrm_Renewal_View extends CP_Common_Lib_ModuleViewAbstract
{
     /**
     *
     */
    function getList($dataArray) {
        $fn = Zend_Registry::get('fn');
        $listObj = Zend_Registry::get('listObj');
        
        $count = 0;
        $rows = '';
        
        foreach ($dataArray as $row) {
            $start_date = $fn->getCPDate($row['start_date'], 'd-m-Y');
            $end_date = $fn->getCPDate($row['end_date'], 'd-m-Y');
            $renewal_due = $fn->getCPDate($row['renewal_due'], 'd-m-Y');
            
            // Get appropriate PDF URL based on contract type
            $urlPrintLinkPdf = '';
            switch ($row['contract_type']) {
                case 'Drain Flushing':
                    $urlPrintLinkPdf = "index.php?_topRm=project&module=enggCrm_renewal&_spAction=PrintDrainFlushPdf&renewal_id={$row['renewal_id']}&showHTML=0";
                    break;
                case 'Ac and Electrical':
                    $urlPrintLinkPdf = "index.php?_topRm=project&module=enggCrm_renewal&_spAction=PrintAcElectricalPdf&renewal_id={$row['renewal_id']}&showHTML=0";
                    break;
                case 'AC Maintenance':
                    $urlPrintLinkPdf = "index.php?_topRm=project&module=enggCrm_renewal&_spAction=PrintACMaintenancePdf&renewal_id={$row['renewal_id']}&showHTML=0";
                    break;
                case 'AMC MEP':
                    $urlPrintLinkPdf = "index.php?_topRm=project&module=enggCrm_renewal&_spAction=PrintAMCMEPPdf&renewal_id={$row['renewal_id']}&showHTML=0";
                    break;
                case 'Electrical':
                    $urlPrintLinkPdf = "index.php?_topRm=project&module=enggCrm_renewal&_spAction=PrintElectricalPdf&renewal_id={$row['renewal_id']}&showHTML=0";
                    break;
            }
    
            $urlPrintLinkPdfs = "index.php?_topRm=project&module=enggCrm_renewal&_spAction=PrintElectricalPdf&renewal_id={$row['renewal_id']}&showHTML=0";
    
            // Get order and finance URLs
            $rowComp = $fn->getRecordByCondition('order', "renewal_id = '{$row['renewal_id']}'");
            $orderUrl = "/admin/index.php?_topRm=finance&module=enggCrm_order&_action=edit&order_id={$rowComp['order_id']}";
    
            // Define action buttons
            $printPdf = "<a href='{$urlPrintLinkPdfs}' target='_blank' class='btn btn-info button ml10'>Print</a>";
            $buttonFinance = "<a href='{$orderUrl}' target='_blank' style='color:#000000;' class='button'><u>Go to Finance</u></a>";
    
            // Check if last actual_date is older than 92 days
            $latestRenewal = $fn->getRecordByCondition('service_renewal', "renewal_id = '{$row['renewal_id']}' ORDER BY service_renewal_id DESC");
            $highlightRow = false;
            $serviceDueText = "No"; // Default text for service due column
            if ($latestRenewal && isset($latestRenewal['schedule_date'])) {
                $actualDate = new DateTime($latestRenewal['schedule_date']);
                $now = new DateTime();
                $interval = $now->diff($actualDate)->days;
    
                if ($interval > 92) {
                    $highlightRow = true; // Flag to set row color to pink
                    $serviceDueText = "Yes"; // Change text if condition is met
                }
            }
    
            // Render row with conditional styling
            $rowStyle = $highlightRow ? "style='background-color: pink;'" : "";
    
            // Highlight renewal_due if it exceeds 30 days
            $renewalDate = new DateTime($row['renewal_due']);
            $currentDate = new DateTime();
            $renewalInterval = $renewalDate->diff($currentDate)->days;
    
            $highlightRenewalDue = "";
            if ($renewalDate > $currentDate && $renewalInterval <= 30) {
                $highlightRenewalDue = "style='background-color: yellow; font-weight: bold;'";
            }
    
            $rows .= "
            		{$listObj->getListRowHeader($row, $count)}
                    <td>{$row['ref_no']}</td>
                    <td>{$row['company_name']}</td>
                    <td>{$row['renewal_shop']}</td>
                    <td>{$row['renewal_location']}</td>
                    <td>{$start_date}</td>
                    <td>{$end_date}</td>
                    <td {$highlightRenewalDue}>{$renewal_due}</td>
                    <td>{$row['contract_value']}</td>
                    <td>{$serviceDueText}</td> <!-- New column for Service Due -->
                    <td>{$row['payment_status']}</td>
                    <td>{$printPdf}</td>
                    {$listObj->getListRowEnd($row['renewal_id'])}
                ";
            

        $count++;
        }
        
        $text = "
            <table class='thinlist' scrollabletable='1' id='bodyList' cellspacing='1'>
                <thead>
                    <tr>
                        <th>#</th>
                        <th></th>
                        <th>Contract No</th>
                        <th>Client Name</th>
                        <th>Shop Name</th>
                        <th>Location</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Renewal Due</th>
                        <th>Value</th>
                        <th>Service Due</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {$rows}
                </tbody>
            </table>";
    
        return $text;
    }
    
    
    
    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
		 $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $sqlCompany = "
        SELECT company_id, company_name FROM company
        ORDER BY company_name
        ";

        $newCompUrl = 'index.php?_spAction=new&lnkRoom=enggCrm_companyLink&showHTML=0';
        $newCompUrl = "<a class='jqui-dialog-form float_left' formId='portalForm' title='New Company'
        w=800 href='' link='{$newCompUrl}' callback='cpm.enggCrm.renewal.afterNewCompany'>New</a>";
        $expComp  = array(
             'notesRight'  => $newCompUrl
            ,'autoSgstModule' => 'enggCrm_company'
            ,'autoSgstSrchFld' => 'company_name'
            ,'autoSgstActualFld' => 'company_id'
            ,'autoSgstActualFldVal' => ''
            ,'autoSgstCallBack' => 'cpm.enggCrm.renewal.loadContactsByCompany'
        );

        $sqlContact = '';

        $newContactUrl = 'index.php?_spAction=new&lnkRoom=enggCrm_contactLink&showHTML=0';
        $newContactUrl = "<a class='jqui-dialog-form float_left newContactLink' formId='portalForm' title='New Contact'
        w=800 href='' link='{$newContactUrl}' callback='cpm.enggCrm.renewal.afterNewContact'>New</a>";

        $expCont  = array(
             'notesRight'  => $newContactUrl
        );

        $expVl   = array('sqlType' => 'OneField');
        $sqlType = $fn->getValueListSQL('contractType');
        $formConType = "index.php?_topRm=main&module=enggCrm_renewal&_spAction=addNewValuelistForm&valuelist_name=contractType&showHTML=0";
        $expConType = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formConType}' class='mr20 addNewValue' valuelist_name='contractType'>New</a>");

		        
        $fielset="
        {$formObj->getDateRow('Contrat Start Date', 'start_date', date('Y-m-d'))}
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany, '', $expComp)}
        {$formObj->getDDRowBySQL('Contract Type', 'contract_type', $sqlType, '', $expConType)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fielset)}
        ";
        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
		$tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');

        $expNoEdit = array('isEditable' => 0);

        $formObj->mode = $tv['action'];

        $renewal_id = $fn->getReqParam('renewal_id');

        $rowComp = $fn->getRecordByCondition('company', "company_id = '{$row['company_id']}'");
        $rowCont = $fn->getRecordByCondition('contact', "contact_id = '{$row['contact_id']}'");


        $sqlComp = "
        SELECT company_id, company_name FROM company
        ORDER BY company_name ASC
        ";
        $drainFlush="";
        if($row['contract_type'] == 'Drain Flushing'){

        $drainFlush="<tr>
            <th colspan='4'>Drain Flush</th>
        </tr>
        <tr>
            <td>{$formObj->getTBRow('Visit Price', 'price_visit', $row['price_visit'])}</td>
            <td>{$formObj->getTBRow('Mall', 'mall', $row['mall'])}</td>
            <td>{$formObj->getTBRow('Shop', 'shop', $row['shop'])}</td>
        </tr>
        <tr>
            <td>{$formObj->getTBRow('Valid Period', 'valid_period', $row['valid_period'])}</td>
            <td>{$formObj->getTBRow('Pay', 'pay_machine', $row['pay_machine'])}</td>
            <td>{$formObj->getTBRow('Service', 'service', $row['service'])}</td>


        </tr>
         ";
        }else{
            $drainFlush="
             <tr>
            <th colspan='4'>Article 1</th>
        </tr>
        <tr>
            <td>{$formObj->getTARow('Maintain', 'willing_to_maintain', $row['willing_to_maintain'])}</td>
            <td>{$formObj->getTARow('Shop Mention', 'shop_mention', $row['shop_mention'])}</td>
         
        </tr>
         <tr>
            <th colspan='4'>Article 2</th>
        </tr>
        <tr>
            <td>{$formObj->getDateRow('Contract Start Date', 'start_date', $row['start_date'])}</td>
            <td>{$formObj->getDateRow('Contract End Date', 'end_date', $row['end_date'])}</td>
         
        </tr>
        <tr>
        <th colspan='4'>Article 3</th>
    </tr>
    <tr>
        <td>{$formObj->getTARow('Article 3', 'article_three', $row['article_three'])}</td>
     
    </tr>
         <tr>
            <th colspan='4'>Article 4</th>
        </tr>
        <tr>
            <td>{$formObj->getTBRow('Contact Person', 'contact_name', $row['contact_name'])}</td>
            <td>{$formObj->getTBRow('Contact No', 'mobile', $row['mobile'])}</td>
         
        </tr>
        <tr>
            <td>{$formObj->getTBRow('Contact Person2', 'contact_name2', $row['contact_name2'])}</td>
            <td>{$formObj->getTBRow('Contact No2', 'mobile2', $row['mobile2'])}</td>
         
        </tr>
        <tr>
            <td>{$formObj->getTBRow('Contact Person3', 'contact_name3', $row['contact_name3'])}</td>
            <td>{$formObj->getTBRow('Contact No3', 'mobile3', $row['mobile3'])}</td>
         
        </tr>
         <tr>
            <th colspan='4'>Article 5</th>
        </tr>
        <tr>
            <td>{$formObj->getTARow('Content', 'article_five_content', $row['article_five_content'])}</td>

         
        </tr>
        <tr>
            <th colspan='4'>Article 6</th>
        </tr>
        <tr>
            <td>{$formObj->getTARow('Article 6', 'article_six', $row['article_six'])}</td>
        
        </tr>
        <tr>
        <th colspan='4'>Article 7</th>
        </tr>
        <tr>
            <td>{$formObj->getTARow('Article 7', 'article_seven', $row['article_seven'])}</td>
        
        </tr>
        ";
        }
        $urlPrintLinkPdf="";
        $creation_date = $fn->getCPDate($row['creation_date'], 'd-m-Y-H-i-s');
        $modification_date = $fn->getCPDate($row['modification_date'], 'd-m-Y-H-i-s');
      
        $urlPrintLinkPdf  = "index.php?_topRm=project&module=enggCrm_renewal&_spAction=PrintElectricalPdf&renewal_id={$row['renewal_id']}&showHTML=0";
        
        $printPdf = "
        <div class='floatbox'>
            <div class='float_right m5'>
                <a href='{$urlPrintLinkPdf}' target='_blank' class='btn btn-info button ml10'>Print</a>                  
            </div>
        </div>";

        $expVl        = array('sqlType' => 'OneField');

        $sqlType = $fn->getValueListSQL('contractType');
    
        $signArray = array(
            "Jassim"
           ,"Ibrahim"
           ,"Wassim"
        );
          
        $sqlCompany = "
        SELECT company_id, company_name FROM company
        ORDER BY company_name
        ";

        $newCompUrl = 'index.php?_spAction=new&lnkRoom=enggCrm_companyLink&showHTML=0';
        $newCompUrl = "<a class='jqui-dialog-form float_left' formId='portalForm' title='New Company'
        w=800 href='' link='{$newCompUrl}' callback='cpm.enggCrm.renewal.afterNewCompany'>New</a>";
        $expComp  = array(
           'notesRight'  => $newCompUrl
          ,'autoSgstModule' => 'enggCrm_company'
          ,'autoSgstSrchFld' => 'company_name'
          ,'autoSgstActualFld' => 'company_id'
          ,'autoSgstActualFldVal' => ''
          ,'autoSgstCallBack' => 'cpm.enggCrm.renewal.loadContactsByCompany'
        );
  
        $sqlContact = '';
        if ($row['company_id'] != "") {
            $sqlContact = $fn->getDDSql('enggCrm_contact', array('condn' => "company_id = {$row['company_id']}"));
        }

  
        $newContactUrl = "index.php?_spAction=new&lnkRoom=enggCrm_contactLink&company_id={$rowComp['company_id']}&showHTML=0";
        $newContactUrl = "<a class='jqui-dialog-form float_left newContactLink' formId='portalForm' title='New Contact'
        w=800 href='' link='{$newContactUrl}' callback='cpm.enggCrm.renewal.afterNewContact'>New</a>";

        $expCont  = array(
           'notesRight'  => $newContactUrl
           ,'detailValue' => $row['contact_name']
        );
  
        $expVl   = array('sqlType' => 'OneField');
        $sqlCategory    = $fn->getValueListSQL('checklist');
        $sqlConType    = $fn->getValueListSQL('contractType');   
    	$sqlPayStatus = $fn->getValueListSQL('paymentStatus');

        $formAddPosition = "index.php?_topRm=main&module=enggCrm_renewal&_spAction=addNewValuelistForm&valuelist_name=checklist&renewal_id={$row['renewal_id']}&showHTML=0";
        $expGroup = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formAddPosition}' class='mr20 addNewValue' valuelist_name='checklist'>New</a>");

        $formConType = "index.php?_topRm=main&module=enggCrm_renewal&_spAction=addNewValuelistForm&valuelist_name=contractType&renewal_id={$row['renewal_id']}&showHTML=0";
        $expConType = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formConType}' class='mr20 addNewValue' valuelist_name='contractType'>New</a>");

        $formPayStatus = "index.php?_topRm=main&module=enggCrm_renewal&_spAction=addNewValuelistForm&valuelist_name=paymentStatus&renewal_id={$row['renewal_id']}&showHTML=0";
        $expPayStatus = array('sqlType' => 'OneField'
                            ,'notesRight' => "<a href='{$formPayStatus}' class='mr20 addNewValue' valuelist_name='paymentStatus'>New</a>");

        $text = "
          <div class='floatbox'>
            <div class='float_right'><u>{$printPdf}</u></div>
        </div>
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='float_left'>Contract Details</div>
                    <div class='toggle'></div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Modified : {$row['modified_by']} {$modification_date}</div>
                    
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>
                    <table class='thinlist'>
                        <tbody>
                           
          <tr>
         <td>{$formObj->getTBRow('Contract No', 'ref_no', $row['ref_no'])}</td>
		<td>{$formObj->getTBRow('Contract Value', 'contract_value', $row['contract_value'])}</td>
		<td>{$formObj->getTBRow('Reference Quotation', 'reference_quotation', $row['reference_quotation'])}</td>
		<td>{$formObj->getTBRow('Email Client', 'email', $row['email'])}</td>

        </tr>
        
          
        <tr>
        <td>{$formObj->getDDRowBySQL('Service Included', 'service_included', $sqlCategory, $row['service_included'], $expGroup)}</td>
         <td>{$formObj->getDateRow('Contract Start Date', 'start_date', $row['start_date'])}</td>
          <td>{$formObj->getTBRow('Terms Of Payment', 'terms_of_payment', $row['terms_of_payment'])}</td>
            <td> {$formObj->getDATERow('Renewal Due', 'renewal_due',  $row['renewal_due'])}</td>
         
        </tr>
         <tr>
            <td>{$formObj->getDDRowBySQL('Company', 'company_id', $sqlCompany, $rowComp['company_id'], $expComp)}</td>    
            <td>{$formObj->getDateRow('Contract End Date', 'end_date', $row['end_date'])}</td>
            <td>{$formObj->getDDRowBySQL('Contract Type', 'contract_type', $sqlConType, $row['contract_type'], $expConType)}</td>
            <td>{$formObj->getTBRow('Contact Person', 'contact_name', $row['contact_name'])}</td>
        </tr>
        <tr>    
            <td>{$formObj->getTBRow('Contact No', 'mobile', $row['mobile'])}</td>
            <td>{$formObj->getTBRow('Contact Person2', 'contact_name2', $row['contact_name2'])}</td>
            <td>{$formObj->getTBRow('Contact No2', 'mobile2', $row['mobile2'])}</td>
            <td>{$formObj->getTBRow('Contact Person3', 'contact_name3', $row['contact_name3'])}</td>
        </tr>
        <tr>    
            <td>{$formObj->getTBRow('Contact No3', 'mobile3', $row['mobile3'])}</td>
            <td>{$formObj->getDDRowBySQL('Payment Status', 'payment_status', $sqlPayStatus, $row['payment_status'], $expPayStatus)}</td>
        </tr>
      
       
        </tbody>
      </table>
        </div>
        </div>
        </div>
        ";
		
        return $text;
    }


        /**
     *
     */
    function getAddNewValuelistForm() {
        $tv      = Zend_Registry::get('tv');
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $valuelist_name = $fn->getReqParam('valuelist_name');
        $renewal_id    = $fn->getReqParam('renewal_id');

        $formAction = "index.php?_topRm=main&module=enggCrm_renewal&_spAction=addNewValuelistFormSubmit&showHTML=0";

        $text = "
        <form id='portalForm' class='yform columnar addNewDropdownValueForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('Value', 'valuelist_value')}
            <input type='hidden' name='valuelist_name' value='{$valuelist_name}' />
            <input type='hidden' name='renewal_id' value='{$renewal_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintElectricalPdf() {
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
        $pdf->SetFooterMargin(6);
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

        $renewal_id = $fn->getReqParam('renewal_id');

        $SQL = "
        SELECT c.*
                ,cy.company_name
              ,cy.address_flat AS billing_address_flat
              ,cy.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,cy.address_po_code AS billing_address_po_code
               ,(
        SELECT sr.shop
        FROM shop_renewal sr
        WHERE sr.renewal_id = c.renewal_id
        LIMIT 1
    ) AS renewal_shop
    ,(
        SELECT sr.location
        FROM shop_renewal sr
        WHERE sr.renewal_id = c.renewal_id
        LIMIT 1
    ) AS renewal_location
        FROM renewal c
        LEFT JOIN (company cy) ON (cy.company_id = c.company_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = cy.address_country)
        WHERE c.renewal_id = '{$renewal_id}'
       ORDER BY c.renewal_id ASC

        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $quote_date   = $fn->getCPDate($company['start_date'], 'd/m/Y');
        $end_date   = $fn->getCPDate($company['end_date'], 'd/m/Y');
        $renewal_due   = $fn->getCPDate($company['renewal_due'], 'd/m/Y');

        $today      = date("d-m-Y");

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = ',<br/>: '.$company['billing_address_street'];
        }

        $seal='';
        $signname='';

        if($company['apply_digital_signature'] == 1){
         $seal='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-left:1px solid #000; border-bottom:1px solid #000;"><img src="images/teamseal.jpg" width="80"/></td>';
         if($company['signature_name'] == "Jassim"){  
        $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/jassim.jpg" width="130" /></td>';
         } else if($company['signature_name'] == "Ibrahim"){  
            $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/ibrahim.jpg" width="130" /></td>';
             } else if($company['signature_name'] == "Wassim"){  
                $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/wasim.jpg" width="130" /></td>';
                 }else{
                    $signname='<td width="25%" border="1" style="font-size:15px;"></td>';
                 }
        }else{
            $seal='<td width="25%" border="1" style="font-size:15px;"></td>';
        }
       


        $tbl1 = '
      <table border="0" width="100%" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; text-decoration:underline; ">'.$company['contract_type'].'- Contract</td>

            </tr>

             <tr>
                <td width="30%" style="font-size:16px; line-height:22px;">Date : '.$quote_date.' </td>
                <td width="70%" align="right" style="line-height:22px;">Contract No :'.$company['ref_no'].' </td>

            </tr>

            <tr>
                <td width="100%" style="font-size:12px;">This contract is made on above date between:</td>

            </tr>

        </table>

         <table border="0" width="100%" cellpadding="4">
            <tr>
                <td width="50%" style="font-size:16px;">First Party:</td>

            </tr>

             <tr>
                 <td width="70%" style="font-size:14px;  line-height:22px;">'.$company['company_name'].',<br/>'.$company['billing_address_flat'].'</td>

            </tr>

            <tr>
                <td width="50%" style="font-size:16px;">Second Party:</td>

            </tr>

             <tr>
                <td width="100%" style="font-size:14px;  line-height:22px;"><b>M/s A TEAM INTERNATIONAL</b><br/>
                It’s Residence In Arbeed Building, Office no1, Floor 1, Street 169, Block 11, Hawally, Kuwait
                </td>

            </tr>

        </table>
            
          <table cellpadding="10" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;">
        <thead>
            <tr>
                <th colspan="2" style="background-color: #c2003c; color: white; text-transform: uppercase; padding: 8px; text-align: left; font-weight: bold;">
                    Contract Details
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 8px; font-weight: bold;">Contract No</td>
                <td style="padding: 8px; font-weight: bold;">Company</td>
            </tr>
            <tr>
                <td style="padding: 8px;background-color: #ddd;">'.$company['ref_no'].'</td>
                <td style="padding: 8px;background-color: #ddd;">'.$company['company_name'].'</td>
            </tr>

             <tr>
                <td style="padding: 8px; font-weight: bold;">Shop</td>
                <td style="padding: 8px; font-weight: bold;">Location</td>
            </tr>
            <tr>
                <td style="padding: 8px;background-color: #ddd;">'.$company['renewal_shop'].'</td>
                <td style="padding: 8px;background-color: #ddd;">'.$company['renewal_location'].'</td>
            </tr>

              <tr>
                <td style="padding: 8px; font-weight: bold;">Contract Start Date</td>
                <td style="padding: 8px; font-weight: bold;">Contract End Date</td>
            </tr>
            <tr>
                <td style="padding: 8px;background-color: #ddd;">'.$quote_date.'</td>
                <td style="padding: 8px;background-color: #ddd;">'.$end_date.'</td>
            </tr>

             <tr>
                <td style="padding: 8px; font-weight: bold;">Contact Person</td>
                <td style="padding: 8px; font-weight: bold;">Contact No</td>
            </tr>
            <tr>
                <td style="padding: 8px;background-color: #ddd;">'.$company['contact_name'].'</td>
                <td style="padding: 8px;background-color: #ddd;">'.$company['mobile'].'</td>
            </tr>

              <tr>
                <td style="padding: 8px; font-weight: bold;">Renewal Due</td>
                <td style="padding: 8px; font-weight: bold;">Contract Value</td>
            </tr>
            <tr>
                <td style="padding: 8px;background-color: #ddd;">'.$renewal_due.'</td>
                <td style="padding: 8px;background-color: #ddd;">'.$company['contract_value'].'</td>
            </tr>

            <tr>
                <td style="padding: 8px; font-weight: bold;">Service Included</td>
                <td style="padding: 8px; font-weight: bold;">Email</td>
            </tr>
            <tr>
                <td style="padding: 8px;background-color: #ddd;">'.$company['service_included'].'</td>
                <td style="padding: 8px;background-color: #ddd;">'.$company['email'].'</td>
            </tr>

            <tr>
                <td style="padding: 8px; font-weight: bold;">Reference Quotation</td>
                <td style="padding: 8px; font-weight: bold;">Terms Of Payment</td>
            </tr>
            <tr>
                <td style="padding: 8px;background-color: #ddd;">'.$company['reference_quotation'].'</td>
                <td style="padding: 8px;background-color: #ddd;">'.$company['terms_of_payment'].'</td>
            </tr>
          
        </tbody>
    </table>
      ';

 
       $pdf->writeHTML($tbl1, true, false, false, false, '');
                // $pdf->ln(20);
              // $pdf->AddPage();
        //$pdf->writeHTML($tbl4, true, false, false, false, '');
   // $pdf->ln(9);
       // $pdf->writeHTML($tbl5, true, false, false, false, '');
        //$pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $company['ref_no'] . '-' . $company['contract_type'] . '.pdf';
        //ob_end_clean();
        $pdf->Output($download_title, 'I');
    }


    /**
     *
     */
    function getPrintACElectricalPdf() {
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
        $pdf->SetFooterMargin(6);
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

        $renewal_id = $fn->getReqParam('renewal_id');

        $SQL = "
        SELECT c.*
                ,cy.company_name
              ,cy.address_flat AS billing_address_flat
              ,cy.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,cy.address_po_code AS billing_address_po_code
        FROM renewal c
        LEFT JOIN (company cy) ON (cy.company_id = c.company_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = cy.address_country)
        WHERE c.renewal_id = '{$renewal_id}'
       ORDER BY c.renewal_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);


        $quote_date   = $fn->getCPDate($company['date'], 'd/m/Y');
        $today      = date("d-m-Y");

       

         $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = ',<br/>: '.$company['billing_address_street'];
        }
        $seal='';
        $signname='';

       if($company['apply_digital_signature'] == 1){
         $seal='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-left:1px solid #000; border-bottom:1px solid #000;"><img src="images/teamseal.jpg" width="80"/></td>';
         if($company['signature_name'] == "Jassim"){  
        $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/jassim.jpg" width="130" /></td>';
         } else if($company['signature_name'] == "Ibrahim"){  
            $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/ibrahim.jpg" width="130" /></td>';
             } else if($company['signature_name'] == "Wassim"){  
                $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/wasim.jpg" width="130" /></td>';
                 }else{
                    $signname='<td width="25%" border="1" style="font-size:15px;"></td>';
                 }
        }else{
            $seal='<td width="25%" border="1" style="font-size:15px;"></td>';
        }

        $tbl1 = '
      <table border="0" width="100%" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; text-decoration:underline; ">Maintenance Agreement contract for<br/>AC & Electrical</td>

            </tr>

             <tr>
                <td width="30%" style="font-size:16px; line-height:22px;">Date : '.$quote_date.' </td>
                <td width="70%" align="right" style="line-height:22px;">Ref :'.$company['ref_no'].' </td>

            </tr>

            <tr>
                <td width="100%" style="font-size:12px;">This contract is made on above date between:</td>

            </tr>

        </table>

         <table border="0" width="100%" cellpadding="4">
            <tr>
                <td width="50%" style="font-size:16px;">First Party:</td>

            </tr>

             <tr>
                <td width="70%" style="font-size:14px;  line-height:22px;">'.$company['company_name'].',<br/>'.$company['billing_address_flat'].'</td>

            </tr>

            <tr>
                <td width="50%" style="font-size:16px;">Second Party:</td>

            </tr>

             <tr>
                 <td width="100%" style="font-size:14px;  line-height:22px;"><b>M/s A TEAM INTERNATIONAL</b><br/>
                It’s Residence In Arbeed Building, Office no1, Floor 1, Street 169, Block 11, Hawally, Kuwait
                </td>
            </tr>

        </table>
            
          <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 1</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;">The Second Party is willing to maintain,</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">'.nl2br($company['willing_to_maintain']).'</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">Belonging for first party for Shops as mentioned below:</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">'.nl2br($company['shop_mention']).'</td>

            </tr>

        </table>
         <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 2</td>
            </tr>
             <tr>
                <td style="line-height:22px;font-size:12px;">1.The second party conducts maintenance visits quarterly for above mentionshops</td>
            </tr>
             <tr>
                <td style="line-height:22px;font-size:12px;">2. The second party will provide maintenance report for each shop after inspection</td>
            </tr>
             <tr>
                <td style="line-height:22px;font-size:12px;">3. This contract period is from ('.$company['start_date'].')and ending in ('.$company['end_date'].')</td>
            </tr>
        </table>
         <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 3</td>
            </tr>
             <tr>
                <td style="line-height:22px;font-size:12px;">'.$company['article_three'].'</td>
            </tr>
        </table>
        <table border="0" width="100%" cellpadding="4">
        <tr>
            <td  style="font-size:16px;text-decoration:underline;">Article 4</td>

        </tr>

         <tr>
           
            <td style="line-height:22px;font-size:12px;"> In case of any emergency First party will contact Second Party and Second Party will attend the same within 6 -18 hoursof time frame.<br/>
                Second Party Contact:  '.$company['contact_name'].':'.$company['mobile'].'
                </td>
        </tr>
    </table>
   
  
    
        ';


        $tbl4=' 
       
        <table border="0" width="100%" cellpadding="4">
        <tr>
            <td  style="font-size:16px;text-decoration:underline;line-height:22px;">Article 5</td>
    
        </tr>
    
         <tr>
           
            <td style="font-size:12px;line-height:22px;">'.nl2br($company['article_five_content']).' </td>
        </tr>
    </table>
      <table border="0" width="100%" cellpadding="4">
        <tr>
            <td  style="font-size:16px;text-decoration:underline;">Article 6</td>

        </tr>

         <tr>
           
            <td style="line-height:22px;font-size:12px;">'.$company['article_six'].'</td>
        </tr>
    </table>
    <table border="0" width="100%" cellpadding="4">
        <tr>
            <td  style="font-size:16px;text-decoration:underline;">Article 7</td>

        </tr>

         <tr>
           
            <td style="line-height:22px;font-size:12px;">'.$company['article_seven'].'<br/><br/>
<b>IN WITNESS WHEREOF</b> the parties here to have caused,this Agreement to be executed by the authorized representatives on the day and date written below.
</td>
        </tr>
    </table> ';

 
             $tbl5 = '       

                   <table border="0" width="100%" cellpadding="4">
                             

               <tr>

              <td width="50%" style="font-size:10px;">OnBehalf of Party1<br/>'.$company['behalf_party'].'<br/> </td>
            <td width="50%" border="1" style="font-size:15px;"></td>
               </tr>
                <br/>
               <tr>

              <td width="50%"  style="font-size:10px;"><b>OnBehalf of Party2</b><br/>
                M/s A TEAM International <br/>
                 </td>
             '.$seal.'
            '.$signname.'
               </tr>
              
                
             
        </table>';
        
       $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(20);
               $pdf->AddPage(); 
        $pdf->writeHTML($tbl4, true, false, false, false, '');
    $pdf->ln(9);
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        //$pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . '-Ac Electrical.pdf';
        //ob_end_clean();
        $pdf->Output($download_title, 'I');
    }



     /**
     *
     */
    function getPrintDrainFlushPdf() {
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
        $pdf->SetFooterMargin(6);
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

        $renewal_id = $fn->getReqParam('renewal_id');

        $SQL = "
      SELECT c.*
                ,cy.company_name
              ,cy.address_flat AS billing_address_flat
              ,cy.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,cy.address_po_code AS billing_address_po_code
        FROM renewal c
        LEFT JOIN (company cy) ON (cy.company_id = c.company_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = cy.address_country)
        WHERE c.renewal_id = '{$renewal_id}'
       ORDER BY c.renewal_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);


        $quote_date   = $fn->getCPDate($company['date'], 'd/m/Y');
        $today      = date("d-m-Y");

       

         $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = '<br/>: '.$company['billing_address_street'];
        }


        $seal='';
        $signname='';

       if($company['apply_digital_signature'] == 1){
         $seal='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-left:1px solid #000; border-bottom:1px solid #000;"><img src="images/teamseal.jpg" width="80"/></td>';
         if($company['signature_name'] == "Jassim"){  
        $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/jassim.jpg" width="130" /></td>';
         } else if($company['signature_name'] == "Ibrahim"){  
            $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/ibrahim.jpg" width="130" /></td>';
             } else if($company['signature_name'] == "Wassim"){  
                $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/wasim.jpg" width="130" /></td>';
                 }else{
                    $signname='<td width="25%" border="1" style="font-size:15px;"></td>';
                 }
        }else{
            $seal='<td width="25%" border="1" style="font-size:15px;"></td>';
        }
       

        $tbl1 = '
      <table border="0" width="100%" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; text-decoration:underline;">Drain Flushing Contract</td>

            </tr>

             <tr>
                <td width="30%" style="font-size:16px; line-height:22px;">Date : '.$quote_date.' </td>
                <td width="70%" align="right" style="line-height:22px;">Ref :'.$company['ref_no'].' </td>

            </tr>

            <tr>
                <td width="100%" style="font-size:12px;">This contract is made on above date between:</td>

            </tr>

        </table>

         <table border="0" width="100%" cellpadding="4">
            <tr>
                <td width="50%" style="font-size:16px;">First Party:</td>

            </tr>

             <tr>
                <td width="70%" style="font-size:14px;  line-height:22px;">'.$company['company_name'].'<br/>'.$company['billing_address_flat'].' <br/>'.$company['billing_address_country'].' - '.$company['billing_address_po_code'].'</td>

            </tr>

            <tr>
                <td width="50%" style="font-size:16px;">Second Party:</td>

            </tr>

             <tr>
               <td width="100%" style="font-size:14px;  line-height:22px;"><b>M/s A TEAM INTERNATIONAL</b><br/>
                It’s Residence In Arbeed Building, Office no1, Floor 1, Street 169, Block 11, Hawally, Kuwait
                </td>
            </tr>

        </table>
            
          <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Preamble</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;"><b>Whereas,</b> first party appointing second party with drainage cleaning service at their '.$company['service'].' Kuwait”.</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;"><b>Now therefore,</b> in consideration of the premises, mutual understanding and obligations contained hereunder the parties do agreed as follows</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">1. The Preamble above and all appendices and annexure herewith shall be considered as the integral part of this agreement.</td>

            </tr>
        

        </table>
         <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;">2. <u>Scope of Work</u>:</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;">
                    A.  Cleaning of the drainage pipe line with pressure machine inside client unit at warehouse mall.<br/>
                    B.  Frequency of unit service once in two months <br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <table width="60%" border="1" align="center" cellpadding="4">
                    <tr><td>Price per visit in KD</td><td>Mall</td><td>Shop</td></tr>

                    <tr><td>'.$company['price_visit'].'</td><td> '.$company['mall'].' </td><td> '.$company['shop'].'</td></tr></table><br/>
                    C.  The contractor shall supply the client with the required manpower and machine to carryout the services.<br/>
                    D.  For any additional visits required client shall be intimate the contractor 72 hours prior to the scheduled date.<br/>
                    E.  This Agreement is Valid for a period of '.$company['valid_period'].' year.<br/>
                    F.  Client will pay for each drainage flushing using pressure machine '.$company['pay_machine'].' Within 7 days from the date of Invoice<br/>
                    G.  This agreement done in two copies signed by both parties and kept by each of the two parties thereof.

                    </td>

            </tr>
        
           

        </table>
        ';


        $tbl4=' 
        <table border="0" width="100%" cellpadding="4">
         

             <tr>
               
                <td style="line-height:22px;font-size:12px;">
                    <b>IN WITNESS WHEREOF</b> the parties here to have caused,this Agreement to be executed by the authorized representatives on the day and date written below.
                </td>
            </tr>
        </table>';

 
             $tbl5 = '       

                   <table border="0" width="100%" cellpadding="4">
                             

               <tr>

              <td width="50%" style="font-size:10px;">OnBehalf of Party1<br/>'.$company['behalf_party'].'<br/> </td>
            <td width="50%" border="1" style="font-size:15px;"></td>
               </tr>
                <br/>
               <tr>

              <td width="50%"  style="font-size:10px;"><b>OnBehalf of Party2</b><br/>
                M/s A TEAM International <br/>
                 </td>
                '.$seal.'
                '.$signname.'
               </tr>
              
                
             
        </table>';
        
       $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(9);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->ln(30);
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        //$pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . '-DrainFlush.pdf';
        //ob_end_clean();
        $pdf->Output($download_title, 'I');
    }




    /**
     *
     */
    function getPrintAMCMEPPdf() {
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
        $pdf->SetFooterMargin(6);
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

        $renewal_id = $fn->getReqParam('renewal_id');

        $SQL = "
      SELECT c.*
                ,cy.company_name
              ,cy.address_flat AS billing_address_flat
              ,cy.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,cy.address_po_code AS billing_address_po_code
        FROM renewal c
        LEFT JOIN (company cy) ON (cy.company_id = c.company_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = cy.address_country)
        WHERE c.renewal_id = '{$renewal_id}'
       ORDER BY c.renewal_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);


        $quote_date   = $fn->getCPDate($company['date'], 'd/m/Y');
        $today      = date("d-m-Y");

       

         $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = ',<br/>: '.$company['billing_address_street'];
        }
        $seal='';
        $signname='';

       if($company['apply_digital_signature'] == 1){
         $seal='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-left:1px solid #000; border-bottom:1px solid #000;"><img src="images/teamseal.jpg" width="80"/></td>';
         if($company['signature_name'] == "Jassim"){  
        $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/jassim.jpg" width="130" /></td>';
         } else if($company['signature_name'] == "Ibrahim"){  
            $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/ibrahim.jpg" width="130" /></td>';
             } else if($company['signature_name'] == "Wassim"){  
                $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/wasim.jpg" width="130" /></td>';
                 }else{
                    $signname='<td width="25%" border="1" style="font-size:15px;"></td>';
                 }
        }else{
            $seal='<td width="25%" border="1" style="font-size:15px;"></td>';
        }

        $tbl1 = '
      <table border="0" width="100%" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; text-decoration:underline; ">Maintenance Agreement contract for<br/>MEP Maintenance</td>

            </tr>

             <tr>
                <td width="30%" style="font-size:16px; line-height:22px;">Date : '.$quote_date.' </td>
                <td width="70%" align="right" style="line-height:22px;">Ref :'.$company['ref_no'].' </td>

            </tr>

            <tr>
                <td width="100%" style="font-size:12px;">This contract is made on above date between:</td>

            </tr>

        </table>

         <table border="0" width="100%" cellpadding="4">
            <tr>
                <td width="50%" style="font-size:16px;">First Party:</td>

            </tr>

             <tr>
                <td width="70%" style="font-size:14px;  line-height:22px;">'.$company['company_name'].',<br/>'.$company['billing_address_flat'].'</td>

            </tr>

            <tr>
                <td width="50%" style="font-size:16px;">Second Party:</td>

            </tr>

             <tr>
                <td width="100%" style="font-size:14px;  line-height:22px;"><b>M/s A TEAM INTERNATIONAL</b><br/>
                It’s Residence In Arbeed Building, Office no1, Floor 1, Street 169, Block 11, Hawally, Kuwait
                </td>
            </tr>

        </table>
            
          <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 1</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;">The Second Party is willing to maintain,</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">'.nl2br($company['willing_to_maintain']).'</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">Belonging for first party for Shops as mentioned below:</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">'.nl2br($company['shop_mention']).'</td>

            </tr>

        </table>
         <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 2</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;">1.    The second party conducts maintenance visits quarterly for above mentionshops</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">2.    The second party will provide maintenance report for each shop after inspection</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">3. This contract period is from ('.$company['start_date'].')and ending in ('.$company['end_date'].')</td>

            </tr>
           

        </table>
         <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 3</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;">'.$company['article_three'].'</td>

            </tr>
        </table>
        <table border="0" width="100%" cellpadding="4">
        <tr>
            <td  style="font-size:16px;text-decoration:underline;">Article 4</td>

        </tr>

         <tr>
           
            <td style="line-height:22px;font-size:12px;"> In case of any emergency First party will contact Second Party and Second Party will attend the same within 6 -18 hoursof time frame.<br/>
                Second Party Contact:  '.$company['contact_name'].':'.$company['mobile'].'
                </td>
        </tr>
    </table>
   

    
        ';


        $tbl4='    
        <table border="0" width="100%" cellpadding="4">
        <tr>
            <td  style="font-size:16px;text-decoration:underline;line-height:22px;">Article 5</td>
    
        </tr>
    
         <tr>
           
            <td style="font-size:12px;line-height:22px;">'.nl2br($company['article_five_content']).' </td>
        </tr>
    </table>
     <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 6</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;">'.$company['article_six'].'</td>
            </tr>
        </table>
        <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 7</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;">'.$company['article_seven'].'<br/><br/>
<b>IN WITNESS WHEREOF</b> the parties here to have caused,this Agreement to be executed by the authorized representatives on the day and date written below.
</td>
            </tr>
        </table>';

 
             $tbl5 = '       

                   <table border="0" width="100%" cellpadding="4">
                             

               <tr>

              <td width="50%" style="font-size:10px;">OnBehalf of Party1<br/>'.$company['behalf_party'].'<br/> </td>
            <td width="50%" border="1" style="font-size:15px;"></td>
               </tr>
                <br/>
               <tr>

              <td width="50%"  style="font-size:10px;"><b>OnBehalf of Party2</b><br/>
                M/s A TEAM International <br/>
                 </td>
              '.$seal.'
              '.$signname.'

               </tr>
              
                
             
        </table>';
        
       $pdf->writeHTML($tbl1, true, false, false, false, '');
                $pdf->ln(20);
               $pdf->AddPage();
        $pdf->writeHTML($tbl4, true, false, false, false, '');
    $pdf->ln(9);
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        //$pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . '-AmcMep.pdf';
        //ob_end_clean();
        $pdf->Output($download_title, 'I');
    }



   /**
     *
     */
    function getPrintAcMaintenancePdf() {
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
        $pdf->SetFooterMargin(6);
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

        $renewal_id = $fn->getReqParam('renewal_id');

        $SQL = "
       SELECT c.*
                ,cy.company_name
              ,cy.address_flat AS billing_address_flat
              ,cy.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,cy.address_po_code AS billing_address_po_code
        FROM renewal c
        LEFT JOIN (company cy) ON (cy.company_id = c.company_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = cy.address_country)
        WHERE c.renewal_id = '{$renewal_id}'
       ORDER BY c.renewal_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);


        $quote_date   = $fn->getCPDate($company['date'], 'd/m/Y');
        $today      = date("d-m-Y");

       
 $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = ',<br/>: '.$company['billing_address_street'];
        }
        $seal='';
        $signname='';

       if($company['apply_digital_signature'] == 1){
         $seal='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-left:1px solid #000; border-bottom:1px solid #000;"><img src="images/teamseal.jpg" width="80"/></td>';
         if($company['signature_name'] == "Jassim"){  
        $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/jassim.jpg" width="130" /></td>';
         } else if($company['signature_name'] == "Ibrahim"){  
            $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/ibrahim.jpg" width="130" /></td>';
             } else if($company['signature_name'] == "Wassim"){  
                $signname='<td width="25%" border="0" style="font-size:15px;border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000;"><img src="images/wasim.jpg" width="130" /></td>';
                 }else{
                    $signname='<td width="25%" border="1" style="font-size:15px;"></td>';
                 }
        }else{
            $seal='<td width="25%" border="1" style="font-size:15px;"></td>';
        }
       

        $tbl1 = '
      <table border="0" width="100%" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; text-decoration:underline; ">Maintenance Agreement contract for<br/>AC Maintenance</td>

            </tr>

             <tr>
                <td width="30%" style="font-size:16px; line-height:22px;">Date : '.$quote_date.' </td>
                <td width="70%" align="right" style="line-height:22px;">Ref :'.$company['ref_no'].' </td>

            </tr>

            <tr>
                <td width="100%" style="font-size:12px;">This contract is made on above date between:</td>

            </tr>

        </table>

         <table border="0" width="100%" cellpadding="4">
            <tr>
                <td width="50%" style="font-size:16px;">First Party:</td>

            </tr>

             <tr>
                <td width="70%" style="font-size:14px;  line-height:22px;">'.$company['company_name'].',<br/>'.$company['billing_address_flat'].'</td>

            </tr>

            <tr>
                <td width="50%" style="font-size:16px;">Second Party:</td>

            </tr>

             <tr>
                <td width="100%" style="font-size:14px;  line-height:22px;"><b>M/s A TEAM INTERNATIONAL</b><br/>
                It’s Residence In Arbeed Building, Office no1, Floor 1, Street 169, Block 11, Hawally, Kuwait
                </td>

            </tr>

        </table>
            
          <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 1</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;">The Second Party is willing to maintain,</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">'.nl2br($company['willing_to_maintain']).'</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">Belonging for first party for Shops as mentioned below:</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">'.nl2br($company['shop_mention']).'</td>

            </tr>

        </table>
         <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 2</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;">1.    The second party conducts maintenance visits quarterly for above mentionshops</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">2.    The second party will provide maintenance report for each shop after inspection</td>

            </tr>
             <tr>
               
                <td style="line-height:22px;font-size:12px;">3. This contract period is from ('.$company['start_date'].')and ending in ('.$company['end_date'].')</td>

            </tr>
           

        </table>
         <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 3</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;">'.$company['article_three'].'</td>

            </tr>
        </table>
        
        <table border="0" width="100%" cellpadding="4">
        <tr>
            <td  style="font-size:16px;text-decoration:underline;">Article 4</td>

        </tr>

         <tr>
           
            <td style="line-height:22px;font-size:12px;"> In case of any emergency First party will contact Second Party and Second Party will attend the same within 6 -18 hoursof time frame.<br/>
                Second Party Contact:  '.$company['contact_name'].':'.$company['mobile'].'
                </td>
        </tr>
    </table>
    
        ';


        $tbl4='    
        <table border="0" width="100%" cellpadding="4">
    <tr>
        <td  style="font-size:16px;text-decoration:underline;line-height:22px;">Article 5</td>

    </tr>

     <tr>
       
        <td style="font-size:12px;line-height:22px;">'.nl2br($company['article_five_content']).' </td>
    </tr>
</table>
   <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 6</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;">'.$company['article_six'].'</td>
            </tr>
        </table>
        <table border="0" width="100%" cellpadding="4">
            <tr>
                <td  style="font-size:16px;text-decoration:underline;">Article 8</td>

            </tr>

             <tr>
               
                <td style="line-height:22px;font-size:12px;">'.$company['article_seven'].'<br/><br/>
<b>IN WITNESS WHEREOF</b> the parties here to have caused,this Agreement to be executed by the authorized representatives on the day and date written below.
</td>
            </tr>
        </table>';

 
             $tbl5 = '       

                   <table border="0" width="100%" cellpadding="4">
                             

               <tr>

              <td width="50%" style="font-size:10px;">OnBehalf of Party1<br/>'.$company['behalf_party'].'<br/> </td>
            <td width="50%" border="1" style="font-size:15px;"></td>
               </tr>
                <br/>
               <tr>

              <td width="50%"  style="font-size:10px;"><b>OnBehalf of Party2</b><br/>
                M/s A TEAM International<br/>
                 </td>
              '.$seal.'
              '.$signname.'

               </tr>
              
                
             
        </table>';
        
       $pdf->writeHTML($tbl1, true, false, false, false, '');
                $pdf->ln(20);
               $pdf->AddPage();
        $pdf->writeHTML($tbl4, true, false, false, false, '');
    $pdf->ln(9);
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        //$pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . '-Ac Maintenance.pdf';
        //ob_end_clean();
        $pdf->Output($download_title, 'I');
    }


     /**
     *
     */
    function getRightPanel($row) {
        $cpCfg           = Zend_Registry::get('cpCfg');
        $tv              = Zend_Registry::get('tv');
        $fn              = Zend_Registry::get('fn');
        $displayLinkData = Zend_Registry::get('displayLinkData');
        $media           = Zend_Registry::get('media');
        $comment         = getCPPluginObj('common_comment');
        $db              = Zend_Registry::get('db');
        
        $record_id       = $fn->getIssetParam($row, 'opportunity_id');
        $wProjectQuote ="";
        
        $wProjectQuote = getCPWidgetObj('enggCrm_projectQuoteRenewal')->view->getAddQuoteFormListView($row['opportunity_id'], $row['renewal_id']);

        $wProjectQuoteRenewal ="";
        
        $wProjectQuoteRenewal = getCPWidgetObj('enggCrm_projectFinanceRenewal')->view->getInvoiceReceiptPortalDetails($row['renewal_id']);

        $wProjectWarrantyRenewal ="";
        
        
        $wProjectWarrantyRenewal = getCPWidgetObj('enggCrm_projectWarrantyRenewal')->view->getProjectWarrantyPortal($row['renewal_id']);
        $rowComp = $fn->getRecordByCondition('order', "renewal_id = '{$row['renewal_id']}'");

        $orderUrl = "/admin/index.php?_topRm=finance&module=enggCrm_order&_action=edit&order_id={$rowComp['order_id']}";


        $text = "
        <div id='tabs' class='mb20 noPadding col-md-12 col-sm-12 col-xs-12 ui-tabs ui-widget ui-widget-content ui-corner-all'>
            <ul class='ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all'>
                <li class='first active ui-state-default ui-corner-top'>
                    <a class='dropdown-item tabButtonLi quoteLinked ui-tabs-anchor' data-toggle='tab' href='#tabs-1' aria-expanded='true'>Quotation</a>
                </li>

                <li class='second ui-state-default ui-corner-top'>
                    <a class='dropdown-item tabButtonLi renewalLinked ui-tabs-anchor' data-toggle='tab' href='#tabs-2'>Service</a>
                </li>

                <li class='third ui-state-default ui-corner-top'>
                    <a class='dropdown-item tabButtonLi financeLinked ui-tabs-anchor' data-toggle='tab' href='#tabs-3'>Finance</a>
                </li>
                <li class='fourth ui-state-default ui-corner-top'>
                    <a class='dropdown-item tabButtonLi warrantyLinked ui-tabs-anchor' data-toggle='tab' href='#tabs-4'>Warranty</a>
                </li>
                  <li class='fifth ui-state-default ui-corner-top'>
                    <a class='dropdown-item tabButtonLi shopLinked ui-tabs-anchor' data-toggle='tab' href='#tabs-5'>Shop</a>
                </li>
                 <li class='sixth ui-state-default ui-corner-top'>
                    <a class='dropdown-item tabButtonLi addStaffAndEmployee ui-tabs-anchor' data-toggle='tab' href='#tabs-6'>Attachment</a>
                </li>
                <div class=''>
                <a href= {$orderUrl} target='_blank' style='color:#000000;' class='button'>
                     <u>Go to Finance</u>
                </a>
            </div>
               

            </ul>

            <div id='tabs-1' aria-labelledby='quoteLinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade active in noPadding ui-tabs-panel ui-widget-content ui-corner-bottom'>
              <div class='mb30 mt10 noPadding col-md-12 col-sm-12 col-xs-12' id='quoteLinkPortal'>
                  {$wProjectQuote}
              </div>
            </div>

            <div id='tabs-2' aria-labelledby='renewalinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding ui-tabs-panel ui-widget-content ui-corner-bottom' aria-labelledby='ui-id-2'>
               
                                    <div id='renewalHistoryPortal'>

                {$this->getRenewalHistoryPortal($row['renewal_id'])}
                            </div>
                </div>
            
                  <div id='tabs-3' aria-labelledby='financeLinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade active in noPadding ui-tabs-panel ui-widget-content ui-corner-bottom'>
              <div class='mb30 mt10 noPadding col-md-12 col-sm-12 col-xs-12' id='financeLinkPortal'>
                  {$wProjectQuoteRenewal}
              </div>
            </div>

             <div id='tabs-4' aria-labelledby='warrantyLinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade active in noPadding ui-tabs-panel ui-widget-content ui-corner-bottom'>
              <div class='mb30 mt10 noPadding col-md-12 col-sm-12 col-xs-12' id='warraLinkPortal'>
                  {$wProjectWarrantyRenewal}
              </div>
            </div>

              <div id='tabs-5' aria-labelledby='shopinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding ui-tabs-panel ui-widget-content ui-corner-bottom'>
               
                                    <div id='shopHistoryPortal'>

                {$this->getShopHistoryPortal($row['renewal_id'])}
                            </div>
                </div>

             <div id='tabs-6' aria-labelledby='rightPanelsLinkedPortals-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade active in noPadding '>
                <div class='floatBox row'>
                    <div class='col-md-12 col-sm-12 col-xs-12 noPadding'> 
                       
                        <div class='col-md-6 col-sm-6 col-xs-12'>
                            {$media->getRightPanelMediaDisplay('Attachments', 'enggCrm_renewal', 'attachment', $row)}
                        </div>
                    </div>
                </div>
                <div class='floatBox row'>
                    <div class='col-md-12 col-sm-12 col-xs-12 noPadding'> 
                        <div class='col-md-6 col-sm-6 col-xs-12'>
                            {$comment->getView(array(
                                 'roomName' => 'enggCrm_renewal'
                                ,'recordId' => $record_id
                            ))}
                        </div>
                    </div>
                </div>
            </div>
             
        </div>
        ";

        return $text;
    }

    function getAddActualCharge() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $vehicle_id     = $fn->getReqParam('vehicle_id');
        $amount = $fn->getReqParam('amount');

        $today = date("Y-m-d");
        
        $rowVehicle     = $fn->getRecordRowByID('vehicle', 'vehicle_id', $vehicle_id);

        $liters = "<input type='text' value='' id='liters' class='text lineItemLiters' name='liters'>";
        $amount      = "<input type='text' value='' id='amount' class='text lineItemAmount' name='amount'>";
        /*$date = "<input type='text' allowEdit='1' name='date' class='fld_date'
                    id='fld_date' value='' />";*/
        $date = $formObj->getDateRow('', 'date', $today);

        $rows = "
        <tr>
            <td>{$date}</td>
            <td>{$amount}</td>
            <td>{$liters}</td>
        </tr>
        ";          

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th class='txtCenter'>Date</th>
            <th class='txtCenter'>Amount</th>
            <th>Liters</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=main&module=enggCrm_vehicle&_spAction=actualchargeSubmit&showHTML=0";
        $expNoEdit = array('isEditable' => 0);

        $SQL = "
        SELECT vl.*
        FROM `vehicle_fuel` vl
        WHERE vl.vehicle_id = {$vehicle_id}
         
        ORDER BY vl.vehicle_fuel_id DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $rowActual = '';
        while ($row = $db->sql_fetchrow($result)) {
            $date = $fn->getCPDate($row['date'], 'd-m-Y');
            $rowActual .= "
            <tr>
                <td>{$date}</td>
                <td>{$row['amount']}</td>
                <td>{$row['liters']}</td>
            </tr>
            ";
        }
        
        $text = "
        <form id='actualChargeForm' class='actualChargeForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='actualChargesTable'>
                <tr><th colspan='3'>Fuel</th></tr>
                {$header}
                {$rows}
                {$rowActual}
            </table>
            <input type='hidden' name='vehicle_id' value='{$vehicle_id}' />
           
        </form>
         ";


        return $text;
    }

    function getAddRenewalDate() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $vehicle_id     = $fn->getReqParam('vehicle_id');
        $renewal_date = $fn->getReqParam('renewal_date');
         $today = date("Y-m-d");

        $rowVehicle     = $fn->getRecordRowByID('vehicle', 'vehicle_id', $vehicle_id);

       $insurance_date = $formObj->getDateRow('', 'insurance_date', $today);
        $insurance_amount      = "<input type='text' value='' id='insurance_amount' class='text lineItemInsuranceAmount' name='insurance_amount'>";
        /*$date = "<input type='text' allowEdit='1' name='date' class='fld_date'
                    id='fld_date' value='' />";*/
        $renewal_date = $formObj->getDateRow('', 'renewal_date', $today);
        $rows = "
        <tr>
            <td>{$insurance_date}</td>
            <td>{$insurance_amount}</td>
            <td>{$renewal_date}</td>
        </tr>
        ";          

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th class='txtCenter'>Insurance_Date</th>
            <th class='txtCenter'>Amount</th>
            <th>Renewal_Date</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=main&module=enggCrm_vehicle&_spAction=renewaldateSubmit&showHTML=0";
        $expNoEdit = array('isEditable' => 0);

        $SQL = "
        SELECT vi.*
        FROM `vehicle_insurance` vi
        WHERE vi.vehicle_id = {$vehicle_id}
         
        ORDER BY vi.vehicle_insurance_id DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $rowActual = '';
        while ($row = $db->sql_fetchrow($result)) {
            $insurance_date = $fn->getCPDate($row['insurance_date'], 'd-m-Y');
            $renewal_date = $fn->getCPDate($row['renewal_date'], 'd-m-Y');
           
            $rowActual .= "
            <tr>
                <td>{$insurance_date}</td>
                <td>{$row['insurance_amount']}</td>
                <td>{$renewal_date}</td>
            </tr>
            ";
        }
        
        $text = "
        <form id='actualChargeForm' class='actualChargeForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='actualChargesTable'>
                <tr><th colspan='3'>Insurance</th></tr>
                {$header}
                {$rows}
                {$rowActual}
            </table>
            <input type='hidden' name='vehicle_id' value='{$vehicle_id}' />
           
        </form>
         ";


        return $text;
    }

    function getAddService() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $vehicle_id     = $fn->getReqParam('vehicle_id');
        $amount = $fn->getReqParam('amount');

        $today = date("Y-m-d");
        $rowVehicle     = $fn->getRecordRowByID('vehicle', 'vehicle_id', $vehicle_id);

         $description = "<textarea value='' id='description' class='text lineItemDescription' name='description'></textarea>";
        $amount      = "<input type='text' value='' id='amount' class='text lineItemAmount' name='amount'>";
        /*$date = "<input type='text' allowEdit='1' name='date' class='fld_date'
                    id='fld_date' value='' />";*/
        $date = $formObj->getDateRow('', 'date', $today);

        $rows = "
        <tr>
            <td>{$date}</td>
            <td>{$amount}</td>
            <td>{$description}</td>
        </tr>
        ";          

        $header ="
        <tr style='background-color:#EAEAE8;'>
            <th class='txtCenter'>Date</th>
            <th class='txtCenter'>Amount</th>
            <th>description</th>
        </tr>
        ";

        $formAction = "index.php?_topRm=main&module=enggCrm_vehicle&_spAction=serviceSubmit&showHTML=0";
        $expNoEdit = array('isEditable' => 0);

        $SQL = "
        SELECT vs.*
        FROM `vehicle_service` vs
        WHERE vs.vehicle_id = {$vehicle_id}
         
        ORDER BY vs.vehicle_service_id DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);
        $rowActual = '';
        while ($row = $db->sql_fetchrow($result)) {
            $date = $fn->getCPDate($row['date'], 'd-m-Y');
            $rowActual .= "
            <tr>
                <td>{$date}</td>
                <td>{$row['amount']}</td>
                <td>{$row['description']}</td>
            </tr>
            ";
        }
        
        $text = "
        <form id='actualChargeForm' class='actualChargeForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='actualChargesTable'>
                <tr><th colspan='3'>Service</th></tr>
                {$header}
                {$rows}
                {$rowActual}
            </table>
            <input type='hidden' name='vehicle_id' value='{$vehicle_id}' />
           
        </form>
         ";


        return $text;
    }
   

    /**
     *
     */
    function getRenewalHistoryPortalOld($renewal_id = '') {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($renewal_id == ''){
            $renewal_id = $fn->getReqParam('renewal_id');
        }

        $SQL = "
        SELECT pm.*
        FROM renewal_chechlist_history pm
        WHERE pm.renewal_id = {$renewal_id}
        ORDER BY pm.renewal_chechlist_history_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
            if($row['monthly'] == 1){
            $monthlyRow = "<input class='monthly' type='checkbox' name='monthly' value='{$row['monthly']}' checked><input  type='hidden' name='renewal_id' value='{$row['renewal_id']}'><input  type='hidden' name='renewal_chechlist_history_id' value='{$row['renewal_chechlist_history_id']}'>";
        }else{
            $monthlyRow = "<input class='monthly' type='checkbox' name='monthly' value='{$row['monthly']}'><input  type='hidden' name='renewal_id' value='{$row['renewal_id']}'><input  type='hidden' name='renewal_chechlist_history_id' value='{$row['renewal_chechlist_history_id']}'>";
        }
         if($row['quaterly'] == 1){
            $quaterlyRow = "<input class='quaterly' type='checkbox' name='quaterly' value='{$row['quaterly']}' checked><input  type='hidden' name='renewal_id' value='{$row['renewal_id']}'><input  type='hidden' name='renewal_chechlist_history_id' value='{$row['renewal_chechlist_history_id']}'>";
        }else{
            $quaterlyRow = "<input class='quaterly' type='checkbox' name='quaterly' value='{$row['quaterly']}'><input  type='hidden' name='renewal_id' value='{$row['renewal_id']}'><input  type='hidden' name='renewal_chechlist_history_id' value='{$row['renewal_chechlist_history_id']}'>";
        }
        if($row['annually'] == 1){
            $annuallyRow = "<input class='annually' type='checkbox' name='annually' value='{$row['annually']}' checked><input  type='hidden' name='renewal_id' value='{$row['renewal_id']}'><input  type='hidden' name='renewal_chechlist_history_id' value='{$row['renewal_chechlist_history_id']}'>";
        }else{
            $annuallyRow = "<input class='annually' type='checkbox' name='annually' value='{$row['annually']}'><input  type='hidden' name='renewal_id' value='{$row['renewal_id']}'><input  type='hidden' name='renewal_chechlist_history_id' value='{$row['renewal_chechlist_history_id']}'>";
        }


            $rows .= "
            <tr class='classSubjectCheckbox1'>
                <td>
                  {$row['title']}
                </td>
                <td>{$monthlyRow}</td>
                <td>{$quaterlyRow}</td>
                <td>{$annuallyRow}</td>
                <td><input type='text' name='remarks' value='{$row['remarks']}' /></td>
            </tr>
            ";
        }


        $text = "
        <div id='materialsPortal' class='linkPortalWrapper'>
            <table class ='list'>
                <thead>
                    <tr>
                        <th colspan='9' align='left' class='rightPanelHeading'>
                          <div class='float_left rightPanelHeading'>
                              Renewal Checklist
                          </div>
                         
                        
                        </th>
                    </tr>
                    <tr>
                        <th>Title</th>
                        <th>Monthly</th>
                        <th>Quaterly</th>
                        <th>Annually</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody class='renewalDetailRow'>
                    {$rows}
                </tbody>
            </table>
        </div>
        ";

        return $text;
    }

     /**
     *
     */
    function getShopHistoryPortal( $renewal_id = '') {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($renewal_id == ''){
            $renewal_id = $fn->getReqParam('renewal_id');
        }
     

        $projRec    = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);
        

        $SQL = "
        SELECT q.*
        FROM `shop_renewal` q
        LEFT JOIN (renewal p) ON (p.renewal_id = q.renewal_id)
        WHERE p.renewal_id = {$renewal_id}
        ORDER BY q.shop_renewal_id DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        $subtotalValue = 0;
        while ($row = $db->sql_fetchrow($result)) {

           
              $quoteActions = '';
              $editForQuote = "index.php?_topRm=main&module=enggCrm_renewal&_spAction=editForShop&renewal_id={$renewal_id}&shop_renewal_id={$row['shop_renewal_id']}&showHTML=0";



              $add_image = $cpCfg['cp.localPath']."images/add.png";
              $print_image = $cpCfg['cp.localPath']."images/icon-print.png";
              $edit_image = $cpCfg['cp.localPath']."images/edit.png";

              $quoteActions ="
                  <div class='float_box clearfix'>
                      <div class='float_left'>
                          <a class='editForShopRenewal' href='{$editForQuote}' title='Edit Quote'><img src='{$edit_image}' class='icon'></a>
                      </div>                   
                  	<div class='float_left'>
                    	<a class='deleteShopRenewal' href='#'  renewal_id='{$renewal_id}' shop_renewal_id='{$row['shop_renewal_id']}'>
                        	<img src='/cmspilotv30/CP/admin/images/icons/btn_remove.png'>
                    	</a>
                	</div>
                  </div>
                ";         
         
              $rows .= "
              <tbody class='quoteDetailRow'>
                  <tr class='addQuoteRow  '>
                   
                        <td data-label='Revision'><p>{$row['shop']}</p></td>
                  
                      <td data-label='Quote Status' class='quoteStatusTd'>{$row['location']}</td>
                     
                      <td data-label='Action'>{$quoteActions}</td>
                  </tr>
              </tbody>
              ";

          

        }

          $text = '';

       
          $text = "
          <div class='float_box mt10 mb10'>
            <a id='addShopMultipleLineItem' class='btn btn-primary' renewal_id='{$renewal_id}'>Add Shop & Location</a>
          </div>
          ";
                 
            $text .= "
            
            <div id='quotesPortal' class='linkPortalWrapper table-responsive'>
                <table class ='list'>
                    <thead>
                        <tr>
                            <th colspan='9' align='left' class='rightPanelHeading'>
                             Shop
                              
                            </th>
                        </tr>
                        <tr>
                            <th scope='col'>Shop</th>
                            <th scope='col'>Location</th>
                          
                            <th scope='col'>Action</th>
                        </tr>
                    </thead>
                        {$rows}
                </table>
            </div>
            ";
          

          return $text;
    }

    function getEditForShop() {
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil  = Zend_Registry::get('cpUtil');

        $shop_renewal_id       = $fn->getReqParam('shop_renewal_id');
        $renewal_id     = $fn->getReqParam('renewal_id');


        $rowQuote      = $fn->getRecordRowByID('shop_renewal', 'shop_renewal_id', $shop_renewal_id);
        $rowProject    = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);

        $formActionEditForQuote = "index.php?_topRm=main&module=enggCrm_renewal&_spAction=editForShopSubmit&lnkRoom={$tv['lnkRoom']}&shop_renewal_id={$rowQuote['shop_renewal_id']}&renewal_id={$renewal_id}&showHTML=0";

        $sqlTimesheetType = $fn->getValueListSQL('timesheetType');

        $expVl           = array('sqlType' => 'OneField');
        $expNoEdit       = array('isEditable' => 0);
        $expHideFirstOpt = array('hideFirstOption' => true);
        $expQuoteDate    = array('maxDate' => date('Y-m-d'), 'yearEnd' => date('Y'));

   
       
        $text = "
        <form id='editForShopRenewal' class='yform columnar editQuote' method='post' action='{$formActionEditForQuote}'>
            <fieldset>
                <table width='100%'>
                    <tr>
                        <td>{$formObj->getTBRow('Shop', 'shop', $rowQuote['shop'])}</td>
                        <td>{$formObj->getTBRow('Location', 'location', $rowQuote['location'])}</td>
                    </tr>
                 
                  
                </table>
                <input type='hidden' name='renewal_id' value='{$renewal_id}' />
                <input type='hidden' name='shop_renewal_id' value='{$shop_renewal_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    

    function getAddShopLineItemRecord($index = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $index = $fn->getReqParam('index');

      // Generate 8 rows
      $shop      = "<textarea type='text' value='' id='shop_$i' class='text lineItemShop' name='shop[]'></textarea>";
            
      $location     = "<textarea type='text' value='' id='location_$i' class='text lineItemLocation' name='location[]'></textarea>";

          
            $clear         = "<td class='text'><a class='clearLineItem'><u>Clear</u></a></td>";
          
            
    
            $rows = "
            <tr>
                   <td>{$shop}</td>
                <td>{$location}</td>
                
                {$clear}
            </tr>";
        
        return $rows;
    }
    /**
     *
     */
    function getAddShopMultipleLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $renewal_id = $fn->getReqParam('renewal_id');
    
        $rows = '';
        for ($i = 0; $i < 8; $i++) {  // Generate 8 rows
            $shop      = "<textarea type='text' value='' id='shop_$i' class='text lineItemShop' name='shop[]'></textarea>";
            
            $location     = "<textarea type='text' value='' id='location_$i' class='text lineItemLocation' name='location[]'></textarea>";

            $clear         = "<td class='text'><a class='clearLineItem'><u>Clear</u></a></td>";
          


    
            $rows .= "
            <tr>
                <td>{$shop}</td>
                <td>{$location}</td>
                
                {$clear}
            </tr>";
        }
    
        $newRow = "
        <a class='addRow btn btn-primary mb10' renewal_id='{$renewal_id}'>Add Line Item</a>";
    
        $header = "
        <tr>
            {$newRow}
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Shop</th>
        
            <th>Location</th>
            <th width='3%'></th>
        </tr>";
    
        $formAction = "index.php?_topRm=main&module=enggCrm_renewal&_spAction=addShopMultipleLineItemSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);
    
        $text = "
        <form id='addShopMultipleLineItemForm' class='addShopMultipleLineItemForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='renewal_id' value='{$renewal_id}' />
        </form>";
    
        return $text;
    }
    

       /**
     *
     */
    function getRenewalHistoryPortal( $renewal_id = '') {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($renewal_id == ''){
            $renewal_id = $fn->getReqParam('renewal_id');
        }
     

        $projRec    = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);
        

        $SQL = "
        SELECT q.*
        FROM `service_renewal` q
        LEFT JOIN (renewal p) ON (p.renewal_id = q.renewal_id)
        WHERE p.renewal_id = {$renewal_id}
        ORDER BY q.service_renewal_id DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        $subtotalValue = 0;
        while ($row = $db->sql_fetchrow($result)) {

              $actual_date   = $fn->getCPDate($row['actual_date'], 'd-m-Y');
              $schedule_date   = $fn->getCPDate($row['schedule_date'], 'd-m-Y');

              $quoteActions = '';
              $editForQuote = "index.php?_topRm=main&module=enggCrm_renewal&_spAction=editForQuote&renewal_id={$renewal_id}&service_renewal_id={$row['service_renewal_id']}&showHTML=0";



              $add_image = $cpCfg['cp.localPath']."images/add.png";
              $print_image = $cpCfg['cp.localPath']."images/icon-print.png";
              $edit_image = $cpCfg['cp.localPath']."images/edit.png";

              $quoteActions ="
                  <div class='float_box clearfix'>
                      <div class='float_left'>
                          <a class='editForServiceRenewal' href='{$editForQuote}' title='Edit Quote'><img src='{$edit_image}' class='icon'></a>
                      </div>
                   
                  </div>
                  ";
          

                  $rows .= "
                  <tbody class='quoteDetailRow'>
                      <tr class='addQuoteRow'>
                          <td data-label='Revision'><p>{$row['schedule']}</p></td>
                          <td data-label='Schedule Date'>{$schedule_date}</td>
                          <td data-label='Quote Date'>{$actual_date}</td>
                          <td data-label='Service Due'><p>" . ($row['service_due'] == 1 ? 'Yes' : 'No') . "</p></td>
                          <td data-label='Quote Status' class='quoteStatusTd'>{$row['remarks']}</td>
                          <td data-label='Action'>{$quoteActions}</td>
                      </tr>
                  </tbody>";
                  
        }

          $text = '';

       
          $text = "
          <div class='float_box mt10 mb10'>
            <a id='addServiceMultipleLineItem' class='btn btn-primary' renewal_id='{$renewal_id}'>Add Service</a>
          </div>
          ";
                 
            $text .= "
            
            <div id='quotesPortal' class='linkPortalWrapper table-responsive'>
                <table class ='list'>
                    <thead>
                        <tr>
                            <th colspan='9' align='left' class='rightPanelHeading'>
                             Service
                              
                            </th>
                        </tr>
                        <tr>
                            <th scope='col'>Schedule</th>
                             <th scope='col'>Schedule Date</th>
                            <th scope='col'>Actual Date</th>
                             <th scope='col'>Service Due</th>
                            <th scope='col'>Remarks</th>
                          
                            <th scope='col'>Action</th>
                        </tr>
                    </thead>
                        {$rows}
                </table>
            </div>
            ";
          

          return $text;
    }

    

    function getAddServiceLineItemRecord($index = '') {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $index = $fn->getReqParam('index');

      // Generate 8 rows
            $schedule      = "<textarea type='text' value='' id='schedule_$index' class='text lineItemSchedule' name='schedule[]'></textarea>";
            $scheduledate  = "<input type='date' id='scheduledate_$index' class='text lineItemScheduleDate' name='schedule_date[]'>";
            $actualdate    = "<input type='date' id='actualdate_$index' class='text lineItemActualDate' name='actual_date[]'>";
            $remarks       = "<textarea type='text' value='' id='remarks_$index' class='text lineItemRemarks' name='remarks[]'></textarea>";
            $clear         = "<td class='text'><a class='clearLineItem'><u>Clear</u></a></td>";
          

            $servicedue = "
            <div class='lineItemServiceDue'>
                <label><input type='radio' class='lineItemServiceDue' name='service_due_{$index}' value='1'> Yes</label>
                <label><input type='radio' class='lineItemServiceDue' name='service_due_{$index}' value='0'> No</label>
            </div>";
            
    
            $rows = "
            <tr>
                <td>{$schedule}</td>
                <td>{$scheduledate}</td>
                <td>{$actualdate}</td>
                <td>{$servicedue}</td>
                <td>{$remarks}</td>
                {$clear}
            </tr>";
        
        return $rows;
    }
    /**
     *
     */
    function getAddServiceMultipleLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $renewal_id = $fn->getReqParam('renewal_id');
    
        $rows = '';
        for ($i = 0; $i < 8; $i++) {  // Generate 8 rows
            $schedule      = "<textarea type='text' value='' id='schedule_$i' class='text lineItemSchedule' name='schedule[]'></textarea>";
            $scheduledate  = "<input type='date' id='scheduledate_$i' class='text lineItemScheduleDate' name='schedule_date[]'>";
            $actualdate    = "<input type='date' id='actualdate_$i' class='text lineItemActualDate' name='actual_date[]'>";
            $remarks       = "<textarea type='text' value='' id='remarks_$i' class='text lineItemRemarks' name='remarks[]'></textarea>";
            $clear         = "<td class='text'><a class='clearLineItem'><u>Clear</u></a></td>";
          

                $servicedue = "
    <div class='lineItemServiceDue'>
        <label><input type='radio' class='lineItemServiceDue' name='service_due[{$i}]' value='1'> Yes</label>
        <label><input type='radio' class='lineItemServiceDue' name='service_due[{$i}]' value='0'> No</label>
    </div>";

    
            $rows .= "
            <tr>
                <td>{$schedule}</td>
                <td>{$scheduledate}</td>
                <td>{$actualdate}</td>
                <td>{$servicedue}</td>
                <td>{$remarks}</td>
                {$clear}
            </tr>";
        }
    
        $newRow = "
        <a class='addRow btn btn-primary mb10' renewal_id='{$renewal_id}'>Add Line Item</a>";
    
        $header = "
        <tr>
            {$newRow}
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Schedule</th>
            <th>Schedule Date</th>
            <th>Actual Date</th>
            <th>Service Due</th>
            <th>Remarks</th>
            <th width='3%'></th>
        </tr>";
    
        $formAction = "index.php?_topRm=main&module=enggCrm_renewal&_spAction=addServiceMultipleLineItemSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);
    
        $text = "
        <form id='addServiceMultipleLineItemForm' class='addServiceMultipleLineItemForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='renewal_id' value='{$renewal_id}' />
        </form>";
    
        return $text;
    }
    



    function getEditForQuote() {
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil  = Zend_Registry::get('cpUtil');

        $service_renewal_id       = $fn->getReqParam('service_renewal_id');
        $renewal_id     = $fn->getReqParam('renewal_id');


        $rowQuote      = $fn->getRecordRowByID('service_renewal', 'service_renewal_id', $service_renewal_id);
        $rowProject    = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);

        $formActionEditForQuote = "index.php?_topRm=main&module=enggCrm_renewal&_spAction=editForQuoteSubmit&lnkRoom={$tv['lnkRoom']}&service_renewal_id={$rowQuote['service_renewal_id']}&renewal_id={$renewal_id}&showHTML=0";

        $sqlTimesheetType = $fn->getValueListSQL('timesheetType');

        $expVl           = array('sqlType' => 'OneField');
        $expNoEdit       = array('isEditable' => 0);
        $expHideFirstOpt = array('hideFirstOption' => true);
        $expQuoteDate    = array('maxDate' => date('Y-m-d'), 'yearEnd' => date('Y'));

   
       
        $text = "
        <form id='editForRenewal' class='yform columnar editQuote' method='post' action='{$formActionEditForQuote}'>
            <fieldset>
                <table width='100%'>
                    <tr>
                        <td>{$formObj->getDateRow('Schedule Date', 'schedule_date',$rowQuote['schedule_date'], $expQuoteDate)}</td>
                        <td>{$formObj->getDateRow('Actual Date', 'actual_date',$rowQuote['actual_date'], $expQuoteDate)}</td>
                        <td>{$formObj->getTBRow('Schedule', 'schedule', $rowQuote['schedule'])}</td>
                    </tr>
                    <tr>
                        <td>{$formObj->getYesNoRRow('Service Due','service_due', $rowQuote['service_due'])}
                    </tr>
                
                     <tr>
                        <td colspan='3'>                          
                        <label>Remarks</label>
                        {$formObj->getHTMLEditor('Remarks', 'remarks', $rowQuote['remarks'])}</td>
                    </tr>
                  
                </table>
                <input type='hidden' name='renewal_id' value='{$renewal_id}' />
                <input type='hidden' name='service_renewal_id' value='{$service_renewal_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }


    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

       
        $company_id         = $fn->getReqParam('company_id');

        //==================================================================//
        

        $sqlCompany = "
        SELECT DISTINCT a.company_id
              ,a.company_name
        FROM company a
        ORDER BY company_name
        ";

        $text = "
        <td>
        <select name='company_id'>
            <option value=''>Company</option>
            {$dbUtil->getDropDownFromSQLCols2($db, $sqlCompany, $company_id)}
        </select>
    </td>
       
        ";

        return $text;
    }

}