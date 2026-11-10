<?
class CPL_Admin_Widgets_EnggCrm_ProjectQuoteRenewal_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        return $text;
    }

    /**
     *
     */
    function getAddQuoteFormListView($opportunity_id = '', $renewal_id = '', $category = '') {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($opportunity_id == ''){
            $opportunity_id = $fn->getReqParam('opportunity_id');
        }
        if($renewal_id == ''){
            $renewal_id = $fn->getReqParam('renewal_id');
        }
        if($category == ''){
            $category = $fn->getReqParam('category');
        }

        $projRec    = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);
        

        $SQL = "
        SELECT q.*
        FROM `quote` q
        LEFT JOIN (renewal p) ON (p.renewal_id = q.renewal_id)
        WHERE p.renewal_id = {$renewal_id}
        ORDER BY q.quote_code DESC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        $subtotalValue = 0;
        while ($row = $db->sql_fetchrow($result)) {

              $quote_date   = $fn->getCPDate($row['quote_date'], 'd-m-Y');

              $sqlQuoteItems ="
              SELECT *
              FROM quote_items qi
              WHERE qi.quote_id = '{$row['quote_id']}'
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
              $editForQuote = "index.php?widget=enggCrm_projectQuoteRenewal&_spAction=editForQuote&renewal_id={$renewal_id}&quote_id={$row['quote_id']}&showHTML=0";

              $urlPrintLinkPdf  = "index.php?widget=enggCrm_projectQuoteRenewal&_spAction=printLinkForPdfNote&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&showHTML=0";

              $formActionGroupForQuoteLineItem = "index.php?widget=enggCrm_projectQuoteRenewal&_spAction=addLineItemForQuoteForm&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&renewal_id={$renewal_id}&showHTML=0";

              $add_image = $cpCfg['cp.localPath']."images/add.png";
              $print_image = $cpCfg['cp.localPath']."images/icon-print.png";
              $edit_image = $cpCfg['cp.localPath']."images/edit.png";

              $quoteActions ="
                  <div class='float_box clearfix'>
                      <div class='float_left'>
                          <a class='editForRenewal' href='{$editForQuote}' title='Edit Quote'><img src='{$edit_image}' class='icon'></a>
                      </div>
                      <div class='printLink'>
                          <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
                      </div>
                  </div>
                  ";
          

              $updation_details = '';
              if ($row['modified_by']) {
                  $updation_details = $row['modified_by'] . ' - <br/>' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
              } else {
                  $updation_details = $row['created_by'] . ' - <br/> ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
              }

              $confirmedQuoteStatus = '';
              if($row['quote_status'] == 'Awarded') {
                  $confirmedQuoteStatus = 'confirmedQuote';
              }

              $cancelledQuoteStatus = '';
              if($row['quote_status'] == 'Cancelled') {
                  $cancelledQuoteStatus = 'cancelledQuote';
              }

              $quote_amount = number_format($totalvalue - $row['discount'], 2);

              if($category == 'Manpower Supply'){
                  $quote_amount = '';
              }

              $discount = number_format($row['discount'], 2);

              $revision = "";
              if($row['revision'] != "") {
                  $revision = $row['revision']. ' ';
              }

              $amountColumn = '';
              if ($_SESSION['userGroupName'] != 'Projects') {
                $amountColumn = "
                <td data-label='Amount' class='txtRight' colspan='2'>{$row['total_amount']}</td>
                ";
              }

              $rows .= "
              <tbody class='quoteDetailRow'>
                  <tr class='addQuoteRow  {$cancelledQuoteStatus}'>
                      <td data-label='Quote Code'>
                          <a class='creationModificationQuote3' quote_id='{$row['quote_id']}'>
                              <u>{$row['quote_code']}</u>
                          </a>
                      </td>
                         <td data-label='Revision'><p>{$row['note']}</p></td>
                      <td data-label='Quote Date'>{$quote_date}</td>
                      <td data-label='Quote Status' class='quoteStatusTd'>{$row['quote_status']}</td>
                      {$amountColumn}
                      <td data-label='' class='' colspan='2'>{$addLineItemView}</td>
                      <td data-label='Action'>{$quoteActions}</td>
                  </tr>
                  {$this->getAddLineItemForQuoteListView($opportunity_id,$renewal_id,$row['quote_id'], $category)}
              </tbody>
              ";

          

        }

          $text = '';

          /*$gotoProjectBtn = '';
          $generateFinanceRecordLbl = "Generate Finance Record";
          $orderRows = $fn->getRecordCount('order', "renewal_id = {$renewal_id}");
          if ($orderRows > 0) {
              $orderRec = $fn->getRecordRowByID('order', 'renewal_id', $renewal_id);
              $urlOrderRecord = "index.php?widget=enggCrm_projectQuoteRenewal&_action=edit&order_id={$orderRec['order_id']}";
              $gotoProjectBtn = "
              <div class='btn btn-danger mb10'>
                  <a href='{$urlOrderRecord}' title='Order Record' target='_blank'>Goto Finance</a>
              </div>
              ";

              $generateFinanceRecordLbl = "Update Finance Record";
          }

          $quoteConfirmCount = $fn->getRecordCount('quote', "renewal_id = {$renewal_id} AND (quote_status = 'Awarded' OR quote_status = 'Order Raised')");

          $orderBtn = '';
          if ($quoteConfirmCount){
              $generateOrderRecordClass = "generateOrderRecords";
              $rowQuote = $fn->getRecordByCondition('quote', "renewal_id = '{$renewal_id}' AND quote_status = 'Awarded'", 'quote_id ASC');

              $orderBtn = "
              <div class='btn btn-danger mb10 mr10'>
                  <a  class='{$generateOrderRecordClass}' quote_id='{$rowQuote['quote_id']}' renewal_id='{$renewal_id}'>{$generateFinanceRecordLbl}</a>
              </div>
              ";
          }*/

          // if($numRows == 0) {
          //   $text = "
          //   <div class='float_box mt10 mb10'>
          //     <a id='addQuoteProject' class='btn btn-primary' renewal_id='{$renewal_id}'>Add Quote</a>
          //   </div>
          //   ";
          // }
          $text = "
          <div class='float_box mt10 mb10'>
            <a id='addQuoteProject' class='btn btn-primary' renewal_id='{$renewal_id}'>Add Quote</a>
          </div>
          ";
          if($numRows > 0)  {
            $ChangeHead = '';
            if ($_SESSION['userGroupName'] != 'Projects') {
              $ChangeHead = "<th scope='col' class='txtRight' colspan='2'>Amount</th>"; 
            }           
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
                            <th scope='col'>Quote Code</th>
                             <th scope='col'>Job Description</th>
                            <th scope='col'>Quote Date</th>
                            <th scope='col'>Quote Status</th>
                            {$ChangeHead}
                            <th scope='col' colspan='2'></th>
                            <th scope='col'>Action</th>
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
       
        FROM quote q
        LEFT JOIN (quote_items qi) ON (qi.quote_id = q.quote_id)
        LEFT JOIN (renewal o) ON (o.renewal_id = q.renewal_id)
        LEFT JOIN (company c) ON (c.company_id = o.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = o.contact_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        LEFT JOIN (staff s) ON (s.employee_id = q.employee_id)
        WHERE q.quote_id = {$quote_id}
        ORDER BY qi.quote_items_id ASC
        ";
        $result = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

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
                        <td width="38%" style="font-size:13px; font-weight:bold;  line-height:16px;">To : </td>
                       
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

        $tbl4 = '
        <table border="0">
          
            <tr>
                <td width="100%" align="left" style="font-size:10px;">'.$company['condition'].'</td>
            </tr>
        </table>
        <br/><br/>
                <table border="0">
                    <tr>
                        <td width="13%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Amount</td>
                        <td width="2%"  align="left" style="font-weight:bold; font-size:10px; line-height:20px;">:</td>
                        <td width="85%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">KWD '.number_format($totalvalue, 3).'</td>
                    </tr>
                    <tr>
                        <td width="13%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Amount In Words</td>
                        <td width="2%"  align="left" style="font-weight:bold; font-size:10px; line-height:20px;">:</td>
                        <td width="85%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">KWD '.$amount_in_words.'</td>
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
        //$pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');
      
        ob_end_clean();
        $download_title = $company['quote_code'] . '-A Team' .'-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }


    /**
     *
     */
    function getAddLineItemForQuoteListView($opportunity_id, $renewal_id, $quote_id, $category) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $SQL = "
        SELECT qt.*
              ,q.drawing_nos
              ,r.renewal_id
        FROM `quote_items` qt
        LEFT JOIN quote q ON (qt.quote_id = q.quote_id)
                LEFT JOIN renewal r ON (r.quote_id = q.quote_id)
        WHERE r.renewal_id = {$renewal_id}
        AND qt.quote_id = {$quote_id}
        ORDER BY qt.quote_items_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows        = '';
        $drawing_nos = '';
        while ($row = $db->sql_fetchrow($result)) {
            $editForLineItem = '';
            $deleteLineItem  = '';
            $edit_image      = $cpCfg['cp.localPath']."images/edit.png";
            $delete_image    = $cpCfg['cp.localPath']."images/delete.png";

            $editForLineItem = "index.php?widget=enggCrm_projectQuoteRenewal&_spAction=editLineItem&opportunity_id={$opportunity_id}&quote_id={$row['quote_id']}&quote_items_id={$row['quote_items_id']}&renewal_id={$renewal_id}&showHTML=0";

            $editText = "
            <div class='float_left'>
                <a class='editForLineItem' href='{$editForLineItem}' title='Edit Line Item'><img src='{$edit_image}' class='icon' opportunity_id='{$opportunity_id}' category='{$category}'></a>
            </div>
            ";

            $deleteLineItem = "
            <div class='float_left'>
                <a  class='deleteLineItem' quote_items_id='{$row['quote_items_id']}' quote_id= '{$row['quote_id']}'  title='Delete Line Item'><img src='{$delete_image}' class='icon'></a></td>
            </div>
            ";

            $addclass = '';
            if ($row['renewal_id'] != '') {
                $addclass = 'quoteFromProj';
            }

            if($row['drawing_nos'] == "" || $row['drawing_nos'] == 0) {
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
              
              $priceColumnVal = '';
              if ($_SESSION['userGroupName'] != 'Projects') {
                $priceColumnVal = "
                <td class='amountRow'>{$unit_price}</td>
                <td class='amountRow'>{$total_amount}</td>
                ";
              }

              $rows .= "
              <tr class = 'quoteLayoutHide showAddLineRow {$addclass}'>
                  <td class='emptyTd'></td>
                  <td class='descriptionWrap'>{$row['title']}</td>
                  <td colspan='3' class='descriptionWrap'>{$row['description']}</td>
                  <td align='center'>{$row['quantity']}</td>
                  {$priceColumnVal}
                  <td>{$updation_details}</td>
                  <td>{$editText} {$deleteLineItem}</td>
              </tr>
              ";
            } else {
              $updation_details = '';
              if ($row['modified_by']) {
                  $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
              } else {
                  $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
              }

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

            $drawing_nos = $row['drawing_nos'];
        }

        $text = '';

        if ($numRows > 0)  {
            if($drawing_nos == "" || $drawing_nos == 0) {
              $priceColumn = '';
                if ($_SESSION['userGroupName'] != 'Projects') {
                  $priceColumn = "
                  <th class='quoteRowBackground txtRight'>Unit Price</th> 
                  <th class='quoteRowBackground txtRight'>Amount</th>
                  ";
                }
                $text = "
                <tr class = 'quoteLayoutHide showAddLineRow'>
                    <!--<th class='quoteRowBorder'></th>
                    <th class='quoteRowBordersecond'></th>-->
                    <th></th>
                    <th class='quoteRowBackground'>Title</th>
                    <th colspan='3' class='quoteRowBackground'>Description</th>
                    <th class='quoteRowBackground txtCenter'>Qty</th>
                    {$priceColumn}
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
     *
     */
    function getAddMultipleLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $renewal_id     = $fn->getReqParam('renewal_id');
        $quote_id       = $fn->getReqParam('quote_id');
        $sqlNationality = $fn->getValueListSQL('nationality');
        $sqlCategory    = $fn->getValueListSQL('employeeCategory');
        
        $rowProject = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);
        $rowQuote   = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);

        if($rowQuote['drawing_nos'] == 1) {
            $rows = $this->getAddMultipleLineItemDrawing();

            $newRow = "
            <a  class='addDrawingRow btn btn-primary mb10' renewal_id='{$renewal_id}'>Add Line Item</a>
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
            $part_no     = "<input type='text' value='' id='partno' class='text lineItemPartno' name='partno[]'>";
            $description = "<textarea type='text' value='' id='description' class='text lineItemDescription' name='description[]'></textarea>";
            $title       = "<textarea type='text' value='' id='title' class='text lineItemTitle' name='title[]'></textarea>";
            $quantity    = "<input type='text' value='' id='quantity' class='text lineItemQuantity' name='quantity[]'>";
            $unit        = "<input type='text' value='' id='unit' class='text lineItemUnit' name='unit[]'>";
            $amount      = "<input type='text' value='' id='unit_price' class='text lineItemUnitPrice' name='unit_price[]'>";
            $total_cost  = "<td><input type='text' value='' id='amount' class='text lineItemAmount' name='amount[]'></td>";
            $remarks     = "<textarea type='text' value='' id='remarks' class='text lineItemRemarks' name='remarks[]'></textarea>";
            $clear       = "<td class='text'><a  class='clearLineItem'><u>Clear</u></a></td>";
            
            $nationality = "
            <select name='nationality[]'>
                <option value=''>Please Select</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlNationality)}
            </select>
            ";

            $category = "
            <select name='title[]'>
                <option value=''>Please Select</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $sqlCategory)}
            </select>
            ";

            $ot_rate       = "<input type='text' value='' id='ot_rate' class='text lineItemTitle' name='ot_rate[]'>";
            $ph_rate       = "<input type='text' value='' id='ph_rate' class='text lineItemTitle' name='ph_rate[]'>";
            $scaffold_code = "<input type='text' value='' id='scaffold_code' class='text lineItemScaffoldCode' name='scaffold_code[]'>";
            $erection      = "<input type='text' value='' id='erection' class='text lineItemErection' name='erection[]'>";
            $dismantle     = "<input type='text' value='' id='dismantle' class='text lineItemDismantle' name='dismantle[]'>";

            $rows = "
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                <!--<td>{$remarks}</td>-->
                {$clear}
            </tr>
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                <!--<td>{$remarks}</td>-->
                {$clear}
            </tr>
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                <!--<td>{$remarks}</td>-->
                {$clear}
            </tr>
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                <!--<td>{$remarks}</td>-->
                {$clear}
            </tr>
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td align='center'>{$unit}</td>
                <td>{$quantity}</td>
                <td align='center'>{$amount}</td>
                {$total_cost}
                <!--<td>{$remarks}</td>-->
                {$clear}
            </tr>
            ";  

            $newRow = "
            <a  class='addRow btn btn-primary mb10' renewal_id='{$renewal_id}'>Add Line Item</a>
            ";

            $header ="
            <tr>
              {$newRow}
              <label class='ml10 mr5'><b>Discount : </b></label>
              <input type='text' value='{$rowQuote['discount']}' id='discount' class='text overallDiscount' name='overallDiscount'>
              <div class='quoteLineItemsOverallTotal'>
                Total Amount <span class='quoteLineItemsOverallTotalAmount'>0.00</span>
              </div>
            </tr>
            <tr style='background-color:#EAEAE8;'>
                <th width='50%'>Title</th>
                <th width='60%'>Description</th>
                <th width='10%' class='txtCenter'>UoM</th>
                <th class='txtCenter'>Qty</th>
                <th width='13%' class='txtCenter'>Unit Price</th>
                <th width='15%' class='txtCenter'>Total Price</th>
                <!--<th>Remarks</th>-->
                <th width='2%' ></th>
            </tr>
            ";
        }

        $formAction = "index.php?widget=enggCrm_projectQuoteRenewal&_spAction=addMultipleLineItemSubmit&showHTML=0";
        $expNoEdit  = array('isEditable' => 0);
        
        $text = "
        <form id='addMultipleLineItemForm' class='addMultipleLineItemForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='renewal_id' value='{$renewal_id}' />
            <input type='hidden' name='quote_id' value='{$quote_id}' />
            <input type='hidden' name='drawing_nos' value='{$rowQuote['drawing_nos']}' />
        </form>
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

    function getEditForQuote() {
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil  = Zend_Registry::get('cpUtil');

        $quote_id       = $fn->getReqParam('quote_id');
        $renewal_id     = $fn->getReqParam('renewal_id');
        $quote_status   = $fn->getReqParam('quote_status');
        $opportunity_id = $fn->getReqParam('opportunity_id');

        $rowQuote      = $fn->getRecordRowByID('quote', 'quote_id', $quote_id);
        $quoteItemsRec = $fn->getRecordRowByID('quote_items', 'quote_id', $rowQuote['quote_id']);
        $rowProject    = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);

        $formActionEditForQuote = "index.php?widget=enggCrm_projectQuoteRenewal&_spAction=editForQuoteSubmit&lnkRoom={$tv['lnkRoom']}&quote_id={$rowQuote['quote_id']}&renewal_id={$renewal_id}&showHTML=0";

        $sqlTimesheetType = $fn->getValueListSQL('timesheetType');

        $expVl           = array('sqlType' => 'OneField');
        $expNoEdit       = array('isEditable' => 0);
        $expHideFirstOpt = array('hideFirstOption' => true);
        $expQuoteDate    = array('maxDate' => date('Y-m-d'), 'yearEnd' => date('Y'));

        $status = "";
        $spArrayQuoteStatus = array('New', 'Quoted', 'Awarded', 'Not Awarded', 'Cancelled');

        if ($rowQuote['quote_status'] == 'Awarded') {
            $status .= "<input type='hidden' name='quote_status' value='{$rowQuote['quote_status']}' />";
            $status .= "{$formObj->getDDRowByArr('Quote Status', 'quote_status', $spArrayQuoteStatus, $rowQuote['quote_status'], $expNoEdit)}";
        } else if ($rowQuote['opportunity_id'] == ''){
            $status = "{$formObj->getDDRowByArr('Quote Status', 'quote_status', $spArrayQuoteStatus, $rowQuote['quote_status'], $expHideFirstOpt)}";
        } else {
            $status = "{$formObj->getDDRowByArr('Quote Status', 'quote_status', $spArrayQuoteStatus, $rowQuote['quote_status'], $expHideFirstOpt)}";
        }

        $introText = '';
        $invoices_payment_terms = '';
        $responsibility = '';
    

        /*$provision_by_client = '';
        $provision_by_krs = '';
        if ($rowProject['category'] == 'Scaffolding'){
            $provision_by_client = "{$formObj->getTextAreaRow('Provision by Client', 'provision_by_client',$rowQuote['provision_by_client'])}";
            $provision_by_krs = "{$formObj->getTextAreaRow('Provision by KRS', 'provision_by_krs',$rowQuote['provision_by_krs'])}";
        }*/

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

        $text = "
        <form id='editForRenewal' class='yform columnar editQuote' method='post' action='{$formActionEditForQuote}'>
            <fieldset>
                <table width='100%'>
                    <tr>
                        <td>{$formObj->getDateRow('Quote Date', 'quote_date',$rowQuote['quote_date'], $expQuoteDate)}</td>
                        <td>{$status}</td>
                        <td>{$formObj->getTBRow('Quote No', 'quote_code', $rowQuote['quote_code'])}</td>
                    </tr>
                    <tr class='defaultQuoteFields {$hideDefaultQuote}'>
                        <td>{$formObj->getTBRow('Location', 'project_location', $rowQuote['project_location'])}</td>
                        <td colspan='2'>{$formObj->getTBRow('Project', 'project_reference', $rowQuote['project_reference'])}</td>
                        <td>{$formObj->getTBRow('Amount', 'total_amount', $rowQuote['total_amount'])}</td>
                    </tr>
                
                     <tr>
                        <td colspan='4'>                          
                        <label>Description</label>
                        {$formObj->getHTMLEditor('Terms & Condition', 'note', $rowQuote['note'])}</td>
                    </tr>
                  
                </table>
                <input type='text' name='renewal_id' value='{$renewal_id}' />
                <input type='text' name='quote_id' value='{$quote_id}' />
            </fieldset>
        </form>
        ";

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
        $renewal_id      = $fn->getReqParam('renewal_id');

        $rowProject     = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);
        $rowQuoteItem   = $fn->getRecordRowByID('quote_items', 'quote_items_id', $quote_items_id);
        $rowQuote       = $fn->getRecordRowByID('quote', 'quote_id', $rowQuoteItem['quote_id']);
        $sqlNationality = $fn->getValueListSQL('nationality');
        $sqlCategory    = $fn->getValueListSQL('employeeCategory');

        $exp   = array('sqlType' => 'OneField');
        $expVL = array('sqlType' => 'OneField');

        $formActionEditLineItem = "index.php?widget=enggCrm_projectQuoteRenewal&_spAction=editLineItemSubmit&lnkRoom={$tv['lnkRoom']}&quote_items_id={$quote_items_id}&opportunity_id={$opportunity_id}&showHTML=0";

        if($rowQuote['drawing_nos'] == 1) {
          $text = "
          <form id='editForLineItem' class='yform columnar' method='post' action='{$formActionEditLineItem}'>
              <fieldset>
                  {$formObj->getTARow('Drawing Number', 'drawing_number', $rowQuoteItem['drawing_number'])}
                  {$formObj->getTARow('Drawing Title', 'drawing_title', $rowQuoteItem['drawing_title'])}
                  {$formObj->getTBRow('Revision', 'drawing_revision', $rowQuoteItem['drawing_revision'])}
                  <input type='hidden' name='opportunity_id' value='{$opportunity_id}' />
                  <input type='hidden' name='quote_items_id' value='{$quote_items_id}' />
                  <input type='hidden' name='renewal_id' value='{$renewal_id}' />
                  <input type='hidden' name='drawing_nos' value='{$rowQuote['drawing_nos']}' />
                  <input type='hidden' name='project_category' value='{$rowProject['category']}' />
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
                  <input type='hidden' name='renewal_id' value='{$renewal_id}' />
                  <input type='hidden' name='drawing_nos' value='{$rowQuote['drawing_nos']}' />
                  <input type='hidden' name='project_category' value='{$rowProject['category']}' />
              </fieldset>
          </form>
          ";
        }

        return $text;
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
              ,p.renewal_id
              ,p.project_code
              ,p.company_id
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,c.address_po_code AS billing_address_po_code
              ,c.company_id
              ,co.email
              ,c.phone
              ,co.mobile
              ,c.fax
              ,co.salutation
              ,co.first_name
              ,s.email AS employee_email
              ,e.mobile AS employee_mobile
        FROM quote q
        LEFT JOIN (quote_items qi) ON (qi.quote_id = q.quote_id)
        LEFT JOIN (renewal p) ON (p.quote_id = q.quote_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = p.contact_id)
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

        $tbl2 ='<table border="0" width="100%" cellpadding="2" cellspacing="0">
                    <tr>
                        <td width="38%" style="font-size:13px; font-weight:bold;  line-height:16px;">To : </td>
                       
                    </tr>
                    <tr><td width="38%" ><table border="0" cellpadding="0">
                                
                                <tr>
                                    <td width="75%" style="font-size:12px;font-weight:bold;">'.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="75%" style="font-size:12px;font-weight:bold;">'.$company['billing_address_flat'].',<br/> '.$company['billing_address_street'].', <br/>'.$company['billing_address_country'].' - '.$company['billing_address_po_code'].'</td>
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
                                    <td width="75%" style="font-size:12px;font-weight:bold;">: '.$company['quote_code'].'</td>
                                </tr>
                               
                                   
                            </table>
                        </td>
                    </tr>
                </table>
                ';

        if ($_SESSION['userGroupName'] == 'Projects') {
          $tbl3 ='<table border="0"  cellpadding="4"  width="100%">
                      <thead>
                          <tr bgcolor="#ededf0">
                              <th width="6%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                              <th width="55%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DESCRIPTION</th>
                              <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                              <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT</th>
                              <th width="12%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;"></th>
                              <th width="13%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;"></th>
                          </tr>
                      </thead>
                      <tbody style="display: table; table-layout: fixed; height: 600px;">';
        } else {
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
                      </thead>
                      <tbody style="display: table; table-layout: fixed; height: 600px;">';
        }
        
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

            if($subtotal_amount_formatted == "0.00") {
                $subtotal_amount_formatted = "";
            }

            if ($_SESSION['userGroupName'] == 'Projects') {
              $tbl3 = $tbl3.'<tr>
                                  <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                  <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit'].'</td>
                                  <td width="12%" align="right" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;"></td>
                                  <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;"></td>
                              </tr>
                      ';
            } else {
              $tbl3 = $tbl3.'<tr>
                                  <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                  <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit'].'</td>
                                  <td width="12%" align="right" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit_price'].'</td>
                                  <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;">'.$subtotal_amount_formatted.'</td>
                              </tr>
                      ';              
            }

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

            $countCheck++;
            $count++;
        }

        if($company['discount'] && $_SESSION['userGroupName'] != 'Projects') {
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

        if($_SESSION['userGroupName'] != 'Projects') {
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
        } else {
            $tbl3 = $tbl3.'<tr>
                              <td colspan="6" style="border-top:1px solid #000;"></td>
                           </tr>
                          </tbody>
                        </table>';          
        }

        $tbl4 = '
        <table border="0" width="100%" cellpadding="2">
          
            <tr>
                <td align="left" style="line-height:16px;font-size:10px;">'.nl2br($company['condition']).'</td>
            </tr>
        </table>
        <br/><br/>
                <table border="0">
                    <tr>
                        <td width="13%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Amount</td>
                        <td width="2%"  align="left" style="font-weight:bold; font-size:10px; line-height:20px;">:</td>
                        <td width="85%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">KWD '.number_format($totalvalue, 2).'</td>
                    </tr>
                    <tr>
                        <td width="13%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">Amount In Words</td>
                        <td width="2%"  align="left" style="font-weight:bold; font-size:10px; line-height:20px;">:</td>
                        <td width="85%" align="left" style="font-weight:bold; font-size:10px; line-height:20px;">'.$amount_in_words.'</td>
                    </tr>
                </table>
              <br/><br/><br/></br><br/><br/><br/><br/><br/><br/><br/>
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
                <td ><img src="'.$imageAttached.'"></td>
            </tr>
            <tr>
                <td style="font-size:10px;font-weight:bold;">Mohamed Ibrahim<br/>
                   A Team International<br/>
                      Mob: 66144322 / 60063220</td>  
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-5);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(14);
        //$pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintLinkForPdfold() {
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
              ,p.renewal_id
              ,p.project_code
              ,p.company_id
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,c.address_po_code AS billing_address_po_code
              ,c.company_id
              ,co.email
              ,c.phone
              ,co.mobile
              ,c.fax
              ,co.salutation
              ,co.first_name
              ,s.email AS employee_email
              ,e.mobile AS employee_mobile
        FROM quote q
        LEFT JOIN (quote_items qi) ON (qi.quote_id = q.quote_id)
        LEFT JOIN (renewal p) ON (p.quote_id = q.quote_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = p.contact_id)
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

        $tbl1 = '
        <table border="0" width="100%" style="border-top: 1px solid #0e502a;" cellpadding="4">
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

        $tbl2 ='<table border="0" width="100%" cellpadding="2" cellspacing="0">
                    <tr>
                        <td width="38%" style="font-size:10px; font-weight:bold; background-color:#ededf0; border:1px solid #000; line-height:16px;"> Quotation To : </td>
                        <td width="24%"></td>
                        <td width="38%" style="font-size:10px; font-weight:bold; background-color:#ededf0; border:1px solid #000; line-height:16px;"> Quotation From :</td>
                    </tr>
                    <tr><td width="38%" style="border:1px solid #000;"><table border="0" cellpadding="0">
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Name</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['salutation'].'. '.$company['first_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> CO. Name</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Address</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['billing_address_flat'].',<br/>: '.$company['billing_address_street'].', <br/>: '.$company['billing_address_country'].' - '.$company['billing_address_po_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Email</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['email'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> HP No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['mobile'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Tel</td>
                                    <td width="75%" style="font-size:10px;">:6666 7777</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Fax</td>
                                    <td width="75%" style="font-size:10px;">: 6666 7777</td>
                                </tr>
                            </table>
                        </td>
                        <td width="24%"></td>
                        <td width="38%" style="border:1px solid #000;"><table border="0">
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Ref. No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['quote_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Date</td>
                                    <td width="75%" style="font-size:10px;">: '.$quote_date.'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Payment</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['payment_method'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Email</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['employee_email'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> HP No</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['employee_mobile'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Tel</td>
                                    <td width="75%" style="font-size:10px;">: 6666 7777</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Fax</td>
                                    <td width="75%" style="font-size:10px;">: 6666 7777</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;"> Created by</td>
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

        if ($_SESSION['userGroupName'] == 'Projects') {
          $tbl3 ='<table border="0"  cellpadding="4"  width="100%">
                      <thead>
                          <tr bgcolor="#ededf0">
                              <th width="6%"  align="center" style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                              <th width="55%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DESCRIPTION</th>
                              <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QTY</th>
                              <th width="7%"  align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT</th>
                              <th width="12%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;"></th>
                              <th width="13%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;"></th>
                          </tr>
                      </thead>
                      <tbody style="display: table; table-layout: fixed; height: 600px;">';
        } else {
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
                      </thead>
                      <tbody style="display: table; table-layout: fixed; height: 600px;">';
        }
        
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

            if($subtotal_amount_formatted == "0.00") {
                $subtotal_amount_formatted = "";
            }

            if ($_SESSION['userGroupName'] == 'Projects') {
              $tbl3 = $tbl3.'<tr>
                                  <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                  <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit'].'</td>
                                  <td width="12%" align="right" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;"></td>
                                  <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;"></td>
                              </tr>
                      ';
            } else {
              $tbl3 = $tbl3.'<tr>
                                  <td width="6%"  style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;" align="center">'.$count.'</td>
                                  <td width="55%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                  <td width="7%"  align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit'].'</td>
                                  <td width="12%" align="right" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit_price'].'</td>
                                  <td width="13%" align="right" style="font-size:10px;border-right:1px solid #000;">'.$subtotal_amount_formatted.'</td>
                              </tr>
                      ';              
            }

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

            $countCheck++;
            $count++;
        }

        if($company['discount'] && $_SESSION['userGroupName'] != 'Projects') {
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

        if($_SESSION['userGroupName'] != 'Projects') {
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
        } else {
            $tbl3 = $tbl3.'<tr>
                              <td colspan="6" style="border-top:1px solid #000;"></td>
                           </tr>
                          </tbody>
                        </table>';          
        }

        $tbl4 = '
        <table border="1" width="100%" cellpadding="2">
            <tr>
                <td align="left" width="100%" style="font-size:10px; font-weight:bold; background-color:#ededf0;">Other Comments or Special Instructions :</td>
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

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Quote.pdf';
        $pdf->Output($download_title, 'I');
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

        $quote_id = $fn->getReqParam('quote_id');

        $SQL = "
        SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.drawing_number
              ,qi.drawing_title
              ,qi.drawing_revision
              ,p.renewal_id
              ,p.project_code
              ,p.company_id
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
        LEFT JOIN (renewal p) ON (p.quote_id = q.quote_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = p.contact_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        WHERE q.quote_id = {$quote_id}
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

        $tbl4 ='<table border="1"  cellpadding="4"  width="100%">
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
        $pdf->ln(-7);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->ln(-5);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getViewQuoteLog1() {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $renewal_id = $fn->getReqParam('renewal_id');
        $opportunity_id = $fn->getReqParam('opportunity_id');

        $projRec    = $fn->getRecordRowByID('renewal', 'renewal_id', $renewal_id);
        $companyRec = $fn->getRecordRowByID('company', 'company_id', $projRec['company_id']);

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
        LEFT JOIN (renewal p) ON (p.quote_id = q.quote_id)
        WHERE p.renewal_id = {$renewal_id}
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
              $urlPrintLinkPdf = "index.php?widget=enggCrm_projectQuoteRenewal&_spAction=printDrawingQuoteLogPdf&opportunity_id={$opportunity_id}&quote_log_id={$row['quote_log_id']}&showHTML=0";

              $add_image = $cpCfg['cp.localPath']."images/add.png";
              $print_image = $cpCfg['cp.localPath']."images/icon-print.png";
              $edit_image = $cpCfg['cp.localPath']."images/edit.png";
              if ($row['quote_status'] == 'Awarded' || $row['quote_status'] == 'Order Raised') {
                  $quoteActions ="
                  <div class='float_box clearfix'>
                      <div class='printLink'>
                          <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
                      </div>
                  </div>
                  ";
              } else {
                  $quoteActions ="
                  <div class='float_box clearfix'>
                      <div class='printLink float_left'>
                          <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
                      </div>
                  </div>
                  ";
              }

              $updation_details = '';
              if ($row['modified_by']) {
                  $updation_details = $row['modified_by'] . ' - <br/>' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
              } else {
                  $updation_details = $row['created_by'] . ' - <br/> ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
              }

              $confirmedQuoteStatus = '';
              if($row['quote_status'] == 'Awarded') {
                  $confirmedQuoteStatus = 'confirmedQuote';
              }

              $cancelledQuoteStatus = '';
              if($row['quote_status'] == 'Cancelled') {
                  $cancelledQuoteStatus = 'cancelledQuote';
              }

              $quote_amount = number_format($row['total_amount'] - $row['discount'], 2);
              $discount     = number_format($row['discount'], 2);

              $rows .= "
              <tbody class='quoteDetailRow'>
                  <tr class='addQuoteRow  {$cancelledQuoteStatus}'>
                      <td>{$row['revision']}</td>
                      <td>
                          <a class='creationModificationQuote3' quote_log_id='{$row['quote_log_id']}'>
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
                  {$this->getAddLineItemForQuoteLogListView($opportunity_id,$renewal_id,$row['quote_log_id'])}
              </tbody>
              ";
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

              $urlPrintLinkPdf  = "index.php?widget=enggCrm_projectQuoteRenewal&_spAction=printLinkForLogPdf&opportunity_id={$opportunity_id}&quote_log_id={$row['quote_log_id']}&showHTML=0";

              $add_image = $cpCfg['cp.localPath']."images/add.png";
              $print_image = $cpCfg['cp.localPath']."images/icon-print.png";
              $edit_image = $cpCfg['cp.localPath']."images/edit.png";
              if ($row['quote_status'] == 'Awarded' || $row['quote_status'] == 'Order Raised') {
                  $quoteActions ="
                  <div class='float_box clearfix'>
                      <div class='printLink'>
                          <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
                      </div>
                  </div>
                  ";
              } else {
                  $quoteActions ="
                  <div class='float_box clearfix'>
                      <div class='printLink float_left'>
                          <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Quote'><img src='{$print_image}' class='icon'></a>
                      </div>
                  </div>
                  ";
              }

              $updation_details = '';
              if ($row['modified_by']) {
                  $updation_details = $row['modified_by'] . ' - <br/>' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
              } else {
                  $updation_details = $row['created_by'] . ' - <br/> ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
              }

              $confirmedQuoteStatus = '';
              if($row['quote_status'] == 'Awarded') {
                  $confirmedQuoteStatus = 'confirmedQuote';
              }

              $cancelledQuoteStatus = '';
              if($row['quote_status'] == 'Cancelled') {
                  $cancelledQuoteStatus = 'cancelledQuote';
              }

              $quote_amount = number_format($totalvalue - $row['discount'], 2);

              $discount = number_format($row['discount'], 2);

              $revision = "";
              if($row['revision'] != "") {
                  $revision = $row['revision']. ' ';
              }

              $rows .= "
              <tbody class='quoteDetailRow'>
                  <tr class='addQuoteRow  {$cancelledQuoteStatus}'>
                      <td>{$row['revision']}</td>
                      <td>
                          <a class='creationModificationQuote3' quote_log_id='{$row['quote_log_id']}'>
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
                  {$this->getAddLineItemForQuoteLogListView($opportunity_id,$renewal_id,$row['quote_log_id'])}
              </tbody>
              ";

          }

        }

          $text = '';

          if($numRows > 0)  {
            $ChangeHead = "<th class='txtRight' colspan='2'>Amount</th>";
            
            $text .= "
            <div id='quotesPortal' class='linkPortalWrapper'>
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
    function getAddLineItemForQuoteLogListView($opportunity_id, $renewal_id, $quote_log_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $SQL = "
        SELECT qt.*
              ,q.drawing_nos
        FROM `quote_items_log` qt
        LEFT JOIN quote_log q ON (qt.quote_log_id = q.quote_log_id)
        WHERE q.renewal_id = {$renewal_id}
        AND qt.quote_log_id = {$quote_log_id}
        ORDER BY qt.quote_items_log_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows        = '';
        $drawing_nos = '';
        while ($row = $db->sql_fetchrow($result)) {
            $editForLineItem = '';
            $deleteLineItem  = '';
            $edit_image      = $cpCfg['cp.localPath']."images/edit.png";
            $delete_image    = $cpCfg['cp.localPath']."images/delete.png";

            $addclass = '';
            if ($row['renewal_id'] != '') {
                $addclass = 'quoteFromProj';
            }

            if($row['drawing_nos'] == "" || $row['drawing_nos'] == 0) {
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
              $updation_details = '';
              if ($row['modified_by']) {
                  $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
              } else {
                  $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
              }

              $rows .= "
              <tr class = 'quoteLayoutHide showAddLineRow {$addclass}'>
                  <td class='emptyTd'></td>
                  <td colspan='2' class='descriptionWrap'>{$row['drawing_number']}</td>
                  <td colspan='4' class='descriptionWrap'>{$row['drawing_title']}</td>
                  <td align='center'>{$row['drawing_revision']}</td>
                  <td>{$updation_details}</td>
              </tr>";
            }

            $drawing_nos = $row['drawing_nos'];
        }

        $text = '';

        if ($numRows > 0)  {
            if($drawing_nos == "" || $drawing_nos == 0) {
                $text = "
                <tr class = 'quoteLayoutHide showAddLineRow'>
                    <!--<th class='quoteRowBorder'></th>
                    <th class='quoteRowBordersecond'></th>-->
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
              ,p.renewal_id
              ,p.project_code
              ,p.company_id
              ,c.company_name
              ,c.address_flat AS billing_address_flat
              ,c.address_street AS billing_address_street
              ,gc.name AS billing_address_country
              ,c.address_po_code AS billing_address_po_code
              ,c.company_id
              ,co.email
              ,c.phone
              ,co.mobile
              ,c.fax
              ,co.salutation
              ,co.first_name
              ,s.email AS employee_email
              ,e.mobile AS employee_mobile
        FROM quote_log q
        LEFT JOIN (quote_items_log qi) ON (qi.quote_log_id = q.quote_log_id)
        LEFT JOIN (renewal p) ON (p.quote_id = q.quote_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = p.contact_id)
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

        $tbl1 = '
        <table border="0" width="100%" style="border-top: 1px solid #0e502a;" cellpadding="4">
            <tr>
                <td align="center" style="font-size:16px; font-weight:bold; color:#078205; text-decoration:underline; line-height:26px;">QUOTATION</td>
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
                                    <td width="75%" style="font-size:10px;">: '.$company['billing_address_flat'].',<br/>: '.$company['billing_address_street'].', <br/>: '.$company['billing_address_country'].' - '.$company['billing_address_po_code'].'</td>
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

            if($subtotal_amount_formatted == "0.00") {
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

            $countCheck++;
            $count++;
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

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Quote.pdf';
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

        $SQL = "
        SELECT q.*
              ,qi.title AS quote_item_title
              ,qi.drawing_number
              ,qi.drawing_title
              ,qi.drawing_revision
              ,p.renewal_id
              ,p.project_code
              ,p.company_id
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
        LEFT JOIN (renewal p) ON (p.quote_id = q.quote_id)
        LEFT JOIN (company c) ON (c.company_id = p.company_id)
        LEFT JOIN (contact co) ON (co.contact_id = p.contact_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = c.address_country)
        WHERE q.quote_log_id = {$quote_log_id}
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

        $tbl4 ='<table border="1"  cellpadding="4"  width="100%">
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
        $pdf->ln(-7);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->ln(-5);
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Quote.pdf';
        $pdf->Output($download_title, 'I');
    }
}