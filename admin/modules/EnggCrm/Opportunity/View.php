<?
class CPL_Admin_Modules_EnggCrm_Opportunity_View extends CP_Admin_Modules_EnggCrm_Opportunity_View
{
    /**
     *
     */
    function getList($dataArray) {
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $text = '';
        $rows = '';
        $rowCounter = 0;

        foreach ($dataArray as $row){

            $SQL = "
            SELECT q.*                 
            FROM `quote` q
            WHERE q.opportunity_id ={$row['opportunity_id']}
            ORDER BY quote_code DESC
            ";
            $result  = $db->sql_query($SQL);
            $row1 = $db->sql_fetchrow($result);
            if (!is_array($row1)) {
                $row1 = array();
            }
            $row1 += array(
                'quote_id' => '',
                'quote_code' => '',
                'revision' => '',
                'our_reference' => '',
                'intro_drawing_quote' => '',
                'total_amount' => '',
                'discount' => 0
            );
               
            $rowQuoteItems = array();
            if ($row1['quote_id'] != '') {
                $sqlQuoteItems ="
                SELECT *
                FROM quote_items qi
                WHERE qi.quote_id = '{$row1['quote_id']}'
                ";

                $resultForQuoteItems  = $db->sql_query($sqlQuoteItems);
                $numRowsForQuoteItems = $db->sql_numrows($resultForQuoteItems);
                $rowQuoteItems = $db->sql_fetchrow($resultForQuoteItems);
                if (!is_array($rowQuoteItems)) {
                    $rowQuoteItems = array();
                }
            }

                  
                   
                   $urlPrintLinkPdf  = "index.php?module=enggCrm_opportunity&_spAction=printLinkForPdfNote&opportunity_id={$row['opportunity_id']}&quote_id={$row1['quote_id']}&showHTML=0";
    
                
                
                    $quoteActions ="
                    <div class='float_box clearfix'>
                        
                        <div class='printLink float_left'>
                            <a href='{$urlPrintLinkPdf}' target='_blank' class='btn btn-info button ml10' title='Print Quote'>print pdf</a>
                        </div>
                    
                    </div>
                    ";                                        
                
    
                    $subtotalValue = 0;
                    $totalvalue    = 0;
                        $subtotal_amount = 0;
                        if(($rowQuoteItems['unit_price'] ?? 0) > 0 && ($rowQuoteItems['quantity'] ?? 0) > 0) {
                            $subtotal_amount = round($rowQuoteItems['quantity'] * $rowQuoteItems['unit_price'], 2);
                        } elseif (($rowQuoteItems['unit_price'] ?? 0) > 0 && ($rowQuoteItems['quantity'] ?? 0) == 0) {
                            $subtotal_amount = round($rowQuoteItems['unit_price'], 2);
                        } elseif (($rowQuoteItems['amount'] ?? 0) > 0) {
                            $subtotal_amount = round($rowQuoteItems['amount'], 2);
                        }
    
                        $subtotalValue += $subtotal_amount;
                        
                     
                          $totalvalue = $subtotalValue;
                        
                    
                   
                   
                   $quote_amount = number_format($totalvalue - $row1['discount'], 2);
    
            
    

            $editText = "
            <a class='editFromList' dialogTitle=\"Edit - {$row['title']}\" href='javascript:void(0);' link='{$fn->getEditFromListUrl($row)}'>
                <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_field.jpg' border='0'>
            </a>
            ";

            $currency = '';
            if ($cpCfg['m.enggCrm.hasMultiCurrency'] == 1){
                $currency = $row['currency'] . '&nbsp;';
            }

            $actual_closing = $dateUtil->formatDate($row['actual_closing'], 'DD MMM YYYY');
            $title = substr($row['title'], 0 ,25);

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getListDataCell($row['opportunity_code'])}
            {$listObj->getListDataCell($title)}
            {$listObj->getListDataCell($row['office_ref_no'])}
            {$listObj->getListDataCell($row['company_name'])}
            {$listObj->getListDataCell($row['status'])}
            {$listObj->getListDataCell($row1['quote_code'])}
            {$listObj->getListDataCell($row1['revision'])}
            {$listObj->getListDataCell($row1['our_reference'])}
            {$listObj->getListDataCell($row1['intro_drawing_quote'])}
            {$listObj->getListDataCell($row1['total_amount'])}
            {$listObj->getListDataCell($quoteActions)}  
            {$listObj->getListRowEnd($row['opportunity_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell('Code', 'o.opportunity_code')}
        {$listObj->getListHeaderCell('Project', 'o.title')}
        {$listObj->getListHeaderCell('Ref No', 'o.office_ref_no')}
        {$listObj->getListHeaderCell('Company Name', 'c.company_name')}
        {$listObj->getListHeaderCell('Status', 'o.status')}
        {$listObj->getListHeaderCell('Quote Code', '')}
        {$listObj->getListHeaderCell('Revision', '')}
        {$listObj->getListHeaderCell('License', '')}
        {$listObj->getListHeaderCell('Job Description', '')}
        {$listObj->getListHeaderCell('Quote Amount', '')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $fn      = Zend_Registry::get('fn');
        $cpCfg   = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $sqlCompany = "
        SELECT company_id, company_name FROM company
        ORDER BY company_name
        ";

        $newCompUrl = 'index.php?_spAction=new&lnkRoom=enggCrm_companyLink&showHTML=0';
        $newCompUrl = "<a class='jqui-dialog-form float_left' formId='portalForm' title='New Company'
        w=800 href='' link='{$newCompUrl}' callback='cpm.enggCrm.opportunity.afterNewCompany'>New</a>";
        $expComp  = array(
             'notesRight'  => $newCompUrl
            ,'autoSgstModule' => 'enggCrm_company'
            ,'autoSgstSrchFld' => 'company_name'
            ,'autoSgstActualFld' => 'company_id'
            ,'autoSgstActualFldVal' => ''
            ,'autoSgstCallBack' => 'cpm.enggCrm.opportunity.loadContactsByCompany'
        );

        $sqlContact = '';

        $newContactUrl = 'index.php?_spAction=new&lnkRoom=enggCrm_contactLink&showHTML=0';
        $newContactUrl = "<a class='jqui-dialog-form float_left newContactLink' formId='portalForm' title='New Contact'
        w=800 href='' link='{$newContactUrl}' callback='cpm.enggCrm.opportunity.afterNewContact'>New</a>";

        $expCont  = array(
             'notesRight'  => $newContactUrl
        );

        $expVl   = array('sqlType' => 'OneField');
        $sqlCat  = $fn->getValueListSQL('projectCategory');

        $fieldset1 = "
        {$formObj->getTBRow('Title *', 'title')}
        {$formObj->getDDRowBySQL('Company Name', 'company_id', $sqlCompany, '', $expComp)}
        {$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlContact, '', $expCont)}
        {$formObj->getDDRowBySQL('Category *', 'category', $sqlCat,'', $expVl)}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('New Opportunity', $fieldset1)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');
        $dateUtil = Zend_Registry::get('dateUtil');

        $formObj->mode = $tv['action'];

        $msgTop   = '';

        if ($row['project_id'] != ''){
            $formObj->mode = 'detail';
            $msgTop = "
            <div class='p5'>
                <h3>This opportunity is already converted to project and no further editing allowed</h3>
            <div>
            ";
            $tv['action'] = 'detail';

            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                $('.actionBtns #actBtn_save').parent().remove();
                $('.actionBtns #actBtn_apply').parent().remove();
                $('.actionBtns #actBtn_convertOppToProject').parent().remove();
            "));

        }
        $quote_ref = '';

        if ($cpCfg['m.enggCrm.oppurtunity.showQuoteRef'] == 1) {
            $quote_ref = $formObj->getTBRow('Quote Ref#', 'quote_ref', $row['quote_ref']);
        }

        $sqlComboContact = '';
        if ($row['company_id'] != "") {
            $sqlComboContact = $fn->getDDSql('enggCrm_contact', array('condn' => "company_id = {$row['company_id']}"));
        }

        $sqlComboCompany = "
        SELECT company_id, company_name FROM company
        ORDER BY company_name
        ";

        $sqlStatus = "
        SELECT value
        FROM valuelist
        WHERE key_text = 'opportunityStatus'
          AND value != 'Won'
        ORDER BY sort_order
        ";

        $expVl = array('sqlType' => 'OneField');

        if (strtolower($row['status']) == 'won'
           || strtolower($row['status']) == 'Awarded'
           || $row['project_id'] > 0
           ){
            $projectTxt = '';

            if ($row['project_id'] > 0) {
                $projectLink = "index.php?_topRm={$tv['topRm']}&module=enggCrm_project&project_id={$row['project_id']}&_action=detail";
                $linkToProj = "<a href='{$projectLink}'>{$row['project_code']}</a>";
                $projectTxt = $formObj->getTBRow('Project Code', 'project_code', $linkToProj, array('isEditable' => 0));
            }

            $expStatus = array('sqlType' => 'OneField');
            $status  = $formObj->getDDRowBySQL('Status *', 'status', $sqlStatus, $row['status'], $expStatus);
            $status .= $projectTxt;

        } else {

            $SQL2 = "
            SELECT count(*) AS count
            FROM quote
            WHERE opportunity_id = {$row['opportunity_id']}
            ";
            $result2 = $db->sql_query($SQL2);
            $row2 = $db->sql_fetchrow($result2);

            $expStatus = array('sqlType' => 'OneField');
            $status = $formObj->getDDRowBySQL('Status *', 'status', $sqlStatus, $row['status'], $expStatus);
        }

        $notes = '';

        $expNoEdit  = array('isEditable' => 0);
        $expOppCode = array('isEditable' => $cpCfg['m.enggCrm.oppurtunity.codeEditable']);

        $sqlCompany = $fn->getDDSql('enggCrm_company', array('condn' => "category = 'client'"));

        $sqlType   = $fn->getValueListSQL('clientType');
        $sqlDiff   = $fn->getValueListSQL('projectDifficulty');
        $sqlType   = $fn->getValueListSQL('clientType');
        $sqlCat    = $fn->getValueListSQL('projectCategory');
        $sqlChance = $fn->getValueListSQL('opportunityChance');
        $sqlStatus = $fn->getValueListSQL('opportunityStatus');
        $expDollar = array("fldPrefix" => "$");

        $expComp = array();
        $expCont = array();

        $companyLink = "<a href='index.php?_topRm={$tv['topRm']}&module=enggCrm_company&company_id={$row['company_id']}&_action=detail'>{$row['company_name']}</a>";
        $contactLink = "<a href='index.php?_topRm={$tv['topRm']}&module=enggCrm_contact&contact_id={$row['contact_id']}&_action=detail'>{$row['contact_name']}</a>";

        if ($tv['action'] == 'edit'){
            $newCompUrl = 'index.php?_spAction=new&lnkRoom=enggCrm_companyLink&showHTML=0';
            $newCompUrl = "<a class='jqui-dialog-form float_left' formId='portalForm' title='New Company'
            w=800 href='' link='{$newCompUrl}' callback='cpm.enggCrm.opportunity.afterNewCompany'>New</a>";
            $expComp  = array(
                 'notesRight'  => $newCompUrl
                ,'detailValue' => $row['company_name']
                ,'autoSgstModule' => 'enggCrm_company'
                ,'autoSgstSrchFld' => 'company_name'
                ,'autoSgstActualFld' => 'company_id'
                ,'autoSgstActualFldVal' => $row['company_id']
                ,'autoSgstCallBack' => 'cpm.enggCrm.opportunity.loadContactsByCompany'
            );

            $newContactUrl = "index.php?_spAction=new&lnkRoom=enggCrm_contactLink&company_id={$row['company_id']}&showHTML=0";
            $newContactUrl = "<a class='jqui-dialog-form float_left newContactLink' formId='portalForm' title='New Contact'
            w=800 href='' link='{$newContactUrl}' callback='cpm.enggCrm.opportunity.afterNewContact'>New</a>";

            $expCont  = array(
                 'notesRight'  => $newContactUrl
                ,'detailValue' => $row['contact_name']
            );

        } else {
            if ($row['company_name'] != ''){
                $expComp['detailValue'] = $companyLink;
            }
        }

        if ($row['contact_name'] != ''){
            $expCont['detailValue'] = $contactLink;
        }

        $enqDate = ($tv['newRecord'] == 1) ? date("Y-m-d") : $row['enquiry_date'];
        $expNum  = array('autoFormat' => 1);
        $expCost = array('autoFormat' => 1, 'isEditable' => 0);

        $expReferrer = array(
             'autoSgstModule' => 'enggCrm_contact'
            ,'autoSgstActualFld' => 'referrer_contact_id'
            ,'autoSgstActualFldVal' => $row['referrer_contact_id']
        );

        $optionArr = array(
             1 => 'Very Low'
            ,2 => 'Low'
            ,3 => 'Normal'
            ,4 => 'High'
            ,5 => 'Very High'
        );

        $sqlPM = $fn->getDDSql('core_staff', array('condn' => "status = 'Current' AND staff_type='Project Manager'"));
        $expPM = array('detailValue' => $row['project_manager_name']);

        $creation_date     = $dateUtil->formatDate($row['creation_date'], 'DD MMM YYYY HHH:MIN:SS');
        $modification_date = $dateUtil->formatDate($row['modification_date'], 'DD MMM YYYY HHH:MIN:SS');
        
        $sqlEmployeeName = "
        SELECT e.employee_id
              ,CONCAT_WS(' ', e.first_name, e.last_name) AS employee_name
        FROM employee e
        ORDER BY employee_name ASC
        ";
        if ($row['status'] != ''){
        $status="{$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, $row['status'], $expStatus)}";
        }else{
            $status="{$formObj->getDDRowBySQL('Status', 'status', $sqlStatus, 'In Progress', $expStatus)}";

        }
        $expHideFirstOpt = array('hideFirstOption' => true);
        $expQuoteDate    = array('maxDate' => date('Y-m-d'), 'yearEnd' => date('Y'));
        $rowQuote = $fn->getRecordByCondition('quote', "opportunity_id = '{$row['opportunity_id']}'");
        $spArrayQuoteStatus = array('New', 'Quoted', 'Awarded', 'Not Awarded', 'Cancelled');
        $signArray = array(
            "Jassim"
           ,"Ibrahim"
           ,"Wassim"
      );
      $sqlForQuoteConvertProj ="
      SELECT *
      FROM quote
      WHERE opportunity_id = {$row['opportunity_id']}
     
      ";

      $resultForQuoteItems  = $db->sql_query($sqlForQuoteConvertProj);                  
      $rowQuoteStatus       = $db->sql_fetchrow($resultForQuoteItems);                  
      $numRowsForQuote      = $db->sql_numrows($resultForQuoteItems);

      if($numRowsForQuote > 0) {
          $statusConfirmed = 'Yes';
      } else {
          $statusConfirmed = 'Not Awarded';
      }

      $sqlForProj ="
      SELECT p.*
      FROM project p
      WHERE p.opportunity_id = {$row['opportunity_id']}
      ";
      $resultForProject   = $db->sql_query($sqlForProj);                  
      $rowForProject      = $db->sql_fetchrow($resultForProject);                  
      $numRowsForProject  = $db->sql_numrows($resultForProject);
      if (!is_array($rowForProject)) {
          $rowForProject = array();
      }
      $rowForProject += array(
          'project_id' => '',
          'quote_id' => 0
      );


      $quoteStatus='';
      // THIS CONDITIONS IS USED FOR: ONCE THE QUOTE STATUS IS CONFIRMED. THE CONVERT BUTTON WILL BE APPEARED //        
      if( $numRowsForProject == 0) {  
          $quoteStatus="
          <div class='float_left btn btn-info mb5'>

              <a  class='convertOppToProject ' statusConfirmed='{$statusConfirmed}'>Convert Opp To Project</a>
          </div>
          
          ";
      
      } 
      $projectRecBtn='';
      $urlprojectRecord = "index.php?_topRm=project&module=enggCrm_project&_action=edit&project_id={$rowForProject['project_id']}";  

      // THIS CONDITIONS IS USED FOR: ONCE IT IS CONVERTED TO PROJECT, ADD QUOTE BUTTON WOULD BE HIDE AFTER THAT GO TO PROJECT BUTTON WILL BE SHOWING //        
      if($rowForProject['quote_id'] != 0){
          $projectRecBtn="
          <div class='float_left btn btn-info mb5'>

              <a href='{$urlprojectRecord}' class='' title='Project Record' target='_blank'>Go to Project</a>
          </div>
          
          ";            
      }
      $urlPrintLinkPdf  = "index.php?module=enggCrm_opportunity&_spAction=printLinkForPdfNote&opportunity_id={$row['opportunity_id']}&quote_id={$rowQuote['quote_id']}&showHTML=0";
    
                
                
      $quoteActions ="

      <div class='float_left btn btn-info mb5'>

              <a href='{$urlPrintLinkPdf}' target='_blank' class='' title='Print Quote'>print pdf</a>
              </div>
        
      ";                                        
  
      $sqlCompany = "
      SELECT company_id, company_name FROM company
      ORDER BY company_name
      ";
      $sqlEmployee = "
      SELECT employee_id, employee_name FROM employee
      ORDER BY employee_name
      ";
        $text = "
        <div class='floatbox actionBtnsDetail'>
        <div class='purchaseOrderRightpanelButtons floatbox'>
        {$quoteStatus}{$projectRecBtn}{$quoteActions}
        </div>
      
        </div>
        <div class='linkPortalWrapper'>
            <div expanded='1' class='header'>
                <div class='floatbox'>
                    <div class='toggle'></div>
                    <div class='float_left heading-title'>Key Details | Code: {$row['opportunity_code']}</div>
                    <div class='float_right'>Creation : {$row['created_by']} on {$creation_date}<br/>Modified : {$row['modified_by']} on {$modification_date}</div>
                </div>
            </div>
            <div>
                <div class='linkPortalDataWrapper'>    
                    <div class='row col-md-12'>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getTBRow('Title *', 'title', $row['title'])}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getDDRowBySQL('Category *', 'category', $sqlCat, $row['category'], $expNoEdit)}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getTBRow('Ref No', 'office_ref_no', $row['office_ref_no'])}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getDDRowBySQL('Company', 'company_id',  $sqlCompany, $row['company_id'], $expComp)}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getDDRowBySQL('Contact', 'contact_id', $sqlComboContact, $row['contact_id'], $expCont)}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$status}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getDateRow('Quote Date', 'quote_date',$rowQuote['quote_date'], $expQuoteDate)}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getTBRow('Quote Code', 'quote_code',$rowQuote['quote_code'])}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getTBRow('Revision', 'revision',$rowQuote['revision'])}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getTBRow('Quote Amount', 'total_amount', isset($rowQuote['total_amount']) ? $rowQuote['total_amount'] : '0.000')}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getDDRowByArr('Quote Status', 'quote_status', $spArrayQuoteStatus, $rowQuote['quote_status'], $expHideFirstOpt)}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getTBRow('Duration', 'project_reference', $rowQuote['project_reference'])}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getDDRowByVL('Mode of Payment', 'payment_method', 'paymentQuoteType', $rowQuote['payment_method'])}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getTBRow('Licence', 'our_reference', $rowQuote['our_reference'])}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getYesNoRRow('Digital Signature','apply_digital_signature', $rowQuote['apply_digital_signature'])}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getDDRowBySQL('Signature Name', 'employee_id',  $sqlEmployee, $row['employee_id'])}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getTBRow('Job Description', 'intro_drawing_quote', $rowQuote['intro_drawing_quote'])}</div>
                        <div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getTBRow('Subject', 'subject', $rowQuote['subject'])}</div>
                    </div>
                    <div class='row col-md-12'>
                        <div class='col-md-12 col-sm-12 col-xs-12'><label>Description</label>{$formObj->getHTMLEditor('Description', 'condition', $rowQuote['condition'])}</div>
                    </div>
                    <div class='row col-md-12'>
                    <div class='col-md-12 col-sm-12 col-xs-12'><label>Note</label>{$formObj->getHTMLEditor('Description', 'note', $rowQuote['note'])}</div>
                </div>
                </div>
            </div>
        </div>
        <input type='hidden' id='hasQuotingModule' value='{$cpCfg['m.enggCrm.hasQuotingModule']}' />
        ";
                        //<div class='col-md-3 col-sm-4 col-xs-12'>{$formObj->getDDRowByArr('Signature Name', 'signature_name', $signArray, $rowQuote['signature_name'])}</div>
        return $text;
    }

    /**
     * Add Line Item Edit
     */
    function getEditLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $tv      = Zend_Registry::get('tv');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $quote_items_id  = $fn->getReqParam('quote_items_id');
        $opportunity_id  = $fn->getReqParam('opportunity_id');

        $rowQuoteItem = $fn->getRecordRowByID('quote_items', 'quote_items_id', $quote_items_id);
        $rowQuote     = $fn->getRecordRowByID('quote', 'quote_id', $rowQuoteItem['quote_id']);
        $exp          = array('sqlType' => 'OneField');

        $formActionEditLineItem = "index.php?module=enggCrm_opportunity&_spAction=editLineItemSubmit&lnkRoom={$tv['lnkRoom']}&quote_items_id={$quote_items_id}&opportunity_id={$opportunity_id}&showHTML=0";       

        if($rowQuote['drawing_nos'] == 1) {
          $text = "
          <form id='editForLineItem' class='yform columnar' method='post' action='{$formActionEditLineItem}'>
              <fieldset>
                  {$formObj->getTARow('Drawing Number', 'drawing_number', $rowQuoteItem['drawing_number'])}
                  {$formObj->getTARow('Drawing Title', 'drawing_title', $rowQuoteItem['drawing_title'])}
                  {$formObj->getTBRow('Revision', 'drawing_revision', $rowQuoteItem['drawing_revision'])}
                  <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
                  <input type='hidden' name='quote_items_id' value='{$quote_items_id}' />
                  <input type='hidden' name='drawing_nos' value='{$rowQuote['drawing_nos']}' />
              </fieldset>
          </form>
          ";
        } else {
          $text = "
          <form id='editForLineItem' class='yform columnar' method='post' action='{$formActionEditLineItem}'>
              <fieldset>
                  {$formObj->getTARow('Title', 'title', $rowQuoteItem['title'])}
                  {$formObj->getTARow('Description', 'description', $rowQuoteItem['description'])}
                  {$formObj->getTBRow('Qty', 'quantity', $rowQuoteItem['quantity'])}
                  {$formObj->getTBRow('UoM', 'unit', $rowQuoteItem['unit'])}
                  {$formObj->getTBRow('Unit Price', 'unit_price', $rowQuoteItem['unit_price'])}
                  {$formObj->getTBRow('Total Price', 'amount', $rowQuoteItem['amount'])}
                  <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
                  <input type='hidden' name='quote_items_id' value='{$quote_items_id}' />
                  <input type='hidden' name='drawing_nos' value='{$rowQuote['drawing_nos']}' />
              </fieldset>
          </form>
          ";
        }

        return $text;
    }

    /**
     *
     */
    function getAddMultipleLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $opportunity_id = $fn->getReqParam('opportunity_id');
        $quote_id       = $fn->getReqParam('quote_id');
        $rowQuote       = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);

        if($rowQuote['drawing_nos'] == 1) {
            $rows = $this->getAddMultipleLineItemDrawing();

            $newRow = "
            <a  class='addDrawingRow btn btn-primary mb10' opportunity_id='{$opportunity_id}'>Add Line Item</a>
            ";

            $header ="
            <tr>
              {$newRow}
            </tr>
            <tr style='background-color:#EAEAE8;'>
                <th width='50%'>Drawing Number</th>
                <th width='60%'>Drawing Title</th>
                <th class='txtCenter'>Revision</th>
                <th width='2%'></th>
            </tr>
            ";
        } else {
            $description = "<textarea type='text' value='' id='description' class='text lineItemDescription' name='description[]'></textarea>";
            $title       = "<textarea type='text' value='' id='title' class='text lineItemTitle' name='title[]'></textarea>";
            $quantity    = "<input type='text' value='' id='quantity' class='text lineItemQuantity' name='quantity[]'>";
            $unit        = "<input type='text' value='' id='unit' class='text lineItemUnit' name='unit[]'>";
            $amount      = "<input type='text' value='' id='unit_price' class='text lineItemUnitPrice' name='unit_price[]'>";
            $total_cost  = "<td><input type='text' value='' id='amount' class='text lineItemAmount' name='amount[]'></td>";
            $clear       = "<td class='text'><a  class='clearLineItem'><u>Clear</u></a></td>";

            $rows = "
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td>{$unit}</td>
                <td>{$quantity}</td>
                <td>{$amount}</td>
                {$total_cost}
                {$clear}
            </tr>
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td>{$unit}</td>
                <td>{$quantity}</td>
                <td>{$amount}</td>
                {$total_cost}
                {$clear}
            </tr>
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td>{$unit}</td>
                <td>{$quantity}</td>
                <td>{$amount}</td>
                {$total_cost}
                {$clear}
            </tr>
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td>{$unit}</td>
                <td>{$quantity}</td>
                <td>{$amount}</td>
                {$total_cost}
                {$clear}
            </tr>
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td>{$unit}</td>
                <td>{$quantity}</td>
                <td>{$amount}</td>
                {$total_cost}
                {$clear}
            </tr>
            ";

            $newRow = "
            <a  class='addRow btn btn-primary mb10'>Add Line Item</a>
            ";

            $header ="
            <tr>
                {$newRow}
              <label class='ml10 mr5'><b>Discount : </b></label>
              <input type='text' value='{$rowQuote['discount']}' id='discount' class='text overallDiscount' name='overallDiscount'>
              <div class='quoteLineItemsOverallTotal'>
                Total Amount <span class='quoteLineItemsOverallTotalAmount'>0.000</span>
              </div>
            </tr>
            <tr style='background-color:#EAEAE8;'>
                <th width='50%'>Title</th>
                <th width='60%'>Description</th>
                <th width='10%' class='txtCenter'>UoM</th>
                <th class='txtCenter'>Qty</th>
                <th width='13%' class='txtCenter'>Unit Price</th>
                <th width='15%' class='txtCenter'>Total Price</th>
                <th width='2%' ></th>
            </tr>
            ";
        }

        $formAction = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=addMultipleLineItemSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);

        $text = "
        <form id='addMultipleLineItemForm' class='addMultipleLineItemForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
            <input type='hidden' name='quote_id' value='{$quote_id}' />
            <input type='hidden' name='drawing_nos' value='{$rowQuote['drawing_nos']}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddLineItemRecord() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $description = "<textarea value='' id='description' class='text lineItemDescription' name='description[]'></textarea>";
        $title       = "<textarea type='text' value='' id='title' class='text lineItemTitle' name='title[]'></textarea>";
        $quantity    = "<input type='text' value='' id='quantity' class='text lineItemQuantity' name='quantity[]'>";
        $unit        = "<input type='text' value='' id='unit' class='text lineItemUnit' name='unit[]'>";
        $amount      = "<input type='text' value='' id='unit_price' class='text lineItemUnitPrice' name='unit_price[]'>";
        $total_cost  = "<td><input type='text' value='' id='amount' class='text lineItemAmount' name='amount[]'></td>";
        $clear       = "<td class='text'><a  class='clearLineItem'><u>Clear</u></a></td>";

        $rows = "
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td>{$unit}</td>
            <td>{$quantity}</td>
            <td>{$amount}</td>
            {$total_cost}
            {$clear}
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getPrintLinkForPdfOld() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);
        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootquote.php');

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

        $quote_id = $fn->getReqParam('quote_id');
        $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        //============================================================================= //
        if ($quoteRec['opportunity_id']) {
            $SQL = "
            SELECT q.*
                  ,qi.title AS quote_item_title
                  ,qi.quantity
                  ,qi.unit
                  ,qi.description
                  ,qi.amount
                  ,qi.unit_price
                  ,o.opportunity_id
                  ,o.opportunity_code
                  ,o.company_id
                  ,c.company_name
                  ,c.billing_address_flat
                  ,c.billing_address_street
                  ,c.billing_address_country
                  ,c.billing_address_po_code
                  ,c.company_id
                  ,co.salutation
                  ,co.first_name 
            FROM quote q
            LEFT JOIN (quote_items qi) ON (qi.quote_id = q.quote_id)
            LEFT JOIN (opportunity o) ON (o.opportunity_id = q.opportunity_id)
            LEFT JOIN (company c) ON (c.company_id = o.company_id)
            LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)
            WHERE q.quote_id = {$quote_id}
            ORDER BY qi.quote_items_id ASC
            ";
        } else {
            $SQL = "
            SELECT q.*
                  ,qi.title AS quote_item_title
                  ,qi.quantity
                  ,qi.unit
                  ,qi.description
                  ,qi.amount
                  ,qi.unit_price
                  ,p.opportunity_id
                  ,p.project_code
                  ,p.company_id
                  ,c.company_name
                  ,c.billing_address_flat
                  ,c.billing_address_street
                  ,c.billing_address_country
                  ,c.billing_address_po_code
                  ,c.company_id
                  ,co.salutation
                  ,co.first_name 
            FROM quote q
            LEFT JOIN (quote_items qi) ON (qi.quote_id = q.quote_id)
            LEFT JOIN (project p) ON (q.project_id = p.project_id)
            LEFT JOIN (company c) ON (c.company_id = p.company_id)
            LEFT JOIN (contact co) ON (co.contact_id = p.contact_id)
            WHERE q.quote_id = {$quote_id}
            ORDER BY qi.quote_items_id ASC
            ";
        }
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
                             
        $today = date("d-m-Y");

        $quote_date   = $fn->getCPDate($company['quote_date'], 'd-m-Y');

        /*
        $sqlCompAdd = "
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
        <table border="0" width="100%"  cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#14213d; text-decoration:underline; line-height:26px;">QUOTATION</td>
            </tr>
        </table>
        ';

        $quo_date = $fn->getCPDate($company['quote_date'], 'ym/');
        $quote_code = $quo_date . substr($company['quote_code'], 2);
        $address_street = "";
        if ($company['billing_address_street']) {
            $address_street = '
            <tr>
                <td>'.strtoupper($company['billing_address_street']).'</td>
                <td colspan="3"></td>
            </tr>
            ';
        }
        $tbl2 ='<table border="0" width="100%" cellpadding="2" cellspacing="0">
                    <tr>
                        <td width="38%" style="font-size:10px; font-weight:bold;  line-height:16px;"> To : </td>
                        <td width="24%"></td>
                        <td width="38%" style="font-size:10px; font-weight:bold;  line-height:16px;"> </td>
                    </tr>
                    <tr><td width="38%" style=""><table border="0" cellpadding="0">
                                <tr>
                                    <td width="75%" style="font-size:10px;"> '.$company['salutation'].'. '.$company['first_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="75%" style="font-size:10px;"> '.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="75%" style="font-size:10px;"> '.$company['billing_address_flat'].',<br/> '.$company['billing_address_street'].', <br/>'.$company['billing_address_country'].' - '.$company['billing_address_po_code'].'</td>
                                </tr>
                               
                            </table>
                        </td>
                        <td width="24%"></td>
                        <td width="38%" style=""><table border="0">
                                
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Date</td>
                                    <td width="75%" style="font-size:10px;">: '.$quote_date.'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Ref. No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['quote_code'].'</td>
                                </tr>
                               
                               
                               
                            </table>
                        </td>
                    </tr>
                </table>
                <br/><br/>
                <table border="0">
                    <tr>
                        <td width="100%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Scope of works - Wet Storage - Foodhall AlKhiran</td>
                       
                    </tr>
                   
                </table>';

        $tbl3 ='<table border="0"  cellpadding="4"  width="100%">
                      <thead>
                          <tr bgcolor="#ededf0">
                              <th width="6%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">Item</th>
                              <th width="55%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DESCRIPTION</th>
                              <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                              <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT</th>
                              <th width="12%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT PRICE<br/>KWD</th>
                              <th width="13%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">TOTAL PRICE<br/>KWD</th>
                          </tr>
                      </thead>
                      <tbody style="display: table; table-layout: fixed; height: 600px;">';
        $subtotalValue = 0;
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {

           

            $subtotal_amount = round($row['quantity'] * $row['unit_price'], 2);
            $subtotal_amount_formatted = number_format($subtotal_amount, 2);
            $explode = explode('.', $row['quantity']);
            $quantity_truncate = $explode[0];

            $tbl3 = $tbl3.'<tr>
                                  <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                  <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit'].'</td>
                                  <td width="12%" align="right" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit_price'].'</td>
                                  <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;">'.$subtotal_amount_formatted.'</td>
                              </tr>

                    ';

            $subtotalValue += $subtotal_amount;    
            $sub_total2  = $subtotalValue - $row['discount'];
            $gsttaxvalue = $cpCfg['cp.gstPercentage'] ;
            $gstvalue = $sub_total2 * $gsttaxvalue / 100;
            $totalvalue = $gstvalue + $sub_total2;
            $count++;
        }

      
        $tbl3 = $tbl3.'<tr>
                            
                              <td colspan="5" align="right" style="font-size:10px; border-top:1px solid #000;border-right:1px solid #000;border-left:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">TOTAL EXCLUDING GST</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;">'.number_format($subtotalValue, 2).'</td>
                           </tr>
                            <tr>
                             
                              <td colspan="5" align="right" style="font-size:10px; border-top:1px solid #000;border-right:1px solid #000;border-left:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">Discount KWD</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;">'.$company['discount'].'</td>
                           </tr>
                            <tr>
                              
                              <td colspan="5" align="right" style="font-size:10px; border-top:1px solid #000;border-right:1px solid #000;border-left:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">Final Amount KWD</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;">'.number_format($subtotalValue - $company['discount'],2).'</td>
                           </tr>
                          </tbody>
                        </table>';

        $tbl4 = '
        <table border="0" width="100%" cellpadding="2">
            <tr>
                <td align="left" width="100%" style="font-size:10px; font-weight:bold; ">Note :</td>
            </tr>
            <tr>
                <td align="left" style="line-height:16px;font-size:10px;">'.nl2br($company['condition']).'</td>
            </tr>
        </table>';

        $tbl5 = '
        <table border="0" width="100%">
            <br/><br/>
            <tr>
                <td border="0" align="left" style="font-size:10px;" width="100%"><b>Terms Of Payment:</b>'.nl2br($company['intro_quote']).'</td>
            </tr>
            <tr>
                <td border="0" align="left" style="font-size:10px;" width="100%"><b>Duration:</b>'.nl2br($company['project_reference']).'</td>
            </tr>
        </table>
        ';

        $SQLMedia = "
        SELECT file_name, record_type
        FROM media
        WHERE record_id = '{$company['employee_id']}'
        AND room_name   = 'payroll_employee'
        AND record_type = 'digitalSign'
        ";
        $resultMedia  = $db->sql_query($SQLMedia);

        $imageAttached = '';
        while($rowMedia = $db->sql_fetchrow($resultMedia)) {
            $imageAttached = realpath($cpCfg['cp.mediaFolder']).'/normal/'.$rowMedia['file_name'];
        }

       $tbl6 = '
        <table border="0" width="100%" cellpadding="3">
         <tr>
                <td border="0" align="left" style="font-size:10px;" width="100%">Sincerely,</td>
            </tr>
         <tr>
                <td border="0" align="left" style="font-size:10px;" width="100%"></td>
            </tr>
            <tr>
            '.$seal.'
            '.$signname.'
            </tr>
            <tr>
                <td style="font-size:10px;font-weight:bold;">Mohamed Ibrahim<br/>
                   A Team International General Contracting company for Buildings<br/>
                      Mob: 66144322 <!--/ 60063220--></td>  
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-5);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
                $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');

            $download_title = $cpCfg['cp.companyName'] . '-' . $company['opportunity_code'] . '-Quote.pdf';
        
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintLinkForPdf() {
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
        //$pdf->setPrintFooter(false);

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

        $quote_id = $fn->getReqParam('quote_id');

        $SQL = "
        SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.quantity
              ,qi.unit
              ,qi.description
              ,qi.amount
              ,qi.unit_price
              ,qi.remarks
              ,o.opportunity_id
              ,o.opportunity_code
              ,o.company_id
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,c.address_town AS billing_address_town
              ,c.address_state AS billing_address_state
              ,gc.name AS billing_address_country
              ,c.address_po_code AS billing_address_po_code
              ,c.company_id
              ,co.email
              ,c.phone
              ,c.fax
              ,c.mobile
              ,co.salutation
              ,co.first_name
              ,s.email AS employee_email
              ,e.mobile AS employee_mobile
        FROM quote q
        LEFT JOIN (quote_items qi) ON (qi.quote_id = q.quote_id)
        LEFT JOIN (opportunity o) ON (o.opportunity_id = q.opportunity_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        LEFT JOIN (employee e) ON (e.employee_id = q.employee_id)
        LEFT JOIN (staff s) ON (s.employee_id = q.employee_id)
        WHERE q.quote_id = {$quote_id}
        ORDER BY qi.quote_items_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $quote_date   = $fn->getCPDate($company['quote_date'], 'd-m-Y');
        $today      = date("d-m-Y");

        $sqlCompAdd = "
        SELECT ca.*
        FROM company_address ca
        WHERE ca.company_id = {$company['company_id']}
        LIMIT 0,1
        ";
        $resultCompAdd = $db->sql_query($sqlCompAdd);
        $rowCompAdd = $db->sql_fetchrow($resultCompAdd);

        $tbl1 = '
        <table border="0" width="100%" style="border-top: 1px solid #0e502a;" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#078205; text-decoration:underline; line-height:26px;">QUOTATION</td>
            </tr>
        </table>
        ';

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = ',<br/>: '.$company['billing_address_street'];
        }

        $tbl2 ='<table border="0" width="100%" cellpadding="2" cellspacing="0">
                    <tr>
                        <td width="38%" style="font-size:10px; font-weight:bold; background-color:#92d14f; border:1px solid #000; line-height:16px;">Quotation To : </td>
                        <td width="24%"></td>
                        <td width="38%" style="font-size:10px; font-weight:bold; background-color:#92d14f; border:1px solid #000; line-height:16px;">Quotation From :</td>
                    </tr>
                    <tr><td width="38%" style="border:1px solid #000;"><table border="0" cellpadding="0">
                                <tr>
                                    <td width="25%" style="font-size:10px;">Name</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['salutation'].'. '.$company['first_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">CO. Name</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Address</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['billing_address_flat'].$rowStreet.', <br/>: '.$company['billing_address_country'].' - '.$company['billing_address_po_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Email</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['email'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">HP No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['mobile'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Tel</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['phone'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Fax</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['fax'].'</td>
                                </tr>
                            </table>
                        </td>
                        <td width="24%"></td>
                        <td width="38%" style="border:1px solid #000;"><table border="0">
                                <tr>
                                    <td width="25%" style="font-size:10px;">Ref. No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['quote_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Date</td>
                                    <td width="75%" style="font-size:10px;">: '.$quote_date.'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Payment</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['payment_method'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Email</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['employee_email'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">HP No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['employee_mobile'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Tel</td>
                                    <td width="75%" style="font-size:10px;">: '.$cpCfg['cp.companyPhone'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Fax</td>
                                    <td width="75%" style="font-size:10px;">: '.$cpCfg['cp.companyFax'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Created by</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['created_by'].'</td>
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
                        <tr bgcolor="#92d14f">
                            <th width="6%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                            <th width="55%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DESCRIPTION</th>
                            <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                            <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT</th>
                            <th width="12%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT PRICE($)</th>
                            <th width="13%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">TOTAL PRICE($)</th>
                        </tr>
                    </thead>
                    <tbody style="display: table; table-layout: fixed; height: 600px;">';
        
        $subtotalValue = 0;
        $count      = 1;
        $countCheck = 1;
        while ($row = $db->sql_fetchrow($result)) {

            if ($row['quote_item_title']) {
                $countCheck++;
                $tbl3 = $tbl3.'<tr>
                                    <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="55%" style="font-size:10px; font-weight:bold; border-left:1px solid #000;border-right:1px solid #000;"><u>'.nl2br($row['quote_item_title']).'</u><br/></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="13%" style="border-right:1px solid #000;"></td>
                                </tr>
                        ';
            }

            $subtotal_amount = 0;
            if ($row['amount'] != "") {
                $subtotal_amount = round($row['amount'], 2);
            } else if($row['unit_price'] > 0 && $row['qty'] > 0) {
                $subtotal_amount = round($row['qty'] * $row['unit_price'], 2);
            } else if ($row['unit_price'] > 0 && $row['qty'] == 0) {
                $subtotal_amount = round($row['unit_price'], 2);
            }

            $subtotal_amount_formatted = number_format($subtotal_amount, 2);

            if($row['quantity'] == 0) {
                $row['quantity'] = "";
            }

            if($row['unit_price'] == 0) {
                $row['unit_price'] = "";
            }

            if($subtotal_amount_formatted == "0.000") {
                $subtotal_amount_formatted = "";
            }

            $tbl3 = $tbl3.'<tr>
                                <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit'].'</td>
                                <td width="12%" align="right" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit_price'].'</td>
                                <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;">'.$subtotal_amount_formatted.'</td>
                            </tr>
                    ';

            $subtotalValue += $subtotal_amount;

            if($company['gst'] == 1) {
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

        if($company['discount']) {
            $tbl3 = $tbl3.'<tr>
                                <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;"></td>
                                <td width="55%" style="font-size:10px; border-left:1px solid #000;border-right:1px solid #000;"><br/><br/>Less Discount</td>
                                <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                <td width="13%" style="font-size:10px;border-right:1px solid #000;" align="right"><br/><br/>-'.number_format($company['discount'], 2).'</td>
                            </tr>
                    ';
        }

        $totalvalue      = $totalvalue - $company['discount'];
        $amount_in_words = $fn->getConvertNumber($totalvalue);

        if($company['gst'] == 1) {
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

        if($company['gst'] == 1) {
            $tbl3 = $tbl3.'<tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;"></td>
                              <td align="right" colspan="3" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;">SUB TOTAL</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;">'.number_format($subtotalValue - $company['discount'],2).'</td>
                          </tr>
                          <tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000;">'.number_format($gstvalue, 2).'</td>
                           </tr>
                           <tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">TOTAL AMOUNT</td>
                              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000; border-bottom:1px solid #000;">'.number_format($totalvalue, 2).'</td>
                           </tr>
                          </tbody>
                        </table>';
        } else {
            $tbl3 = $tbl3.'<tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">TOTAL EXCLUDING GST</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;">'.number_format($totalvalue, 2).'</td>
                           </tr>
                          </tbody>
                        </table>';
        }

        $tbl4 = '
        <table border="1" width="100%" cellpadding="2">
            <tr>
                <td align="left" width="100%" style="font-size:10px; font-weight:bold; background-color:#92d14f;">Other Comments or Special Instructions :</td>
            </tr>
            <tr>
                <td align="left" style="line-height:16px;font-size:10px;">'.nl2br($company['condition']).'</td>
            </tr>
        </table>';

        $tbl5 = '
        <table border="0" width="100%">
            <br/><br/>
            <tr>
                <td border="0" align="left" style="font-size:10px;" width="100%">Yours Faithfully,</td>
            </tr>
        </table>
        ';

        $SQLMedia = "
        SELECT file_name, record_type
        FROM media
        WHERE record_id = '{$company['employee_id']}'
        AND room_name   = 'payroll_employee'
        AND record_type = 'digitalSign'
        ";
        $resultMedia  = $db->sql_query($SQLMedia);

        $imageAttached = '';
        while($rowMedia = $db->sql_fetchrow($resultMedia)) {
            $imageAttached = realpath($cpCfg['cp.mediaFolder']).'/normal/'.$rowMedia['file_name'];
        }

        $tbl6 = '
        <table border="0" width="100%" cellpadding="3">
            <tr>
                <td width="40%" style="border-bottom:1px solid black"><img src="'.$imageAttached.'"></td>
                <td width="30%"></td>
                <td width="30%" style="border-bottom:1px solid black"></td>
            </tr>
            <tr>
                <td style="font-size:10px;">Authorised Signature / Date</td>
                <td></td>
                <td style="font-size:10px;">Accepted By / Date</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-5);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['opportunity_code'] . '-Quote.pdf';
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
        $wMaintenanace ="";
        $renewal="";
        $wCostingSummary = getCPWidgetObj('enggCrm_opportunityCostingSummary')->view->getRowsHTML($row['opportunity_id']);

                if ($row['category'] == 'Contract') {
                        $renewal ="<li class='fourth'>
                    <a class='dropdown-item tabButtonLi contractLinked' data-toggle='tab' href='#tabs-4'>Contract</a>
                </li>";
                      $wMaintenanace = getCPModuleObj('enggCrm_opportunity')->view->getProjectMaintenanacePortal($row['opportunity_id']);

                }




        
        $text = "
        <div id='tabs' class='mb20 noPadding col-md-12 col-sm-12 col-xs-12'>
            <ul>
             
                <li class='first active'>
                    <a class='dropdown-item tabButtonLi addStaffAndEmployee' data-toggle='tab' href='#tabs-1'  aria-expanded='true'>Attachment</a>
                </li>

               {$renewal}
            </ul>

       

            <div id='tabs-1' aria-labelledby='rightPanelsLinkedPortals-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade active in noPadding'>
                <div class='floatBox row'>
                    <div class='col-md-12 col-sm-12 col-xs-12 noPadding'> 
                        <!--<div class='col-md-6 col-sm-6 col-xs-12'>
                            {$displayLinkData->getLinkPortalMain('enggCrm_opportunity', 'enggCrm_employeeLink', 'Add Employee', $row)}
                        </div>-->
                        <div class='col-md-6 col-sm-6 col-xs-12'>
                            {$media->getRightPanelMediaDisplay('Attachments', 'enggCrm_opportunity', 'attachment', $row)}
                        </div>
                    </div>
                </div>
                <div class='floatBox row'>
                    <div class='col-md-12 col-sm-12 col-xs-12 noPadding'> 
                        <div class='col-md-6 col-sm-6 col-xs-12'>
                            {$comment->getView(array(
                                 'roomName' => 'enggCrm_opportunity'
                                ,'recordId' => $record_id
                            ))}
                        </div>
                    </div>
                </div>
            </div>
             <div id='tabs-4' aria-labelledby='contractLinkPortal-tab' role='tabpanel' class='col-md-12 col-sm-12 col-xs-12 tab-pane fade noPadding'>
                <div class='mb30 mt10 noPadding col-md-12 col-sm-12 col-xs-12' id='contractLinkPortal'>
                    <div id='addContractPortalView'>
                        {$wMaintenanace}
                    </div>
                </div>
            </div>
        </div>
        ";

        return $text;
    }


     /**
     *
     */
    function getProjectMaintenanacePortal($opportunity_id = '') {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($opportunity_id == ''){
            $opportunity_id = $fn->getReqParam('opportunity_id');
        }

        $SQL = "
        SELECT pm.*
        FROM renewal pm
        WHERE pm.opportunity_id = {$opportunity_id}
        ORDER BY pm.renewal_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
                $renewal = "<a target='_blank' href='index.php?_topRm=finance&module=enggCrm_renewal&renewal_id={$row['renewal_id']}&_action=edit'><u>{$row['store']}</u></a>";

            $rows .= "
            <tr>
                <td>
                    {$renewal}
                </td>
                <td>{$row['date']}</td>
                <td>{$row['time']}</td>
                <td>{$row['completed_by']}</td>
                <td>{$row['service_type']}</td>
            </tr>
            ";
        }


        $text = "
        <div id='renewalPortal' class='linkPortalWrapper'>
            <table class ='list'>
                <thead>
                    <tr>
                        <th  align='left' class='rightPanelHeading'>
                          <div class='float_left rightPanelHeading'>
                              Maintenanace
                          </div>
                          <div class='float_left'>
                              <a class='addMultipleOpportunity btn btn-primary' opportunity_id='{$opportunity_id}'>Add Maintenance</a>
                          </div>
                        </th>
                    </tr>
                    <tr>
                        <th>Store</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Completed By</th>
                        <th>Service Type</th>
                    </tr>
                </thead>
                <tbody class='materialsDetailRow'>
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
    function getAddMultipleMaterials() {
        $fn      = Zend_Registry::get('fn');
        $formObj = Zend_Registry::get('formObj');

        $opportunity_id  = $fn->getReqParam('opportunity_id');

       

        $formAction = "index.php?module=enggCrm_opportunity&_spAction=addMultipleMaterialsSubmit&showHTML=0";

        $expEdit = array("isEditable" => 0);
          $expVl        = array('sqlType' => 'OneField');

        $sqlType = $fn->getValueListSQL('contractType');

            
        $text = "
        <form id='addMultipleOpportunityForm' class='yform columnar addMultipleRenewalForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <fieldset>
                <table width='100%'>
                    <tr>
                     <td>{$formObj->getDateRow('Date', 'date','')}<td>
                <td>{$formObj->getTIMERow('Time', 'time','')}<td>
                <td>{$formObj->getTBRow('Store/Location', 'store', '')}<td>
                        
                    </tr>
                    <tr>
                        <td>{$formObj->getTBRow('Completed By', 'completed_by', '')}<td>
                        <td>{$formObj->getTBRow('Service Type', 'service_type', '')}</td>
                                <td>{$formObj->getDDRowBySQL('Type *', 'contract_type', $sqlType, '', $expVl)}</td>

                    </tr>
                   
                 
                </table>
                <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }


    /**
     *
     */
    function getAddQuoteFormListView($opportunity_id = '', $category = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($opportunity_id == ''){
            $opportunity_id = $fn->getReqParam('opportunity_id');
        }

        $SQL = "
        SELECT q.*                 
        FROM `quote` q
        WHERE q.opportunity_id = {$opportunity_id}
        ORDER BY quote_code DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        $subtotalValue = 0;
        while ($row = $db->sql_fetchrow($result)) {
            if($row['drawing_nos'] == 1) {
                $sqlQuoteItems ="
                SELECT *
                FROM quote_items qi
                WHERE qi.quote_id = {$row['quote_id']}
                  AND qi.opportunity_id = {$row['opportunity_id']}
                ";
                $resultForQuoteItems  = $db->sql_query($sqlQuoteItems);
                $numRowsForQuote      = $db->sql_numrows($resultForQuoteItems);

                $addLineItemView = '';
                if($numRowsForQuote > 0) {
                    $addLineItemView = "
                    <div class='float_right'>
                        <a href='javascript:void(0);' class='quoteLayoutShow'>View Line Items</a>
                    </div>
                    ";
                }
                
                $urlPrintLinkPdf = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=printDrawingQuotePdf&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&showHTML=0";
            } else {
                $sqlQuoteItems ="
                SELECT *
                FROM quote_items qi
                WHERE qi.quote_id = {$row['quote_id']}
                ";

                $resultForQuoteItems  = $db->sql_query($sqlQuoteItems);
                $numRowsForQuoteItems = $db->sql_numrows($resultForQuoteItems);
                $subtotalValue = 0;
                $totalvalue    = 0;
                while ($rowQuoteItems = $db->sql_fetchrow($resultForQuoteItems)) {
                    $subtotal_amount = 0;
                    if($rowQuoteItems['unit_price'] > 0 && $rowQuoteItems['quantity'] > 0) {
                        $subtotal_amount = round($rowQuoteItems['quantity'] * $rowQuoteItems['unit_price'], 2);
                    } elseif ($rowQuoteItems['unit_price'] > 0 && $rowQuoteItems['quantity'] == 0) {
                        $subtotal_amount = round($rowQuoteItems['unit_price'], 2);
                    } elseif ($rowQuoteItems['amount'] > 0) {
                        $subtotal_amount = round($rowQuoteItems['amount'], 2);
                    }

                    $subtotalValue += $subtotal_amount;
                  
                      $totalvalue = $subtotalValue;
                    
                }

                $addLineItemView = '';
                if($totalvalue > 0) {
                    $addLineItemView = "
                    <div class='float_right'>
                        <a href='javascript:void(0);' class='quoteLayoutShow'>View Line Items</a>
                    </div>
                    ";
                }
                
           

               $urlPrintLinkPdf  = "index.php?module=enggCrm_opportunity&_spAction=printLinkForPdfNote&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&showHTML=0";

            
            }

            $quoteActions = '';
            $editForQuote = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=editForQuote&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&showHTML=0";
            $formActionGroupForQuoteLineItem = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=addLineItemForQuoteForm&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&showHTML=0";
            $add_image = $cpCfg['cp.localPath']."images/add.png";
            $print_image = $cpCfg['cp.localPath']."images/icon-print.png";
            $edit_image = $cpCfg['cp.localPath']."images/edit.png";
            $duplicate = $cpCfg['cp.localPath']."images/duplicate.png";

            if ($row['project_id'] == 0 || $row['project_id']  = '' 
                ||  is_null($row['project_id'])) {
                $quoteActions ="
                <div class='float_box clearfix'>
                    <div class='float_left'>
                        <a class='editForQuote' opportunity_id={$opportunity_id} quote_id = {$row['quote_id']}  href='{$editForQuote}' title='Edit Quote'><img src='{$edit_image}' class='icon'></a>
                    </div>    
                    <!--<div class='float_left'>
                        <a  class='deleteAddQuote' quote_id='{$row['quote_id']}'>Delete</a>
                    </div>-->
                    <div class='printLink float_left'>
                        <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
                    </div>
                    <!--<div class='float_left duplicateQuote'>
                        <a class='duplicateQuote' quote_id='{$row['quote_id']}' opportunity_id='{$row['opportunity_id']}' title='Duplicate Quote'><img src='{$duplicate}' class='icon'></a>
                    </div>-->
                    <div class='float_left'>
                        <a opportunity_id={$opportunity_id} quote_id = {$row['quote_id']} class='addMultipleLineItem' title='Add Line Item'><img src='{$add_image}' class='icon'></a>
                    </div>
                </div>
                ";                                        
            } else {
                $quoteActions ="
                <div class='float_left printLink'>
                    <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
                </div>
                ";    
            }   

            $quote_date   = $fn->getCPDate($row['quote_date'], 'd-m-Y');

            $confirmedQuoteStatus = '';
            if($row['quote_status'] == 'Awarded') {
                $confirmedQuoteStatus = 'confirmedQuote';
            }

            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - <br/>' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - <br/> ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }


            if($row['drawing_nos'] == 1) {
                $quote_amount = number_format($row['total_amount'] - $row['discount'], 2);
                $discount = number_format($row['discount'], 2);

                $rows .= "
                <tbody class='quoteDetailRow'>
                    <tr class='addQuoteRow {$confirmedQuoteStatus}'>
                        <td>R-{$row['revision']}</td>
                        <td>
                            <a class='creationModificationQuote' quote_id='{$row['quote_id']}'>
                                <u>{$row['quote_code']}</u>
                            </a>
                        </td>
                        <td>{$quote_date}</td>
                        <td class='quoteStatusTd'>{$row['quote_status']}</td>
                        <td class='txtRight'>{$discount}</td>
                        <td class='txtRight' colspan='2'>{$quote_amount}</td>
                        <td class='' colspan='2'>{$addLineItemView}</td>
                        <td>{$quoteActions}</td>
                    </tr>
                    {$this->getAddLineItemForQuoteListView($opportunity_id,$row['quote_id'])}
                </tbody>
                ";
            } else {
                $quote_amount = number_format($totalvalue - $row['discount'], 2);
                $discount = number_format($row['discount'], 2);

                $rows .= "
                <tbody class='quoteDetailRow'>
                    <tr class='addQuoteRow {$confirmedQuoteStatus}'>
                        <td>R-{$row['revision']}</td>
                        <td>
                            <a class='creationModificationQuote' quote_id='{$row['quote_id']}'>
                                <u>{$row['quote_code']}</u>
                            </a>
                        </td>
                        <td>{$quote_date}</td>
                        <td class='quoteStatusTd'>{$row['quote_status']}</td>
                        <td class='txtRight'>{$discount}</td>
                        <td class='txtRight' colspan='2'>{$quote_amount}</td>
                        <td class='' colspan='2'>{$addLineItemView}</td>
                        <td>{$quoteActions}</td>
                    </tr>
                    {$this->getAddLineItemForQuoteListView($opportunity_id,$row['quote_id'])}
                </tbody>
                ";
            }
        }    

        $sqlForQuoteConvertProj ="
        SELECT *
        FROM quote
        WHERE opportunity_id = {$opportunity_id}
        AND quote_status = 'Awarded' 
        ";

        $resultForQuoteItems  = $db->sql_query($sqlForQuoteConvertProj);                  
        $rowQuoteStatus       = $db->sql_fetchrow($resultForQuoteItems);                  
        $numRowsForQuote      = $db->sql_numrows($resultForQuoteItems);

        if($numRowsForQuote > 0) {
            $statusConfirmed = 'Yes';
        } else {
            $statusConfirmed = 'Not Awarded';
        }

       // After clicking the Converted Project button it will be disabled

        $sqlForProj ="
        SELECT p.*
        FROM project p
        WHERE p.opportunity_id = {$opportunity_id}
        ";
        $resultForProject   = $db->sql_query($sqlForProj);                  
        $rowForProject      = $db->sql_fetchrow($resultForProject);                  
        $numRowsForProject  = $db->sql_numrows($resultForProject);


        $sqlForRene ="
        SELECT p.*
        FROM renewal p
        WHERE p.opportunity_id = {$opportunity_id}
        ";
        $resultForRenewal   = $db->sql_query($sqlForRene);                  
        $rowForRenewal      = $db->sql_fetchrow($resultForRenewal);                  
        $numRowsForRenewal  = $db->sql_numrows($resultForRenewal);


        $sqlForQuote ="
        SELECT q.quote_id
        FROM quote q
        WHERE q.opportunity_id = {$opportunity_id}
        ";
        $resultForQuote   = $db->sql_query($sqlForQuote);                  
        $rowForQuote      = $db->sql_fetchrow($resultForQuote);                  
        $numRowsForQuote  = $db->sql_numrows($resultForQuote);

        $quoteStatus      = '';
        $addQuoteBtn      = '';
        $projectRecBtn    = '';
        $urlprojectRecord = "index.php?_topRm=project&module=enggCrm_project&_action=edit&project_id={$rowForProject['project_id']}";  

        // THIS CONDITIONS IS USED FOR: ONCE THE QUOTE STATUS IS CONFIRMED. THE CONVERT BUTTON WILL BE APPEARED //        
        if($rowQuoteStatus['quote_status'] == 'Awarded' && $numRowsForProject == 0) {  
            $quoteStatus="
            <div class='float_left'>
                <a  class='convertOppToProject btn btn-primary' statusConfirmed='{$statusConfirmed}'>Convert Opp To Project</a>
            </div>
            ";
        
        } 

        // THIS CONDITIONS IS USED FOR: ONCE IT IS CONVERTED TO PROJECT, ADD QUOTE BUTTON WOULD BE HIDE AFTER THAT GO TO PROJECT BUTTON WILL BE SHOWING //        
        if($rowForProject['quote_id'] != 0){
            $projectRecBtn="
            <div class='float_left mb5'>
                <a href='{$urlprojectRecord}' class='btn btn-primary' title='Project Record' target='_blank'>Go to Project</a>
            </div>
            ";            
        }

        if($numRowsForQuote == 0){
            $addQuoteBtn ="
            <div class='float_left mb5'>
                <a  id='addQuote' class='btn btn-primary' opportunity_id='{$opportunity_id}'>Add Quote</a>
            </div>
            ";
        }

        $text = "
        <div class='floatbox'>
            {$addQuoteBtn}
            {$quoteStatus}
            {$projectRecBtn}
        </div>
        "; 

        if ($numRows > 0)  {
            $ChangeHead = "<th class='txtRight' colspan='2'>Amount</th>";
            $text .= "   
            <div id='quotesPortal' class='linkPortalWrapper table-responsive'>
                <table class ='list'>
                    <thead>
                        <tr>
                            <th colspan='7' align='left' class='rightPanelHeading'>
                                Quotations
                              <a class='viewQuoteLog btn btn-primary ml20' opportunity_id='{$opportunity_id}'>View Quote Log</a>
                            </th>
                        </tr>
                        <tr>
                            <th>Revision</th>
                            <th>Quote Code</th>
                            <th>Quote Date</th>
                            <th>Quote Status</th>
                            <th class='txtRight'>Discount</th>
                            {$ChangeHead}
                            <th colspan='2'></th>
                            <th>Action</th>
                        </tr>
                    </thead>
                        {$rows}
                </table>
            </div>    
            ";    
        }
        
        return $text;    
    }


    /**
     *
     */
    function getPrintLinkForPdfNote() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(500000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootquote.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');
        //$pdf->setPrintFooter(false);

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
        $pdf->SetAutoPageBreak(TRUE, 15);

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

        $SQL = "
          SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.quantity
              ,qi.unit
              ,qi.description
              ,qi.amount
              ,qi.unit_price
              ,qi.remarks
              ,o.opportunity_id
              ,o.opportunity_code
              ,o.title AS opportunity_title
              ,o.company_id
              ,o.site_id
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,c.address_town AS billing_address_town
              ,c.address_state AS billing_address_state
              ,gc.name AS billing_address_country
              ,c.address_po_code AS billing_address_po_code
              ,c.company_id
              ,co.email
              ,c.phone
              ,c.fax
              ,c.mobile
              ,co.salutation
              ,co.first_name
              ,e.employee_id AS employeeID
              ,e.employee_name
              ,e.email AS employee_email
              ,e.mobile AS employee_mobile
        FROM quote q
        LEFT JOIN (quote_items qi) ON (qi.quote_id = q.quote_id)
        LEFT JOIN (opportunity o) ON (o.opportunity_id = q.opportunity_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        LEFT JOIN (employee e) ON (e.employee_id = o.employee_id)
        LEFT JOIN (staff s) ON (s.employee_id = q.employee_id)
        WHERE q.quote_id = {$quote_id}
        ORDER BY qi.quote_items_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);
        // get the currency based on site_id
        switch ($company['site_id']) {
    case 1:
        $currency = 'KWD';
        break;

    case 2:
        $currency = 'SAR';
        break;

    default:
        $currency = 'USD';
        break;
}

        $quote_date   = $fn->getCPDate($company['quote_date'], 'd-m-Y');
        $today      = date("d-m-Y");

        $seal='';
        $signname='';

        $tbl1 = '
        <table border="0" width="100%" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#14213d; text-decoration:underline; line-height:26px;">QUOTATION</td>
            </tr>
        </table>
        ';

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = '
            <tr>
                <td style="font-size:12px;">'.strtoupper($company['billing_address_street']).'</td>
                <td colspan="3"></td>
            </tr>
            ';
        }
        $revision = "";
        if ($company['revision'] != "") {
            $revision = '/' . $company['revision'];
        }
        
        $tbl2 ='<table border="0" width="100%" cellpadding="2" cellspacing="0">
                    <tr>
                        <td width="38%" style="font-size:13px; font-weight:bold;  line-height:16px;">To : '.$company['first_name'].'</td>
                       
                    </tr>
                    <tr><td width="38%" ><table border="0" cellpadding="0">
                                
                                <tr>
                                    <td width="100%" style="font-size:12px;font-weight:bold;">'.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="75%" style="font-size:12px;font-weight:bold;">'.$company['billing_address_flat'].'</td>
                                </tr>
                               
                            </table>
                        </td>
                        <td width="24%"></td>
                        <td width="38%" ><table border="0">
                                
                                <tr>
                                    <td width="25%" style="font-size:12px;font-weight:bold;"> Date</td>
                                    <td width="75%" style="font-size:12px;font-weight:bold;">: '.$quote_date.'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:12px;font-weight:bold;"> Ref. No</td>
                                    <td width="75%" style="font-size:12px;font-weight:bold;">: '.$company['quote_code'].''.$revision.'</td>
                                </tr>
                               
                                   
                            </table>
                        </td>
                    </tr>
                </table>
                ';

       
        

      
        $totalvalue      =  $company['total_amount'];
        $amount_in_words = $fn->getConvertNumber($totalvalue);

        $tbl3 = '
        <table border="0">
         <tr>
                <td width="100%" align="left" style="font-size:11px;"><b>Subject</b> : '.$company['subject'].'</td>
            </tr>
          
            <tr>
                <td width="100%" align="left" style="font-size:10px;">'.$company['condition'].'</td>
            </tr>
        </table>';

            $tbl4 = '
                <table border="0">
                    <tr>
                        <td width="13%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Amount</td>
                        <td width="2%"  align="left" style="font-weight:bold; font-size:10px; line-height:20px;">:</td>
                        <td width="85%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">'.$currency.' '.number_format($totalvalue, 3).'</td>
                    </tr>
                    <tr>
                        <td width="13%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Amount In Words</td>
                        <td width="2%"  align="left" style="font-weight:bold; font-size:10px; line-height:20px;">:</td>
                        <td width="85%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">'.$currency.' '.$amount_in_words.'</td>
                    </tr>
                </table>
              <br/><br/><br/>
                <table border="0" width="100%"  style="font-size:10px; ">
          <tr>
                <td align="left" style="line-height:20px;font-weight:bold;font-size:10px;">Note :</td>
            </tr>
            <tr>
                <td align="left" style="font-size:10px;">'.nl2br($company['note']).'</td>
            </tr>
        </table>';

        $tbl5 = '
        <table border="0" width="100%">
            <br/><br/>
            <tr>
                <td border="0" align="left" style="font-weight:bold;font-size:10px;" width="100%">Sincerely,</td>
            </tr>
        </table>
        ';

        $SQLMedia = "
        SELECT file_name, record_type
        FROM media
        WHERE record_id = '{$company['employeeID']}'
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

        $tbl6 = '
        <table border="0" width="100%" cellpadding="3">
            <tr>
            '.$signname.'
            '.$seal.'
            
            </tr>
            <tr>
                <td width="100%" style="font-size:10px;font-weight:bold;">'.$company['employee_name'].'<br/>
                   A Team International<br/>
                      Mob: '.$company['employee_mobile'].' <!--/ 60063220--><br/>
                      Email: '.$company['employee_email'].'</td>  
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');
      
        ob_end_clean();
        $download_title = $company['quote_code'] . '-' . $company['opportunity_title'] . '-A Team' .'-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }


    /**
     *
     */
    function getAddLineItemForQuoteListView($opportunity_id, $quote_id, $category = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $quoteRec = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);

        $SQL = "
        SELECT qt.* 
        FROM `quote_items` qt
        WHERE qt.opportunity_id = {$opportunity_id}
        AND qt.quote_id = {$quote_id}
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $editForLineItem = '';
            $deleteLineItem  = '';
            
            $editText = '';    
            $editForLineItem = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=editLineItem&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&quote_items_id={$row['quote_items_id']}&showHTML=0";

            $SQlForQuoteItems = "
            SELECT q.*                 
            FROM `quote` q
            WHERE q.opportunity_id = {$opportunity_id}
            ";        

            $resultForQuoteItems     = $db->sql_query($SQlForQuoteItems);
            $rowForQuoteItems        = $db->sql_fetchrow($resultForQuoteItems);

            $SQlForQuoteItemsConfiredProj ="
            SELECT *
            FROM project
            WHERE quote_id = {$row['quote_id']}
            ";

            $resultForQuoteItemsConfiredProj    = $db->sql_query($SQlForQuoteItemsConfiredProj);
            $rowForQuoteItemsConfirmedProj     = $db->sql_fetchrow($resultForQuoteItemsConfiredProj);

            $edit_image = $cpCfg['cp.localPath']."images/edit.png";
            $delete_image = $cpCfg['cp.localPath']."images/delete.png";

            if ($rowForQuoteItemsConfirmedProj['quote_id'] == 0 && $rowForQuoteItems['project_id'] == 0) {
                $editText = "
                <div class='float_left'>
                    <a class='editForLineItem' href='{$editForLineItem}' title='Edit'><img src='{$edit_image}' class='icon'></a>
                </div>    
                ";

                $deleteLineItem = "
                <div class='float_left'>
                    <a  class='deleteLineItem' quote_items_id='{$row['quote_items_id']}' quote_id= '{$row['quote_id']}' title='Delete'><img src='{$delete_image}' class='icon'></a></td>
                </div>
                ";    
            }

            $addclass = '';
            if ($row['opportunity_id'] != '') {
                $addclass = 'quoteFromProj';
            }

            $total_amount = 0;
            if($row['unit_price'] > 0 && $row['quantity'] > 0) {
                $total_amount = round($row['quantity'] * $row['unit_price'], 2);
            } elseif ($row['unit_price'] > 0 && $row['quantity'] == 0) {
                $total_amount = round($row['unit_price'], 2);
            } elseif ($row['amount'] > 0) {
                $total_amount = round($row['amount'], 2);
            }

            $total_amount = number_format($total_amount, 2);
            $unit_price   = number_format($row['unit_price'], 2);

            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }

            if($quoteRec['drawing_nos'] == "" || $quoteRec['drawing_nos'] == 0) {
                $rows .= "
                <tr class = 'quoteLayoutHide showAddLineRow {$addclass}'>
                    <td class='emptyTd'></td>
                    <td class='descriptionWrap'>{$row['title']}</td>
                    <td colspan='3' class='descriptionWrap'>{$row['description']}</td>
                    <td align='center'>{$row['quantity']}</td>
                    <td class='amountRow'>{$unit_price}</td>
                    <td class='amountRow'>{$total_amount}</td>
                    <td>{$updation_details}</td>
                    <td>{$editText} {$deleteLineItem}</td>
                </tr>
                ";
            } else {
                $rows .= "
                <tr class = 'quoteLayoutHide showAddLineRow {$addclass}'>
                  <td class='emptyTd'></td>
                  <td colspan='2' class='descriptionWrap'>{$row['drawing_number']}</td>
                  <td colspan='4' class='descriptionWrap'>{$row['drawing_title']}</td>
                  <td align='center'>{$row['drawing_revision']}</td>
                  <td>{$updation_details}</td>
                  <td>{$editText} {$deleteLineItem}</td>
                </tr>";
            }
        }
            
        $text = '';

        if ($numRows > 0)  {
            if($quoteRec['drawing_nos'] == "" || $quoteRec['drawing_nos'] == 0) {
                $text = "
                <tr class = 'quoteLayoutHide showAddLineRow'>
                    <th></th>
                    <th class='quoteRowBackground'>Title</th>
                    <th colspan='3' class='quoteRowBackground'>Description</th>
                    <th class='quoteRowBackground txtCenter'>Qty</th>
                    <th class='quoteRowBackground txtRight'>Unit Price</th> 
                    <th class='quoteRowBackground txtRight'>Amount</th>
                    <th class='quoteRowBackground'>Updated By</th>
                    <th class='quoteRowBackground'>Action</th>
                </tr>
                {$rows}
                ";
            } else {
                $text = "
                <tr class = 'quoteLayoutHide showAddLineRow'>
                    <th></th>
                    <th colspan='2' class='quoteRowBackground'>Drawing Number</th>
                    <th colspan='4' class='quoteRowBackground'>Drawing Title</th>
                    <th class='quoteRowBackground txtCenter'>Revision</th>
                    <th class='quoteRowBackground'>Updated By</th>
                    <th class='quoteRowBackground'>Action</th>
                </tr>
                {$rows}
                ";
            }

            return $text;
        }
    }

    /**
     * Quote Portal Edit
     */
    function getEditForQuoteOld() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');

        $quote_id         = $fn->getReqParam('quote_id');
        $opportunity_id   = $fn->getReqParam('opportunity_id');
        $quote_status     = $fn->getReqParam('quote_status');

        $rowQuote         = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        $quoteItemsRec    = $fn->getRecordRowByID('quote_items', 'quote_id', $rowQuote['quote_id']);

        $formActionEditForQuote = "index.php?module=enggCrm_opportunity&_spAction=editForQuoteSubmit&lnkRoom={$tv['lnkRoom']}&quote_id={$rowQuote['quote_id']}&opportunity_id={$opportunity_id}&showHTML=0";

        $expNoEdit  = array('isEditable' => 0);

        $spArrayQuoteStatus = array('New', 'Submitted', 'Awarded', 'Not Awarded', 'Cancelled');

        $text = "
        <form id='editForQuote' class='yform columnar' method='post' action='{$formActionEditForQuote}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title',$rowQuote['title'])}
                {$formObj->getDateRow('Quote Date', 'quote_date',$rowQuote['quote_date'])}
                {$formObj->getDDRowByArr('Quote Status', 'quote_status', $spArrayQuoteStatus, $rowQuote['quote_status'])}
                {$formObj->getTextAreaRow('Terms & Condition', 'condition',$rowQuote['condition'])}
                {$formObj->getTBRow('Discount', 'discount',$rowQuote['discount'])}
                <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
                <input type='hidden' name='quote_id' value='{$quote_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    function getEditForQuote() {
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil  = Zend_Registry::get('cpUtil');

        $quote_id       = $fn->getReqParam('quote_id');
        $opportunity_id = $fn->getReqParam('opportunity_id');
        $quote_status   = $fn->getReqParam('quote_status');

        $rowOpportunity = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);
        $rowQuote       = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        $quoteItemsRec  = $fn->getRecordRowByID('quote_items', 'quote_id', $rowQuote['quote_id']);

        $formActionEditForQuote = "index.php?module=enggCrm_opportunity&_spAction=editForQuoteSubmit&lnkRoom={$tv['lnkRoom']}&quote_id={$rowQuote['quote_id']}&opportunity_id={$opportunity_id}&showHTML=0";

        $sqlTimesheetType = $fn->getValueListSQL('timesheetType');

        $expVl           = array('sqlType' => 'OneField');
        $expNoEdit       = array('isEditable' => 0);
        $expHideFirstOpt = array('hideFirstOption' => true);
        $expQuoteDate    = array('maxDate' => date('Y-m-d'), 'yearEnd' => date('Y'));

        $status = "<input type='hidden' name='quote_status' value='{$rowQuote['quote_status']}' />";
        $spArrayQuoteStatus = array('New', 'Quoted', 'Awarded', 'Not Awarded', 'Cancelled');

        if ($rowQuote['quote_status'] == 'Order Raised') {
            $status = "{$formObj->getDDRowByArr('Quote Status', 'quote_status', $spArrayQuoteStatus, $rowQuote['quote_status'], $expNoEdit)}";
        } else if ($rowQuote['opportunity_id'] == ''){
            $status = "{$formObj->getDDRowByArr('Quote Status', 'quote_status', $spArrayQuoteStatus, $rowQuote['quote_status'], $expHideFirstOpt)}";
        } else {
            $status = "{$formObj->getDDRowByArr('Quote Status', 'quote_status', $spArrayQuoteStatus, $rowQuote['quote_status'], $expHideFirstOpt)}";
        }

        $introText = '';
        $invoices_payment_terms = '';
        $responsibility = '';
        $manPowerTermsInQuote = '';

        if($rowQuote['project_reference'] == "") {
            $rowQuote['project_reference'] = $rowOpportunity['title'];
        }

        if($rowQuote['drawing_nos'] == "" || $rowQuote['drawing_nos'] == 0) {
           $hideDrawingQuote = "displayNone";
           $hideDefaultQuote = "";
        } else {
           $hideDrawingQuote = "";
           $hideDefaultQuote = "displayNone";
        }

        $drawingYesNo = "
        <td>
          {$formObj->getYesNoRRow('Drawing Nos', 'drawing_nos', $rowQuote['drawing_nos'])}
        </td>";
        if($rowQuote['drawing_nos'] == "1") {
          $expDrawing = array("isEditable" => 0);
          $drawingYesNo = "
          <td>
            {$formObj->getTBRow('Drawing Nos', 'drawing_nos_disabled', $fn->getYesNo($rowQuote['drawing_nos']), $expDrawing)}
            <input type='hidden' name='drawing_nos' value='{$rowQuote['drawing_nos']}'/>
          </td>
          ";
        }

        $expVl        = array('sqlType' => 'OneField');

        $sqlType = $fn->getValueListSQL('contractType');

        $signArray = array(
            "Jassim"
           ,"Ibrahim"
           ,"Wassim"
      );

        $text = "
        <form id='editForQuote' class='yform columnar editQuote' method='post' action='{$formActionEditForQuote}'>
            <fieldset>
                <table width='100%'>
                <tr>                           
                      <label>Quote Revised</label>
                        <div class='col-md-4 col-sm-4 col-xs-12'>
                          <input class='materialVirescoFactory' type='checkbox' name='quote_revised' value='1'>
                        </div>
                      </tr>
                    <tr>
                        <td>{$formObj->getDateRow('Quote Date', 'quote_date',$rowQuote['quote_date'], $expQuoteDate)}</td>
                        <td>{$status}</td>
                        <td>{$formObj->getTBRow('Discount', 'discount', $rowQuote['discount'])}</td>
                    </tr>
                    <tr >
                       <td colspan='2'>{$formObj->getTBRow('Duration', 'project_reference', $rowQuote['project_reference'])}</td>
                        <td>{$formObj->getDDRowByVL('Mode of Payment', 'payment_method', 'paymentQuoteType', $rowQuote['payment_method'])}</td>
                    </tr>
                    <tr>
                        <td class=''>{$formObj->getTBRow('Licence', 'our_reference', $rowQuote['our_reference'])}</td>
                        <td class=''>{$formObj->getTBRow('Ref No', 'ref_no_quote', $rowQuote['ref_no_quote'])}</td>
                       
                        <td>{$formObj->getTBRow('Quote Revision', 'revision', $rowQuote['revision'])}</td>
                    </tr>
                    <tr>
                    <td>{$formObj->getYesNoRRow('Digital Signature','apply_digital_signature', $rowQuote['apply_digital_signature'])}
                    </td>          
                    <td>{$formObj->getDDRowByArr('Signature Name', 'signature_name', $signArray, $rowQuote['signature_name'])}</td>
                    
                    </tr> 
                    <tr>
                        <td colspan='4'>{$formObj->getTextAreaRow('Terms & Condition', 'condition', $rowQuote['condition'])}</td>
                    </tr>
                      <tr>
                        <td colspan='4'>
                          <label>Terms Of Payment</label>
                          {$formObj->getHTMLEditor('', 'intro_quote', $rowQuote['intro_quote'])}
                        </td>
                    </tr>

                    <tr class='drawingQuoteFields'>
                        <td colspan='4'>
                          <label>Job Description</label>
                          {$formObj->getHTMLEditor('Intro Line Items', 'intro_drawing_quote', $rowQuote['intro_drawing_quote'])}
                        </td>
                    </tr>
                </table>
                <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
                <input type='hidden' name='quote_id' value='{$quote_id}' />
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
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $chance             = $fn->getReqParam('chance');
        $category           = $fn->getReqParam('category');
        $company_id         = $fn->getReqParam('company_id');
        $yearMonthStart     = $fn->getReqParam('yearMonthStart');

        $SQLComp = "
        SELECT DISTINCT a.company_id
              ,a.company_name
        FROM company a
        JOIN opportunity b ON (a.company_id = b.company_id)
        ORDER BY company_name
        ";

        $SQLStatus = $fn->getValueListSQL('opportunityStatus');
        $sqlCat    = $fn->getValueListSQL('projectCategory');

        $SQLMonth = "
        SELECT DISTINCT DATE_FORMAT(enquiry_date, '%Y-%m') AS yearMonthStart
              ,DATE_FORMAT(enquiry_date, '%b %Y') AS monthYear
        FROM opportunity
        WHERE DATE_FORMAT(enquiry_date, '%b %Y') IS NOT NULL
        ORDER BY yearMonthStart DESC
        ";

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );


        $text = "
        <td>
            <select name='company_id' class='w100'>
                <option value=''>Company</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $company_id)}
            </select>
        </td>
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $tv['status'])}
            </select>
        </td>
        <td>
            <select name='yearMonthStart'>
                <option value=''>Month</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLMonth, $yearMonthStart)}
            </select>
        </td>
        <td>
            <select name='special_search'>
                <option value=''>Special Search</option>
                {$cpUtil->getDropDown1($spArray, $tv['special_search'])}
            </select>
        </td>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddMultipleLineItemDrawing() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $drawingNumber   = "<textarea type='text' id='drawingNumber' class='text drawingNumber' name='drawing_number[]'></textarea>";
        $drawingTitle    = "<textarea type='text' id='drawingTitle' class='text drawingTitle' name='drawing_title[]'></textarea>";
        $drawingRevision = "<input type='text' value='' id='drawingRevision' class='text drawingRevision' name='drawing_revision[]'>";
        $clear           = "<td class='text'><a  class='clearDrawingLineItem'><u>Clear</u></a></td>";
        
        $text = "
        <tr>
            <td>{$drawingNumber}</td>
            <td>{$drawingTitle}</td>
            <td align='center'>{$drawingRevision}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$drawingNumber}</td>
            <td>{$drawingTitle}</td>
            <td align='center'>{$drawingRevision}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$drawingNumber}</td>
            <td>{$drawingTitle}</td>
            <td align='center'>{$drawingRevision}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$drawingNumber}</td>
            <td>{$drawingTitle}</td>
            <td align='center'>{$drawingRevision}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$drawingNumber}</td>
            <td>{$drawingTitle}</td>
            <td align='center'>{$drawingRevision}</td>
            {$clear}
        </tr>
        ";          

        return $text;
    }

    /**
     *
     */
    function getAddLineDrawingItemRecord() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $drawingNumber   = "<textarea type='text' id='drawingNumber' class='text drawingNumber' name='drawing_number[]'></textarea>";
        $drawingTitle    = "<textarea type='text' id='drawingTitle' class='text drawingTitle' name='drawing_title[]'></textarea>";
        $drawingRevision = "<input type='text' value='' id='drawingRevision' class='text drawingRevision' name='drawing_revision[]'>";
        $clear           = "<td class='text'><a  class='clearDrawingLineItem'><u>Clear</u></a></td>";
      
        $rows = "
        <tr>
            <td>{$drawingNumber}</td>
            <td>{$drawingTitle}</td>
            <td align='center'>{$drawingRevision}</td>
            {$clear}
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getPrintDrawingQuotePdf() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootPrintQuoteDrawing.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');
        $pdf->setPrintFooter(false);

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

        $quote_id       = $fn->getReqParam('quote_id');
        $opportunity_id = $fn->getReqParam('opportunity_id');

        $SQL = "
        SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.drawing_number
              ,qi.drawing_title
              ,qi.drawing_revision
              ,o.opportunity_id
              ,o.opportunity_code
              ,o.company_id
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,c.address_po_code AS billing_address_po_code
              ,c.company_id
              ,co.email
              ,c.phone
              ,c.fax
              ,co.salutation
              ,co.first_name
        FROM quote q
        LEFT JOIN (quote_items qi) ON (qi.quote_id = q.quote_id)
        LEFT JOIN (opportunity o) ON (o.opportunity_id = q.opportunity_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        WHERE q.quote_id       = {$quote_id}
          AND q.opportunity_id = {$opportunity_id}
        ORDER BY qi.quote_items_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = '
            <tr>
                <td style="font-size:10px;line-height:10px;">'.$company['billing_address_street'].'</td>
            </tr>
            ';
        }

        $tbl2 = '
        <table border="0" width="100%" cellpadding="0">
            <tr>
                <td style="font-size:10px; font-weight:bold;line-height:16px;">'.$company['company_name'].'</td>
            </tr>
            <tr>
                <td style="font-size:10px;line-height:16px;">'.$company['billing_address_flat'].'</td>
            </tr>
            ' .  $rowStreet .'
            <tr>
                <td style="font-size:10px;line-height:16px;">'.$company['billing_address_country'].' - '.$company['billing_address_po_code'].'</td>
            </tr>
            <tr>
                <td style="font-size:10px;line-height:16px;">Tel : '.$company['phone'].'</td>
            </tr>
            <tr>
                <td style="font-size:10px;line-height:30px; font-weight:bold;">Attn : '.$company['salutation'].'. '.$company['first_name'].'</td>
            </tr>
        </table>
        ';

        $tbl3 = '
        <div style="font-size:10px;line-height:16px;">
        '.$company['intro_quote'].'
        </div>
        ';

        $tbl4 = '
        <div style="font-size:10px;line-height:16px;">
        '.$company['intro_drawing_quote'].'
        </div>
        ';

        $tbl4 = $tbl4.'<table border="1"  cellpadding="4"  width="100%">
                    <thead>
                        <tr>
                            <th width="5%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S.NO</th>
                            <th width="30%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DRAWING NUMBER</th>
                            <th width="50%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DRAWING TITLE</th>
                            <th width="15%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">REVISION</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        $count = 1;

        //$pdf->ln(10);

        while ($row = $db->sql_fetchrow($result)) {
            $tbl4 = $tbl4.'<tr>
                                <td width="5%"  style="border:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                <td width="30%" align="center" style="border:1px solid #000;font-size:10px;">'.nl2br($row['drawing_number']).'</td>
                                <td width="50%" style="font-size:10px;border:1px solid #000;">'.nl2br($row['drawing_title']).'</td>
                                <td width="15%" align="center" style="font-size:10px;border:1px solid #000;">'.$row['drawing_revision'].'</td>
                            </tr>
                    ';
            $count++;
        }
        
        $tbl4 = $tbl4.'</tbody></table>';

        $tbl5 = '
        <table border="0" width="100%" cellpadding="0">
            <tr>
                <td style="font-size:10px;line-height:18px;">Yours sincerely,</td>
            </tr>
            <tr>
                <td style="font-size:10px;line-height:18px;">'.$cpCfg['cp.companyName'].'</td>
            </tr>
        </table>';
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-10);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->AddPage();
        $pdf->ln(-5);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['opportunity_code'] . '-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getViewQuoteLog() {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $opportunity_id = $fn->getReqParam('opportunity_id');

        $oppRec    = $fn->getRecordRowByID('opportunity', 'opportunity_id', $opportunity_id);
        $companyRec = $fn->getRecordRowByID('company', 'company_id', $oppRec['company_id']);

        $company_prefix = explode(' ', $companyRec['company_name']);
        $length = strlen($company_prefix[0]);
        if ($length > 10) {
            $company_short = substr($company_prefix[0], 0, 10);
            $company_short = strtoupper($company_short);
        } else {
            $company_short = strtoupper($company_prefix[0]);
        }

        $SQL = "
        SELECT q.*
        FROM `quote_log` q
        LEFT JOIN (opportunity p) ON (p.opportunity_id = q.opportunity_id)
        WHERE p.opportunity_id = {$opportunity_id}
        ORDER BY q.quote_code DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        $subtotalValue = 0;
        while ($row = $db->sql_fetchrow($result)) {
          if($row['drawing_nos'] == 1) {
                $quote_date   = $fn->getCPDate($row['quote_date'], 'd-m-Y');

                $sqlQuoteItems ="
                SELECT *
                FROM quote_items_log qi
                WHERE qi.quote_log_id = {$row['quote_log_id']}
                ";
                $resultForQuoteItems  = $db->sql_query($sqlQuoteItems);
                $numRowsForQuoteItems = $db->sql_numrows($resultForQuoteItems);

                $addLineItemView = '';
                if($numRowsForQuoteItems > 0) {
                  $addLineItemView ="
                  <div class='float_right'>
                      <a href='javascript:void(0);' class='quoteLayoutShow'>View Line Items</a>
                  </div>
                  ";
                }

                $quoteActions    = '';
                $urlPrintLinkPdf = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=printDrawingQuoteLogPdf&opportunity_id={$opportunity_id}&quote_log_id={$row['quote_log_id']}&showHTML=0";
          } else {
              $quote_date   = $fn->getCPDate($row['quote_date'], 'd-m-Y');

              $sqlQuoteItems ="
              SELECT *
              FROM quote_items_log qi
              WHERE qi.quote_log_id = {$row['quote_log_id']}
              ";

              $resultForQuoteItems  = $db->sql_query($sqlQuoteItems);
              $subtotalValue = 0;
              $totalvalue    = 0;
              while ($rowQuoteItems = $db->sql_fetchrow($resultForQuoteItems)) {
                  $subtotal_amount = 0; 
                  if($rowQuoteItems['unit_price'] > 0 && $rowQuoteItems['quantity'] > 0) {
                      $subtotal_amount = round($rowQuoteItems['quantity'] * $rowQuoteItems['unit_price'], 2);
                  } elseif ($rowQuoteItems['unit_price'] > 0 && $rowQuoteItems['quantity'] == 0) {
                      $subtotal_amount = round($rowQuoteItems['unit_price'], 2);
                  } elseif ($rowQuoteItems['amount'] > 0) {
                      $subtotal_amount = round($rowQuoteItems['amount'], 2);
                  }

                  $subtotalValue += $subtotal_amount;
                  
                  if($row['gst'] == 1) {
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
              }

              $addLineItemView = '';
              if($totalvalue > 0) {
                  $addLineItemView ="
                  <div class='float_right'>
                      <a href='javascript:void(0);' class='quoteLayoutShow'>View Line Items</a>
                  </div>
                  ";
              }

              $quoteActions = '';

              $urlPrintLinkPdf  = "index.php?_topRm=project&module=enggCrm_opportunity&_spAction=printLinkForLogPdf&opportunity_id={$opportunity_id}&quote_log_id={$row['quote_log_id']}&showHTML=0";
          }

            $quoteActions = '';
            $print_image = $cpCfg['cp.localPath']."images/icon-print.png";

            $quoteActions ="
            <div class='float_left printLink'>
                <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
            </div>
            ";    
            $quote_date   = $fn->getCPDate($row['quote_date'], 'd-m-Y');
            
            $confirmedQuoteStatus = '';
            if($row['quote_status'] == 'Awarded') {
                $confirmedQuoteStatus = 'confirmedQuote';
            }

            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - <br/>' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - <br/> ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }

            if($row['drawing_nos'] == 1) {
                $quote_amount = number_format($row['total_amount'] - $row['discount'], 2);
                $discount = number_format($row['discount'], 2);

                $rows .= "
                <tbody class='quoteDetailRow'>
                    <tr class='addQuoteRow {$confirmedQuoteStatus}'>
                        <td>{$row['revision']}</td>
                        <td>
                            <a class='creationModificationQuote' quote_log_id='{$row['quote_log_id']}'>
                                <u>{$row['quote_code']}</u>
                            </a>
                        </td>
                        <td>{$quote_date}</td>
                        <td class='quoteStatusTd'>{$row['quote_status']}</td>
                        <td class='txtRight'>{$discount}</td>
                        <td class='txtRight' colspan='2'>{$quote_amount}</td>
                        <td class='' colspan='2'>{$addLineItemView}</td>
                        <td>{$quoteActions}</td>
                    </tr>
                    {$this->getAddLineItemForQuoteLogListView($opportunity_id,$row['quote_log_id'])}
                </tbody>
                ";
            } else {
                $quote_amount = number_format($totalvalue - $row['discount'], 2);
                $discount = number_format($row['discount'], 2);

                $rows .= "
                <tbody class='quoteDetailRow'>
                    <tr class='addQuoteRow {$confirmedQuoteStatus}'>
                        <td>{$row['revision']}</td>
                        <td>
                            <a class='creationModificationQuote' quote_log_id='{$row['quote_log_id']}'>
                                <u>{$row['quote_code']}</u>
                            </a>
                        </td>
                        <td>{$quote_date}</td>
                        <td class='quoteStatusTd'>{$row['quote_status']}</td>
                        <td class='txtRight'>{$discount}</td>
                        <td class='txtRight' colspan='2'>{$quote_amount}</td>
                        <td class='' colspan='2'>{$addLineItemView}</td>
                        <td>{$quoteActions}</td>
                    </tr>
                    {$this->getAddLineItemForQuoteLogListView($opportunity_id,$row['quote_log_id'])}
                </tbody>
                ";
            }
        }

        $sqlForQuoteConvertProj ="
        SELECT *
        FROM quote
        WHERE opportunity_id = {$opportunity_id}
        AND quote_status = 'Awarded' 
        ";

        $resultForQuoteItems  = $db->sql_query($sqlForQuoteConvertProj);                  
        $rowQuoteStatus       = $db->sql_fetchrow($resultForQuoteItems);                  
        $numRowsForQuote      = $db->sql_numrows($resultForQuoteItems);

        if($numRowsForQuote > 0) {
            $statusConfirmed = 'Yes';
        } else {
            $statusConfirmed = 'Not Awarded';
        }

          $text = '';

          if($numRows > 0)  {
            $ChangeHead = "<th class='txtRight' colspan='2'>Amount</th>";
            
            $text .= "
            <div id='quotesPortal' class='linkPortalWrapper table-responsive'>
                <table class ='list'>
                    <thead>
                        <tr>
                            <th colspan='9' align='left' class='rightPanelHeading'>
                              Quotations
                            </th>
                        </tr>
                        <tr>
                            <th>Revision</th>
                            <th>Quote Code</th>
                            <th>Quote Date</th>
                            <th>Quote Status</th>
                            <th class='txtRight'>Discount</th>
                            {$ChangeHead}
                            <th colspan='2'></th>
                            <th>Action</th>
                        </tr>
                    </thead>
                        {$rows}
                </table>
            </div>
            ";
          } else {
            $text = "No history records found.";
          }

          return $text;
    }

    /**
     *
     */
    function getAddLineItemForQuoteLogListView($opportunity_id, $quote_log_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $quoteRec = $fn->getRecordRowByID('quote_log', 'quote_log_id', $quote_log_id);

        $SQL = "
        SELECT qt.* 
        FROM `quote_items_log` qt
        WHERE qt.opportunity_id = {$opportunity_id}
        AND qt.quote_log_id = {$quote_log_id}
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $editForLineItem = '';
            $deleteLineItem  = '';
            
            $editText = '';    

            $SQlForQuoteItems = "
            SELECT q.*                 
            FROM `quote_log` q
            WHERE q.opportunity_id = {$opportunity_id}
            ";        

            $resultForQuoteItems     = $db->sql_query($SQlForQuoteItems);
            $rowForQuoteItems        = $db->sql_fetchrow($resultForQuoteItems);

            $addclass = '';
            if ($row['opportunity_id'] != '') {
                $addclass = 'quoteFromProj';
            }

            $total_amount = 0;
            if($row['unit_price'] > 0 && $row['quantity'] > 0) {
                $total_amount = round($row['quantity'] * $row['unit_price'], 2);
            } elseif ($row['unit_price'] > 0 && $row['quantity'] == 0) {
                $total_amount = round($row['unit_price'], 2);
            } elseif ($row['amount'] > 0) {
                $total_amount = round($row['amount'], 2);
            }

            $total_amount = number_format($total_amount, 2);
            $unit_price   = number_format($row['unit_price'], 2);

            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }

            if($quoteRec['drawing_nos'] == "" || $quoteRec['drawing_nos'] == 0) {
                $rows .= "
                <tr class = 'quoteLayoutHide showAddLineRow {$addclass}'>
                    <td class='emptyTd'></td>
                    <td class='descriptionWrap'>{$row['title']}</td>
                    <td colspan='3' class='descriptionWrap'>{$row['description']}</td>
                    <td align='center'>{$row['quantity']}</td>
                    <td class='amountRow'>{$unit_price}</td>
                    <td class='amountRow'>{$total_amount}</td>
                    <td>{$updation_details}</td>
                </tr>
                ";
            } else {
                $rows .= "
                <tr class = 'quoteLayoutHide showAddLineRow {$addclass}'>
                  <td class='emptyTd'></td>
                  <td colspan='2' class='descriptionWrap'>{$row['drawing_number']}</td>
                  <td colspan='4' class='descriptionWrap'>{$row['drawing_title']}</td>
                  <td align='center'>{$row['drawing_revision']}</td>
                  <td>{$updation_details}</td>
                </tr>";
            }
        }
            
        $text = '';

        if ($numRows > 0)  {
            if($quoteRec['drawing_nos'] == "" || $quoteRec['drawing_nos'] == 0) {
                $text = "
                <tr class = 'quoteLayoutHide showAddLineRow'>
                    <th></th>
                    <th class='quoteRowBackground'>Title</th>
                    <th colspan='3' class='quoteRowBackground'>Description</th>
                    <th class='quoteRowBackground txtCenter'>Qty</th>
                    <th class='quoteRowBackground txtRight'>Unit Price</th> 
                    <th class='quoteRowBackground txtRight'>Amount</th>
                    <th class='quoteRowBackground'>Updated By</th>
                </tr>
                {$rows}
                ";
            } else {
                $text = "
                <tr class = 'quoteLayoutHide showAddLineRow'>
                    <th></th>
                    <th colspan='2' class='quoteRowBackground'>Drawing Number</th>
                    <th colspan='4' class='quoteRowBackground'>Drawing Title</th>
                    <th class='quoteRowBackground txtCenter'>Revision</th>
                    <th class='quoteRowBackground'>Updated By</th>
                    <th class='quoteRowBackground'>Action</th>
                </tr>
                {$rows}
                ";
            }

            return $text;
        }
    }

    /**
     *
     */
    function getPrintLinkForLogPdf() {
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
        //$pdf->setPrintFooter(false);

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

        $quote_log_id = $fn->getReqParam('quote_log_id');

        $SQL = "
        SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.quantity
              ,qi.unit
              ,qi.description
              ,qi.amount
              ,qi.unit_price
              ,qi.remarks
              ,o.opportunity_id
              ,o.opportunity_code
              ,o.company_id
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,c.address_town AS billing_address_town
              ,c.address_state AS billing_address_state
              ,gc.name AS billing_address_country
              ,c.address_po_code AS billing_address_po_code
              ,c.company_id
              ,co.email
              ,c.phone
              ,c.fax
              ,c.mobile
              ,co.salutation
              ,co.first_name
              ,s.email AS employee_email
              ,e.mobile AS employee_mobile
        FROM quote_log q
        LEFT JOIN (quote_items_log qi) ON (qi.quote_log_id = q.quote_log_id)
        LEFT JOIN (opportunity o) ON (o.opportunity_id = q.opportunity_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        LEFT JOIN (employee e) ON (e.employee_id = q.employee_id)
        LEFT JOIN (staff s) ON (s.employee_id = q.employee_id)
        WHERE q.quote_log_id = {$quote_log_id}
        ORDER BY qi.quote_items_log_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $quote_date   = $fn->getCPDate($company['quote_date'], 'd-m-Y');
        $today      = date("d-m-Y");

        $sqlCompAdd = "
        SELECT ca.*
        FROM company_address ca
        WHERE ca.company_id = {$company['company_id']}
        LIMIT 0,1
        ";
        $resultCompAdd = $db->sql_query($sqlCompAdd);
        $rowCompAdd = $db->sql_fetchrow($resultCompAdd);

        $tbl1 = '
        <table border="0" width="100%" style="border-top: 1px solid #0e502a;" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#078205; text-decoration:underline; line-height:26px;">QUOTATION</td>
            </tr>
        </table>
        ';

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = ',<br/>: '.$company['billing_address_street'];
        }

        $tbl2 ='<table border="0" width="100%" cellpadding="2" cellspacing="0">
                    <tr>
                        <td width="38%" style="font-size:10px; font-weight:bold; background-color:#92d14f; border:1px solid #000; line-height:16px;">Quotation To : </td>
                        <td width="24%"></td>
                        <td width="38%" style="font-size:10px; font-weight:bold; background-color:#92d14f; border:1px solid #000; line-height:16px;">Quotation From :</td>
                    </tr>
                    <tr><td width="38%" style="border:1px solid #000;"><table border="0" cellpadding="0">
                                <tr>
                                    <td width="25%" style="font-size:10px;">Name</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['first_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">CO. Name</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Address</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['billing_address_flat'].$rowStreet.', <br/>: '.$company['billing_address_country'].' - '.$company['billing_address_po_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Email</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['email'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">HP No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['mobile'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Tel</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['phone'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Fax</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['fax'].'</td>
                                </tr>
                            </table>
                        </td>
                        <td width="24%"></td>
                        <td width="38%" style="border:1px solid #000;"><table border="0">
                                <tr>
                                    <td width="25%" style="font-size:10px;">Ref. No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['quote_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Date</td>
                                    <td width="75%" style="font-size:10px;">: '.$quote_date.'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Payment</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['payment_method'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Email</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['employee_email'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">HP No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['employee_mobile'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Tel</td>
                                    <td width="75%" style="font-size:10px;">: '.$cpCfg['cp.companyPhone'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Fax</td>
                                    <td width="75%" style="font-size:10px;">: '.$cpCfg['cp.companyFax'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Created by</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['created_by'].'</td>
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
                        <tr bgcolor="#92d14f">
                            <th width="6%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                            <th width="55%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DESCRIPTION</th>
                            <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                            <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT</th>
                            <th width="12%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT PRICE($)</th>
                            <th width="13%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">TOTAL PRICE($)</th>
                        </tr>
                    </thead>
                    <tbody style="display: table; table-layout: fixed; height: 600px;">';
        
        $subtotalValue = 0;
        $count      = 1;
        $countCheck = 1;
        while ($row = $db->sql_fetchrow($result)) {

            if ($row['quote_item_title']) {
                $countCheck++;
                $tbl3 = $tbl3.'<tr>
                                    <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="55%" style="font-size:10px; font-weight:bold; border-left:1px solid #000;border-right:1px solid #000;"><u>'.nl2br($row['quote_item_title']).'</u><br/></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                    <td width="13%" style="border-right:1px solid #000;"></td>
                                </tr>
                        ';
            }

            $subtotal_amount = 0;
            if ($row['amount'] != "") {
                $subtotal_amount = round($row['amount'], 2);
            } else if($row['unit_price'] > 0 && $row['qty'] > 0) {
                $subtotal_amount = round($row['qty'] * $row['unit_price'], 2);
            } else if ($row['unit_price'] > 0 && $row['qty'] == 0) {
                $subtotal_amount = round($row['unit_price'], 2);
            }

            $subtotal_amount_formatted = number_format($subtotal_amount, 2);

            if($row['quantity'] == 0) {
                $row['quantity'] = "";
            }

            if($row['unit_price'] == 0) {
                $row['unit_price'] = "";
            }

            if($subtotal_amount_formatted == "0.000") {
                $subtotal_amount_formatted = "";
            }

            $tbl3 = $tbl3.'<tr>
                                <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit'].'</td>
                                <td width="12%" align="right" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit_price'].'</td>
                                <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;">'.$subtotal_amount_formatted.'</td>
                            </tr>
                    ';

            $subtotalValue += $subtotal_amount;

            if($company['gst'] == 1) {
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

        if($company['discount']) {
            $tbl3 = $tbl3.'<tr>
                                <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;"></td>
                                <td width="55%" style="font-size:10px; border-left:1px solid #000;border-right:1px solid #000;"><br/><br/>Less Discount</td>
                                <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                <td width="7%"  style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                <td width="12%" style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                                <td width="13%" style="font-size:10px;border-right:1px solid #000;" align="right"><br/><br/>-'.number_format($company['discount'], 2).'</td>
                            </tr>
                    ';
        }

        $totalvalue      = $totalvalue - $company['discount'];
        $amount_in_words = $fn->getConvertNumber($totalvalue);

        if($company['gst'] == 1) {
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

        if($company['gst'] == 1) {
            $tbl3 = $tbl3.'<tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;"></td>
                              <td align="right" colspan="3" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;">SUB TOTAL</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;">'.number_format($subtotalValue - $company['discount'],2).'</td>
                          </tr>
                          <tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; font-weight:bold;">GST '.$cpCfg['cp.gstPercentage'].'% </td>
                              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000;">'.number_format($gstvalue, 2).'</td>
                           </tr>
                           <tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-right:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">TOTAL AMOUNT</td>
                              <td align="right" style="font-size:10px; font-weight:bold;border-right:1px solid #000; border-bottom:1px solid #000;">'.number_format($totalvalue, 2).'</td>
                           </tr>
                          </tbody>
                        </table>';
        } else {
            $tbl3 = $tbl3.'<tr>
                              <td align="right" colspan="2" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;"></td>
                              <td colspan="3" align="right" style="font-size:10px; border-top:1px solid #000;border-right:1px solid #000; border-bottom:1px solid #000; font-weight:bold;">TOTAL EXCLUDING GST</td>
                              <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000; border-right:1px solid #000; border-bottom:1px solid #000;">'.number_format($totalvalue, 2).'</td>
                           </tr>
                          </tbody>
                        </table>';
        }

        $tbl4 = '
        <table border="1" width="100%" cellpadding="2">
            <tr>
                <td align="left" width="100%" style="font-size:10px; font-weight:bold; background-color:#92d14f;">Other Comments or Special Instructions :</td>
            </tr>
            <tr>
                <td align="left" style="line-height:16px;font-size:10px;">'.nl2br($company['condition']).'</td>
            </tr>
        </table>';

        $tbl5 = '
        <table border="0" width="100%">
            <br/><br/>
            <tr>
                <td border="0" align="left" style="font-size:10px;" width="100%">Yours Faithfully,</td>
            </tr>
        </table>
        ';

        $tbl6 = '
        <table border="0" width="100%" cellpadding="3">
            <tr>
                <td width="40%" style="border-bottom:1px solid black"></td>
                <td width="30%"></td>
                <td width="30%" style="border-bottom:1px solid black"></td>
            </tr>
            <tr>
                <td style="font-size:10px;">Authorised Signature / Date</td>
                <td></td>
                <td style="font-size:10px;">Accepted By / Date</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-5);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['opportunity_code'] . '-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintDrawingQuoteLogPdf() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootPrintQuoteDrawing.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');
        //$pdf->setPrintFooter(false);

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

        $quote_log_id = $fn->getReqParam('quote_log_id');
        $opportunity_id = $fn->getReqParam('opportunity_id');

        $SQL = "
        SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.drawing_number
              ,qi.drawing_title
              ,qi.drawing_revision
              ,o.opportunity_id
              ,o.opportunity_code
              ,o.company_id
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,c.address_po_code AS billing_address_po_code
              ,c.company_id
              ,co.email
              ,c.phone
              ,c.fax
              ,co.salutation
              ,co.first_name
        FROM quote_log q
        LEFT JOIN (quote_items_log qi) ON (qi.quote_log_id = q.quote_log_id)
        LEFT JOIN (opportunity o) ON (o.opportunity_id = q.opportunity_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        WHERE q.quote_log_id       = {$quote_log_id}
          AND q.opportunity_id = {$opportunity_id}
        ORDER BY qi.quote_items_log_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $rowStreet = '';
        if ($company['billing_address_street']) {
            $rowStreet = '
            <tr>
                <td style="font-size:10px;line-height:10px;">'.$company['billing_address_street'].'</td>
            </tr>
            ';
        }

        $tbl2 = '
        <table border="0" width="100%" cellpadding="0">
            <tr>
                <td style="font-size:10px; font-weight:bold;line-height:16px;">'.$company['company_name'].'</td>
            </tr>
            <tr>
                <td style="font-size:10px;line-height:16px;">'.$company['billing_address_flat'].'</td>
            </tr>
            ' .  $rowStreet .'
            <tr>
                <td style="font-size:10px;line-height:16px;">'.$company['billing_address_country'].' - '.$company['billing_address_po_code'].'</td>
            </tr>
            <tr>
                <td style="font-size:10px;line-height:16px;">Tel : '.$company['phone'].'</td>
            </tr>
            <tr>
                <td style="font-size:10px;line-height:30px; font-weight:bold;">Attn : '.$company['salutation'].'. '.$company['first_name'].'</td>
            </tr>
        </table>
        ';

        $tbl3 = '
        <div style="font-size:10px;line-height:16px;">
        '.$company['intro_quote'].'
        </div>
        ';

        $tbl4 = '
        <div style="font-size:10px;line-height:16px;">
        '.$company['intro_drawing_quote'].'
        </div>
        ';

        $tbl4 = $tbl4.'<table border="1"  cellpadding="4"  width="100%">
                    <thead>
                        <tr>
                            <th width="5%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S.NO</th>
                            <th width="30%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DRAWING NUMBER</th>
                            <th width="50%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DRAWING TITLE</th>
                            <th width="15%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">REVISION</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        $count = 1;

        //$pdf->ln(10);

        while ($row = $db->sql_fetchrow($result)) {
            $tbl4 = $tbl4.'<tr>
                                <td width="5%"  style="border:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                <td width="30%" align="center" style="border:1px solid #000;font-size:10px;">'.nl2br($row['drawing_number']).'</td>
                                <td width="50%" style="font-size:10px;border:1px solid #000;">'.nl2br($row['drawing_title']).'</td>
                                <td width="15%" align="center" style="font-size:10px;border:1px solid #000;">'.$row['drawing_revision'].'</td>
                            </tr>
                    ';
            $count++;
        }
        
        $tbl4 = $tbl4.'</tbody></table>';

        $tbl5 = '
        <table border="0" width="100%" cellpadding="0">
            <tr>
                <td style="font-size:10px;line-height:18px;">Yours sincerely,</td>
            </tr>
            <tr>
                <td style="font-size:10px;line-height:18px;">'.$cpCfg['cp.companyName'].'</td>
            </tr>
        </table>';
        $pdf->ln(10);

        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-10);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->AddPage();
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['opportunity_code'] . '-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }
}