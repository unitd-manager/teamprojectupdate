<?
class CPL_Admin_Widgets_EnggCrm_ProjectWorkOrder_View extends CP_Common_Lib_WidgetViewAbstract
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
    function getWorkOrderListView($project_id = '') {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }
        $addWOBtn = "
        <div class='mb10'>
            <a  id='addWOProject' class='btn btn-primary' project_id='{$project_id}'>Add Work Order</a>
        </div>
        ";

        $text = "
        <div class='float_box mt10'>
            {$addWOBtn}
        </div>
        <div class='floatbox'>
            {$this->getSubconWorkOrderPortal($project_id)}
            {$this->getSubconWorkOrderPaymentPortal($project_id)}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getSubconWorkOrderPortal($project_id = '') {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        $projRec    = $fn->getRecordRowByID('project', 'project_id', $project_id);
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
              ,s.company_name
        FROM `sub_con_work_order` q
        LEFT JOIN (project p) ON (p.project_id = q.project_id)
        LEFT JOIN (sub_con s) ON (s.sub_con_id = q.sub_con_id)
        WHERE p.project_id = {$project_id}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalQuoteAmount = '';
        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
                $wo_date   = $fn->getCPDate($row['work_order_date'], 'd-m-Y');
                $due_date   = $fn->getCPDate($row['work_order_due_date'], 'd-m-Y');
                $completed_date   = $fn->getCPDate($row['completed_date'], 'd-m-Y');

                $sqlQuoteItems ="
                SELECT SUM(amount)As quote_amount
                      ,SUM(quantity) AS quote_qty
                       ,work_order_line_items_id
                FROM work_order_line_items qi
                WHERE qi.sub_con_work_order_id = {$row['sub_con_work_order_id']}
                ";

                $resultForQuoteItems  = $db->sql_query($sqlQuoteItems);
                $rowQuoteItems        = $db->sql_fetchrow($resultForQuoteItems);

                $addLineItemView = '';
                if($rowQuoteItems['quote_amount'] > 0 || $rowQuoteItems['quote_qty']) {
                    $addLineItemView ="
                    <div class='float_right'>
                        <a href='javascript:void(0);' class='quoteLayoutShow'>View Line Items</a>
                    </div>
                    ";
                }

                $quoteActions = '';
                $editForQuote = "index.php?widget=enggCrm_projectWorkOrder&_spAction=editForWorkOrder&project_id={$project_id}&sub_con_work_order_id={$row['sub_con_work_order_id']}&showHTML=0";

                $urlPrintLinkPdf  = "index.php?widget=enggCrm_projectWorkOrder&_spAction=printLinkWOForPdf&sub_con_work_order_id={$row['sub_con_work_order_id']}&showHTML=0";

                $add_image = $cpCfg['cp.localPath']."images/add.png";
                $print_image = $cpCfg['cp.localPath']."images/icon-print.png";
                $edit_image = $cpCfg['cp.localPath']."images/edit.png";
                if ($row['status'] == 'Confirmed' || $row['status'] == 'Order Raised') {
                    $quoteActions ="
                    <div class='float_box clearfix'>
                        <div class='float_left'>
                            <a class='editForWorkOrder' href='{$editForQuote}' title='Edit Work Order'><img src='{$edit_image}' class='icon'></a>
                        </div>
                        <div class='printLink'>
                            <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Work Order'><img src='{$print_image}' class='icon'></a>
                        </div>
                    </div>
                    ";
                } else {
                    $quoteActions ="
                    <div class='float_box clearfix'>
                        <div class='float_left'>
                            <a class='editForWorkOrder'  href='{$editForQuote}' title='Edit Work Order'><img src='{$edit_image}' class='icon'></a>
                        </div>
                        <div class='printLink float_left'>
                            <a href='{$urlPrintLinkPdf}' target='_blank' title='Print Work Order'><img src='{$print_image}' class='icon'></a>
                        </div>
                        <div class='float_left'>
                            <a project_id={$row['project_id']} sub_con_work_order_id = {$row['sub_con_work_order_id']} class='addMultipleWOItem' title='Add Line Item'><img src='{$add_image}' class='icon'></a>
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
                if($row['status'] == 'Confirmed') {
                    $confirmedQuoteStatus = 'confirmedQuote';
                }

                $quote_amount = number_format($rowQuoteItems['quote_amount'], 2);

                $rows .= "
                <tbody class='quoteDetailRow'>
                    <tr class='addQuoteRow {$confirmedQuoteStatus}'>
                        <td>
                            <a class='creationModificationDetails' record_id='{$row['sub_con_work_order_id']}' field_name='sub_con_work_order_id' table_name='sub_con_work_order'>
                                <u>{$row['sub_con_worker_code']}</u>
                            </a>
                        </td>
                        <td>{$wo_date}</td>
                        <td>{$row['company_name']}</td>
                        <td>{$row['status']}</td>
                        <td>{$due_date}</td>
                        <td>{$completed_date}</td>
                        <td class='txtRight'>{$quote_amount}</td>
                        <td class=''>{$addLineItemView}</td>
                        <td>{$quoteActions}</td>
                    </tr>
                    {$this->getAddLineItemForWorkOrder($project_id,$row['sub_con_work_order_id'])}
                </tbody>
                ";

            }

            $text = '';

            if($numRows > 0)  {
              $ChangeHead = "<th class='txtRight'>Amount</th>";
              
              $text = "
              <div id='quotesPortal' class='linkPortalWrapper'>
                  <table class ='list'>
                      <thead>
                          <tr>
                              <th colspan='9' align='left' class='rightPanelHeading'>
                                Work Orders
                              </th>
                          </tr>
                          <tr>
                              <th>WO Code</th>
                              <th>Date</th>
                              <th>Sub Con</th>
                              <th>Status</th>
                              <th>Due Date</th>
                              <th>Completed Date</th>
                              {$ChangeHead}
                              <th></th>
                              <th>Action</th>
                          </tr>
                      </thead>
                          {$rows}
                  </table>
              </div>
              ";

              return $text;
          }
    }

    /**
     *
     */
    function getSubconWorkOrderPaymentPortal($project_id) {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = '';
        $errorText = '';

        $sqlClient = "
        SELECT sr.amount
              ,sr.creation_date AS date
              ,sr.mode_of_payment
              ,sr.status
              ,sr.sub_con_payments_id
              ,sr.sub_con_id
              ,srh.sub_con_work_order_id
              ,sc.company_name
        FROM sub_con_payments_history srh
        LEFT JOIN (sub_con_payments sr) ON (sr.sub_con_payments_id = srh.sub_con_payments_id)
        LEFT JOIN (sub_con sc) ON (sc.sub_con_id = sr.sub_con_id)
        WHERE sr.project_id = {$project_id}
          AND sr.status != 'Cancelled'
        ORDER BY srh.sub_con_payments_history_id
        ";

        $result     = $db->sql_query($sqlClient);
        $numRows    = $db->sql_numrows($result);

        while ($row = $db->sql_fetchrow($result)) {
            $date = $fn->getCPDate($row['date'], 'd-m-Y');

            $rows .= "
            <tbody class='quoteDetailRow'>
                <tr>
                    <td>{$date}</td>
                    <td>{$row['company_name']}</td>
                    <td>{$row['amount']}</td>
                    <td>{$row['mode_of_payment']}</td>
                </tr>
            </tbody>    
            ";
        }

        $clientRows = "
        <div id='quotesPortal' class='linkPortalWrapper'>
            <table class ='list'>
                <thead>
                    <tr>
                        <th colspan='4' align='left' class='rightPanelHeading'>
                          Payment History
                        </th>
                    </tr>
                    <tr>
                        <th>Date</th>
                        <th>SubCon Name</th>
                        <th>Amount</th>
                        <th>Mode of Payment</th>
                    </tr>
                </thead>
                    {$rows}
            </table>
        </div>
        ";

        $text ="
        {$clientRows}
        ";

        return $text;
    }

    /**
     *
     */
    function getAddLineItemForWorkOrder($project_id, $sub_con_work_order_id) {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $SQL = "
        SELECT qt.*
        FROM `work_order_line_items` qt
        LEFT JOIN sub_con_work_order q ON (qt.sub_con_work_order_id = q.sub_con_work_order_id)
        WHERE q.project_id = {$project_id}
        AND qt.sub_con_work_order_id = {$sub_con_work_order_id}
        ORDER BY qt.work_order_line_items_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $editForLineItem = '';
            $deleteLineItem  = '';
            $edit_image = $cpCfg['cp.localPath']."images/edit.png";
            $delete_image = $cpCfg['cp.localPath']."images/delete.png";

            $editForLineItem = "index.php?widget=enggCrm_projectWorkOrder&_spAction=editWOLineItem&sub_con_work_order_id={$row['sub_con_work_order_id']}&work_order_line_items_id={$row['work_order_line_items_id']}&project_id={$project_id}&showHTML=0";

            $editText = "
            <div class='float_left'>
                <a class='editForWOLineItem' href='{$editForLineItem}' title='Edit Line Item'><img src='{$edit_image}' class='icon'></a>
            </div>
            ";

            $deleteLineItem = "
            <div class='float_left'>
                <a  class='deleteWOLineItem' work_order_line_items_id='{$row['work_order_line_items_id']}' sub_con_work_order_id= '{$row['sub_con_work_order_id']}'  title='Delete Line Item'><img src='{$delete_image}' class='icon'></a></td>
            </div>
            ";

            $addclass = '';
            if ($row['project_id'] != '') {
                $addclass = 'quoteFromProj';
            }

            $total_amount = number_format($row['amount'], 2);

            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }

            $rows .= "
            <tr class = 'quoteLayoutHide showAddLineRow {$addclass}'>
                <td colspan='4' class='descriptionWrap'>{$row['description']}</td>
                <td>{$row['quantity']}</td>
                <td class='amountRow'>{$row['unit_rate']}</td>
                <td class='amountRow'>{$total_amount}</td>
                <td>{$updation_details}</td>
                <td colspan='2'>{$editText} {$deleteLineItem}</td>
            </tr>
            ";
        }

        $text = '';

        if ($numRows > 0)  {
            $text = "
                <tr class = 'quoteLayoutHide showAddLineRow'>
                    <th class='quoteRowBackground' colspan='4'>Description</th>
                    <th class='quoteRowBackground'>Quantity</th>
                    <th class='quoteRowBackground txtRight'>Unit Rate</th>
                    <th class='quoteRowBackground txtRight'>Amount</th>
                    <th class='quoteRowBackground'>Updated By</th>
                    <th colspan='2' class='quoteRowBackground'>Action</th>
                </tr>
                {$rows}
            ";

            return $text;
        }
    }

    /**
     *
     */
    function getEditForWorkOrder() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil = Zend_Registry::get('cpUtil');

        $sub_con_work_order_id = $fn->getReqParam('sub_con_work_order_id');
        $project_id       = $fn->getReqParam('project_id');

        $rowWO         = $fn->getRecordRowByID('sub_con_work_order', 'sub_con_work_order_id', $sub_con_work_order_id);
        $rowProject       = $fn->getRecordRowByID('project', 'project_id', $project_id);

        $formActionEditForQuote = "index.php?widget=enggCrm_projectWorkOrder&_spAction=editForWorkOrderSubmit&lnkRoom={$tv['lnkRoom']}&sub_con_work_order_id={$rowWO['sub_con_work_order_id']}&project_id={$project_id}&showHTML=0";

        $expVl           = array('sqlType' => 'OneField');
        $expNoEdit       = array('isEditable' => 0);
        $expHideFirstOpt = array('hideFirstOption' => true);
        $expQuoteDate    = array('maxDate' => date('Y-m-d'), 'yearEnd' => date('Y'));

        $status = "<input type='hidden' name='status' value='{$rowWO['status']}' />";
        $spArrayQuoteStatus = array('New' ,'Cancelled' ,'Confirmed','Hold');
        if ($rowWO['status'] == 'Order Raised') {
            $status = "{$formObj->getDDRowByArr('Status', 'status', $spArrayQuoteStatus, $rowWO['status'], $expNoEdit)}";
        } else {
            $status = "{$formObj->getDDRowByArr('Status', 'status', $spArrayQuoteStatus, $rowWO['status'], $expHideFirstOpt)}";
        }
        $introText = '';
        $invoices_payment_terms = '';
        $responsibility = '';
        $manPowerTermsInQuote = '';
        $provision_by_client = '';
        $provision_by_krs = '';
        $sqlSubcon = "SELECT sub_con_id, company_name FROM sub_con";

        $text = "
        <form id='editForQuote' class='yform columnar editQuote' method='post' action='{$formActionEditForQuote}'>
            <fieldset>
                <table width='100%'>
                    <tr>
                        <td width='50%'>{$formObj->getDDRowBySQL('Sub Con', 'sub_con_id', $sqlSubcon, $rowWO['sub_con_id'])}</td>
                        <td width='50%'>{$formObj->getDateRow('Date', 'work_order_date',$rowWO['work_order_date'])}</td>
                    </tr>
                    <tr>
                        <td width='50%'>{$formObj->getDateRow('Due Date', 'work_order_due_date',$rowWO['work_order_due_date'])}</td>
                        <td width='50%'>{$formObj->getDateRow('Completed Date', 'completed_date',$rowWO['completed_date'])}</td>
                    </tr>
                    <tr>
                        <td>{$status}</td>
                        <td width='50%'>{$formObj->getTBRow('Project Location', 'project_location',$rowWO['project_location'])}</td>
                    </tr>
                    <tr>
                        <td width='50%'>{$formObj->getTBRow('Project Reference', 'project_reference',$rowWO['project_reference'])}</td>
                        <td width='50%'>{$formObj->getDateRow('Quotation Reference', 'quote_date',$rowWO['quote_date'])}</td>
                    </tr>
                    <tr>
                        <td width='50%'>{$formObj->getTBRow('Quotation Reference', 'quote_reference',$rowWO['quote_reference'])}</td>
                    </tr>
                    <tr>
                        <td colspan='2'>{$formObj->getTextAreaRow('Terms & Condition', 'condition',$rowWO['condition'])}</td>
                    </tr>
                </table>
                <input type='hidden' name='project_id' value='{$project_id}' />
                <input type='hidden' name='sub_con_work_order_id' value='{$sub_con_work_order_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintLinkWOForPdf() {
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

        $sub_con_work_order_id = $fn->getReqParam('sub_con_work_order_id');

        $SQL = "
        SELECT wo.*
              ,woi.quantity
              ,woi.unit
              ,woi.description
              ,woi.unit_rate
              ,woi.amount
              ,woi.remarks
              ,p.project_id
              ,p.project_code
              ,p.company_id
              ,s.company_name
              ,s.phone
              ,s.email
              ,s.mobile
              ,s.address_flat
              ,s.address_street
              ,s.address_town
              ,s.address_state
              ,gc.name AS address_country
        FROM sub_con_work_order wo
        LEFT JOIN (work_order_line_items woi) ON (woi.sub_con_work_order_id = wo.sub_con_work_order_id)
        LEFT JOIN (project p) ON (p.project_id = wo.project_id)
        LEFT JOIN (sub_con s) ON (s.sub_con_id = wo.sub_con_id)
        LEFT JOIN (geo_country gc) ON (gc.country_code = s.address_country)
        WHERE wo.sub_con_work_order_id = {$sub_con_work_order_id}
        ORDER BY woi.work_order_line_items_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $work_order_date = $fn->getCPDate($company['work_order_date'], 'd M Y');
        $today           = date("d-m-Y");

        $tbl1 = '
        <table border="0" width="100%" style="" cellpadding="4">
            <tr>
                <td align="center" style="font-size:20px; font-weight:bold; color:#14213d;">WORK ORDER</td>
            </tr>
        </table>
        ';

        $subConAddress = "";
        if($company['address_flat']) {
          $subConAddress .= $company['address_flat'].', ';
        }

        if($company['address_street']) {
          $subConAddress .= $company['address_street'].', ';
        }

        if($company['address_town']) {
          $subConAddress .= $company['address_town'].', ';
        }

        if($company['address_country']) {
          $subConAddress .= $company['address_country'];
        }
        
        if($company['address_state']) {
          $subConAddress .= ' - '.$company['address_state'].'.';
        }

        $subConAddress = rtrim($subConAddress, ', ');

        $tbl2 ='<table border="0" width="100%" cellpadding="0">
                    <tr>
                        <td width="33%">
                            <table border="0" cellpadding="3" style="border:1px solid #000;">
                                <tr>
                                    <td width="100%" style="font-size:10px; font-weight:bold; background-color:#ededf0; border-bottom:1px solid #000;">SUBCONTRACTOR NAME</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">CO. Name</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['company_name'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Address</td>
                                    <td width="75%" style="font-size:10px;">: '.$subConAddress.'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Email</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['email'].'</td>
                                </tr>
                                <tr>
                                    <td width="25%" style="font-size:10px;">Tel</td>
                                    <td width="75%" style="font-size:10px;">: '.$company['phone'].'</td>
                                </tr>
                            </table>
                        </td>
                        <td width="34%">
                        </td>
                        <td width="33%">
                            <table border="0" cellpadding="3" style="border:1px solid #000;">
                                <tr>
                                    <td width="100%" style="font-size:10px; font-weight:bold; background-color:#ededf0; border-bottom:1px solid #000;">WORK ORDER REF</td>
                                </tr>
                                <tr>
                                    <td width="35%" style="font-size:10px;">Date</td>
                                    <td width="65%" style="font-size:10px;">: '.$work_order_date.'</td>
                                </tr>
                                <tr>
                                    <td width="35%" style="font-size:10px;">WO No</td>
                                    <td width="65%" style="font-size:10px;">: '.$company['sub_con_worker_code'].'</td>
                                </tr>
                                <tr>
                                    <td width="35%" style="font-size:10px;">Quotation Ref</td>
                                    <td width="65%" style="font-size:10px;">: '.$company['quote_reference'].'</td>
                                </tr>
                                <tr>
                                    <td width="35%" style="font-size:10px;">Quotation Date</td>
                                    <td width="65%" style="font-size:10px;">: '.$fn->getCPDate($company['quote_date'], 'd M Y').'</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td style="line-height:10px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="left" style="font-size:10px; font-weight:bold; line-height:20px;">Project location : '.$company['project_location'].'</td>
                    </tr>
                    <tr>
                        <td align="left" style="font-size:10px; font-weight:bold; line-height:20px;">Project Reference : '.$company['project_reference'].'</td>
                    </tr>
                </table>';


        $tbl3 ='<table border="0"  cellpadding="4"  width="100%" style="border:1px solid #000;">
                    <thead>
                        <tr bgcolor="#ededf0">
                            <th width="7%" align="center"  style="font-size:10px; font-weight:bold;border:1px solid #000;">S. NO.</th>
                            <th width="53%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">DESCRIPTION</th>
                            <th width="10%" align="center" style="border:1px solid #000;font-size:10px; font-weight:bold;">QUANTITY</th>
                            <th width="15%" align="right"  style="border:1px solid #000;font-size:10px; font-weight:bold;">UNIT RATE($)</th>
                            <th width="15%" align="right"  style="border:1px solid #000;font-size:10px; font-weight:bold;">AMOUNT($)</th>
                        </tr>
                    </thead>';
        $subtotalValue = 0;
        $count = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $subtotal_amount = round($row['amount'], 2);
            $subtotal_amount_formatted = number_format($subtotal_amount, 2);

            $tbl3 = $tbl3.'<tr>
                                <td width="7%"  align="center" style="font-size:10px;">'.$count.'</td>
                                <td width="53%" style="border-left:1px solid #000;border-right:1px solid #000;font-size:10px;">'.nl2br($row['description']).'</td>
                                <td width="10%" align="center" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['quantity'].'</td>
                                <td width="15%" align="right" style="font-size:10px;border-left:1px solid #000;border-right:1px solid #000;">'.$row['unit_rate'].'</td>
                                <td width="15%" align="right" style="font-size:10px;">'.$subtotal_amount_formatted.'</td>
                            </tr>
                    ';

            $subtotalValue += $subtotal_amount;
            $count++;
        }
        $emptyRow = '';
        if($count <= 8){
            $countCheck = 8 - $count;
        }
        else{
            $countCheck = 0;
        }
        for($ic = 1; $ic <= $countCheck; $ic++){
            $emptyRow .= '
            <tr>
                <td style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                <td style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                <td style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                <td style="border-left:1px solid #000;border-right:1px solid #000;"></td>
                <td style="border-left:1px solid #000;border-right:1px solid #000;"></td>
            </tr>
            ';
        }

        $tbl3 = $tbl3.''.$emptyRow.'
                      <tr>
                          <td align="right" colspan="4" style="font-size:10px; font-weight:bold; border-top:1px solid #000;border-right:1px solid #000;">SUB TOTAL</td>
                          <td align="right" style="font-size:10px; font-weight:bold; border-top:1px solid #000;">'.number_format($subtotalValue,2).'</td>
                      </tr>
                    </table>';

        $tbl4 = '
        <table border="0" width="100%" cellpadding="2">
            <tr>
                <td align="left" width="100%" style="font-size:10px; font-weight:bold; text-decoration:underline;">Terms And Conditions</td>
            </tr>
            <tr>
                <td align="left" style="font-size:10px;">'.nl2br($company['condition']).'</td>
            </tr>
        </table>';

        $tbl5 = '
        <table border="0" width="100%">
            <tr>
                <td border="0" align="left" style="border-top:1px solid #000;font-weight:bold; font-size:10px;" width="40%">for ' . $cpCfg['cp.companyName'] . '</td>
                <td width="25%" style="border-top:1px solid #000;"></td>
                <td border="0" align="left" style="border-top:1px solid #000;font-weight:bold; font-size:10px;" width="35%">I hereby confirm my acceptance of this Work Order and shall comply fully to the above terms and conditions.</td>
            </tr>
            <br/>
            <br/>
        </table>
        ';

        $tbl6 = '
        <table border="0" width="100%" cellpadding="3">
            <tr>
                <td width="40%" style="border-bottom:1px solid black"></td>
                <td width="25%"></td>
                <td width="35%" style="border-bottom:1px solid black"></td>
            </tr>
            <tr>
                <td style="font-size:10px;">(Name / Designation / Signature)</td>
                <td></td>
                <td style="font-size:10px;">(Name /Signature/ Company Stamp/ Date)</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl3, true, false, false, false, '');
        $pdf->writeHTML($tbl4, true, false, false, false, '');
        $pdf->writeHTML($tbl5, true, false, false, false, '');
        $pdf->writeHTML($tbl6, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['sub_con_worker_code'] . '-Workorder.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     * Add Line Item Edit
     */
    function getEditWOLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $tv      = Zend_Registry::get('tv');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');

        $work_order_line_items_id  = $fn->getReqParam('work_order_line_items_id');
        $project_id      = $fn->getReqParam('project_id');

        $rowProject     = $fn->getRecordRowByID('project', 'project_id', $project_id);
        $rowWOItem   = $fn->getRecordRowByID('work_order_line_items', 'work_order_line_items_id', $work_order_line_items_id);

        $exp   = array('sqlType' => 'OneField');
        $expVL = array('sqlType' => 'OneField');

        $formActionEditLineItem = "index.php?widget=enggCrm_projectWorkOrder&_spAction=editWOLineItemSubmit&lnkRoom={$tv['lnkRoom']}&work_order_line_items_id={$work_order_line_items_id}&showHTML=0";

        $text = "
        <form id='editForWOLineItem' class='yform columnar' method='post' action='{$formActionEditLineItem}'>
            <fieldset>
                {$formObj->getTARow('Description', 'description',$rowWOItem['description'] )}
                {$formObj->getTBRow('Quantity', 'quantity',$rowWOItem['quantity'])}
                {$formObj->getTBRow('Unit Rate', 'unit_rate',$rowWOItem['unit_rate'])}
                {$formObj->getTBRow('Amount', 'amount',$rowWOItem['amount'])}
                <input type='hidden' name='work_order_line_items_id' value='{$work_order_line_items_id}' />
                <input type='hidden' name='project_id' value='{$project_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddWOLineItemRecord() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $project_id     = $fn->getReqParam('project_id');
        $rowProject     = $fn->getRecordRowByID('project', 'project_id', $project_id);

        $description = "<textarea value='' id='description' class='text lineItemDescription' name='description[]'></textarea>";
        $quantity    = "<input type='text' value='' id='quantity' class='text lineItemQuantity' name='quantity[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text lineItemUnitPrice' name='amount[]'>";
        $total_cost  = "<td class='txtRight text totalCost' name='totalCost[]'></td>";
        $clear       = "<td class='text'><a  class='clearLineItem'><u>Clear</u></a></td>";
        $unit_rate      = "<input type='text' value='' id='unit_rate' class='text lineItemAmount' name='unit_rate[]'>";

        $rows = "
        <tr>
            <td>{$description}</td>
            <td>{$quantity}</td>
            <td>{$unit_rate}</td>
            <td>{$amount}</td>
            {$clear}
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getAddMultipleWOItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        
        $project_id     = $fn->getReqParam('project_id');
        $sub_con_work_order_id = $fn->getReqParam('sub_con_work_order_id');
        
        $rowProject     = $fn->getRecordRowByID('project', 'project_id', $project_id);

        $description = "<textarea value='' id='description' class='text lineItemDescription' name='description[]'></textarea>";
        $quantity    = "<input type='text' value='' id='quantity' class='text lineItemQuantity' name='quantity[]'>";
        $unit_rate      = "<input type='text' value='' id='unit_rate' class='text lineItemUnitPrice' name='unit_rate[]'>";
        $amount      = "<input type='text' value='' id='amount' class='text lineItemAmount' name='amount[]'>";
        $total_cost  = "<td class='txtRight text totalCost' name='totalCost[]'></td>";
        $clear       = "<td class='text'><a  class='clearLineItem'><u>Clear</u></a></td>";

        $rows = "
        <tr>
            <td>{$description}</td>
            <td>{$quantity}</td>
            <td>{$unit_rate}</td>
            <td>{$amount}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$description}</td>
            <td>{$quantity}</td>
            <td>{$unit_rate}</td>
            <td>{$amount}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$description}</td>
            <td>{$quantity}</td>
            <td>{$unit_rate}</td>
            <td>{$amount}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$description}</td>
            <td>{$quantity}</td>
            <td>{$unit_rate}</td>
            <td>{$amount}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$description}</td>
            <td>{$quantity}</td>
            <td>{$unit_rate}</td>
            <td>{$amount}</td>
            {$clear}
        </tr>
        ";          

        $newRow = "
        <a class='addWORow btn btn-primary mb10' project_id='{$project_id}'>Add Line Item</a>
        ";

        $header ="
        <tr>{$newRow}</tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Description</th>
            <th class='txtCenter'>Quantity</th>
            <th class='txtCenter'>Unit Rate</th>
            <th class='txtRight'>Amount</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?widget=enggCrm_projectWorkOrder&_spAction=addMultipleWOItemSubmit&showHTML=0";
        $expNoEdit = array('isEditable' => 0);
        
        $text = "
        <form id='addMultipleWOItemForm' class='addMultipleWOItemForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expNoEdit)}
            <table class='thinlist' id='quoteItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='sub_con_work_order_id' value='{$sub_con_work_order_id}' />
        </form>
        ";

        return $text;
    }
}