<?
class CPL_Admin_Widgets_EnggCrm_ProjectClaim_View extends CP_Common_Lib_WidgetViewAbstract
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
    function getAddClaimPortalListView($project_id = '') {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');
        $media    = Zend_Registry::get('media');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        $rowProject = $fn->getRecordRowByID('project', 'project_id', $project_id);

        $SQL = "
        SELECT pc.*
              ,c.company_name
        FROM `project_claim` pc
        LEFT JOIN (project p) ON (p.project_id = pc.project_id)
        LEFT JOIN (company c) ON (c.company_id = pc.client_id)
        WHERE pc.project_id = {$project_id}
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $totalClaimAmount   = '';
        $claimPaymentLinked = '';
        $rows               = '';
        $addNewClaim        = '';
        while ($row = $db->sql_fetchrow($result)) {
                $claim_date = $fn->getCPDate($row['claim_date'], 'd-m-Y');

                $sqlClaimItems ="
                SELECT SUM(amount) AS claimAmount
                FROM claim_line_items
                WHERE project_claim_id = {$row['project_claim_id']}
                ";
                $resultClaimItems = $db->sql_query($sqlClaimItems);
                $rowClaimItems    = $db->sql_fetchrow($resultClaimItems);

                $addLineItemView      = '';
                $editLineItemView     = '';
                $claimPaymentItemView = '';
                if($rowClaimItems['claimAmount'] > 0) {
                    $editLineItemView ="
                    <div class='float_left'>
                        <a href='javascript:void(0);' project_id='{$project_id}' project_claim_id='{$row['project_claim_id']}' class='editClaimLineItem' title='Edit Progress Claim'>Edit PC</a>
                    </div>
                    ";

                    $addLineItemView ="
                    <div class='float_left'>
                        <a href='javascript:void(0);' class='claimLayoutShow'>View Line Items</a>
                    </div>
                    ";

                    $claimPaymentItemView = "
                    <div class='float_left'>
                        <a href='javascript:void(0);' class='claimPaymentDetailsShow'>View PC Items</a>
                    </div>
                    ";
                }

                $claimActions = '';
                $add_image    = $cpCfg['cp.localPath']."images/add.png";
                $edit_image   = $cpCfg['cp.localPath']."images/edit.png";
                $addNewClaim  = "";
                $editForClaim = "index.php?widget=enggCrm_projectClaim&_spAction=editForClaim&project_id={$project_id}&project_claim_id={$row['project_claim_id']}&showHTML=0";
                if($rowClaimItems['claimAmount'] > 0) {
                    $claimActions ="
                    <div class='float_box clearfix'>
                        <div class='float_left'>
                            <a class='editForClaim' href='{$editForClaim}' title='Edit Project Claim'><img src='{$edit_image}' class='icon'></a>
                        </div>
                    </div>
                    ";

                    $addNewClaim = "
                    <div>
                      <a  class='addNewClaim btn btn-primary mb10' project_id='{$project_id}' project_claim_id='{$row['project_claim_id']}'>New PC</a>
                    </div>
                    ";

                } else {
                    $claimActions ="
                    <div class='float_box clearfix'>
                        <div class='float_left'>
                            <a class='editForClaim' href='{$editForClaim}' title='Edit Project Claim'><img src='{$edit_image}' class='icon'></a>
                        </div>
                        <div class='float_left'>
                            <a project_id='{$row['project_id']}' project_claim_id='{$row['project_claim_id']}' class='addMultipleClaimItem' title='Add PC Items'><img src='{$add_image}' class='icon'/></a>
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

                $confirmedClaimStatus = '';
                if($row['status'] == 'Confirmed') {
                    $confirmedClaimStatus = 'confirmedClaim';
                }

                $claimAmount = number_format($row['amount'], 2);

                //{$this->getAddLineItemForClaim($project_id, $row['project_claim_id'])}                
                $claimPaymentLinked = $this->getAddClaimPaymentPortal($project_id, $row['project_claim_id']);
                $claimPaymentLinked = "
                <tr class='claimPaymentDetails claimPaymentHide'>
                  <td></td>
                  <td colspan='6'>
                    {$claimPaymentLinked}
                  </td>
                </tr>
                ";

                $rows .= "
                <tbody class='claimDetailRow'>
                    <tr class='addClaimRow {$confirmedClaimStatus}'>
                        <td>{$row['claim_code']}</td>
                        <td>{$claim_date}</td>
                        <td>
                            <a class='creationModificationDetails' record_id='{$row['project_claim_id']}' field_name='project_claim_id' table_name='project_claim'>
                                <u>{$row['project_title']}</u>
                            </a>
                        </td>
                        <td>{$claimActions}</td>
                        <td>{$row['status']}</td>
                        <td class='txtRight'>{$claimAmount}</td>
                        <td class='claimActionsDetails'>
                          {$claimPaymentItemView}
                          {$editLineItemView}
                        </td>
                    </tr>
                    {$claimPaymentLinked}
                </tbody>
                ";

            }

            $quoteRows = $fn->getRecordCount('quote', "project_id = {$project_id}");
            $addClaimBtn = "";
            if ($quoteRows > 0 && $numRows == 0) {
                $addClaimBtn = "
                <div class='float_box mb10'>
                  <div class='mb10'>
                      <a  id='addClaimProject' class='btn btn-primary' project_id='{$project_id}'>Add Claim</a>
                  </div>
                </div>
                  ";
            }

            $text = "
            {$addClaimBtn}
            ";

            if($numRows > 0)  {  
              $text .= "
              {$addNewClaim}
              <div id='quotesPortal' class='linkPortalWrapper'>
                  <table class ='list'>
                      <thead>
                          <tr>
                              <th colspan='9' align='left' class='rightPanelHeading'>
                                Claim
                              </th>
                          </tr>
                          <tr>
                              <th>Code</th>
                              <th>Date</th>
                              <th>Title</th>
                              <th>Action</th>
                              <th>Status</th>
                              <th class='txtRight'>Amount</th>
                              <th width='30%'></th>
                          </tr>
                      </thead>
                          {$rows}
                  </table>
              </div>
              ";

            }

            $text .= "
            <div class='col-md-6 col-sm-6 col-xs-12 noPadding'>
                {$media->getRightPanelMediaDisplay('Claim Attachment', 'enggCrm_project', 'claimAttachment', $rowProject)}
            </div>";
        
        return $text;
    }

    /**
     *
     */
    function getAddLineItemForClaim($project_id, $project_claim_id) {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $SQL = "
        SELECT ct.*
        FROM `claim_line_items` ct
        LEFT JOIN project_claim pc ON (pc.project_claim_id = ct.project_claim_id)
        WHERE pc.project_id       = {$project_id}
        AND   ct.project_claim_id = {$project_claim_id}
        ORDER BY ct.claim_line_items_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $editForLineItem = '';
            $deleteLineItem  = '';
            $edit_image   = $cpCfg['cp.localPath']."images/edit.png";
            $delete_image = $cpCfg['cp.localPath']."images/delete.png";

            $addclass = '';
            if ($row['project_id'] != '') {
                $addclass = 'claimFromProj';
            }

            $total_amount = number_format($row['amount'], 2);

            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - ' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }

            $rows .= "
            <tr class='claimLayoutHide showAddLineRow {$addclass}'>
                <td></td>
                <td class='descriptionWrap'>{$row['title']}</td>
                <td class='descriptionWrap'>{$row['description']}</td>
                <td class='amountRow'>{$total_amount}</td>
                <td>{$updation_details}</td>
            </tr>
            ";
        }

        $text = '';

        if ($numRows > 0)  {
            $text = "
                <tr class = 'claimLayoutHide showAddLineRow'>
                    <th></th>
                    <th class='quoteRowBackground'>Title</th>
                    <th class='quoteRowBackground'>Description</th>
                    <th class='quoteRowBackground txtRight'>Amount</th>
                    <th class='quoteRowBackground'>Updated By</th>
                </tr>
                {$rows}
            ";

            return $text;
        }
    }

    function getEditForClaim() {
        $fn      = Zend_Registry::get('fn');
        $tv      = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $cpUtil  = Zend_Registry::get('cpUtil');

        $project_claim_id = $fn->getReqParam('project_claim_id');
        $project_id       = $fn->getReqParam('project_id');

        $rowClaim   = $fn->getRecordRowByID('project_claim', 'project_claim_id', $project_claim_id);
        $rowProject = $fn->getRecordRowByID('project', 'project_id', $project_id);

        $formActionEditForClaim = "index.php?widget=enggCrm_projectClaim&_spAction=editForClaimSubmit&lnkRoom={$tv['lnkRoom']}&project_claim_id={$rowClaim['project_claim_id']}&project_id={$project_id}&showHTML=0";

        $expNoEdit       = array('isEditable' => 0);
        $expHideFirstOpt = array('hideFirstOption' => true);
        $expClaimDate    = array('maxDate' => date('Y-m-d'), 'yearEnd' => date('Y'));

        //$status = "<input type='hidden' name='status' value='{$rowClaim['status']}' />";
        $spArrayClaimStatus = array('In Progress', 'Confirmed', 'Claim Amount Recieved', 'Cancelled');

        $text = "
        <form id='editForClaim' class='yform columnar editClaim' method='post' action='{$formActionEditForClaim}'>
            <fieldset>
                <table width='100%'>
                    <tr>
                        <td>{$formObj->getDateRow('Claim Date', 'claim_date', $rowClaim['claim_date'], $expClaimDate)}</td>
                        <td>{$formObj->getDDRowByArr('Status', 'status', $spArrayClaimStatus, $rowClaim['status'])}</td>
                    </tr>
                    <tr>
                        <td>{$formObj->getTBRow('Project Title', 'project_title', $rowClaim['project_title'])}</td>
                        <td>{$formObj->getTBRow('PO / Quote No', 'po_quote_no', $rowClaim['po_quote_no'])}</td>
                    </tr>
                    <tr>
                        <td>{$formObj->getTBRow('Ref No', 'ref_no', $rowClaim['ref_no'])}</td>
                        <td>{$formObj->getTBRow('Contract Sum Amount', 'amount', $rowClaim['amount'])}</td>
                    </tr>
                    <tr>
                        <td>{$formObj->getTBRow('Variation Order Submission', 'variation_order_submission', $rowClaim['variation_order_submission'])}</td>
                        <td>{$formObj->getTBRow('Value of Contract Work Done', 'value_of_contract_work_done', $rowClaim['value_of_contract_work_done'])}</td>
                    </tr>
                    <tr>
                        <td>{$formObj->getTBRow('VO Claim Work Done', 'vo_claim_work_done', $rowClaim['vo_claim_work_done'])}</td>
                        <td>{$formObj->getTBRow('Less Previous Retention', 'less_previous_retention', $rowClaim['less_previous_retention'])}</td>
                    </tr>
                    <tr>
                        <td colspan='2'>{$formObj->getTARow('Work Description', 'description', $rowClaim['description'])}</td>
                    </tr>
                </table>
                <input type='hidden' name='project_id' value='{$project_id}' />
                <input type='hidden' name='project_claim_id' value='{$project_claim_id}' />
            </fieldset>
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddMultipleClaimItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $formObj = Zend_Registry::get('formObj');
        $expEdit = array('isEditable' => 0);

        $project_id           = $fn->getReqParam('project_id');
        $project_claim_id     = $fn->getReqParam('project_claim_id');
        $rowClaim             = $fn->getRecordRowByID('project_claim', 'project_claim_id', $project_claim_id);
        $title                = "<textarea type='text' value='' id='title' class='text claimTitle' name='title[]'></textarea>";
        $description          = "<textarea type='text' value='' id='description' class='text claimItemDescription' name='description[]'></textarea>";
        $amount               = "<input type='text' value='' id='amount' class='text claimAmount' name='amount[]'>";
        $prev_amount          = "<td class='txtRight text prev_amount' name='prev_amount[]'></td>";
        $current_month_amount = "<input type='text' value='' id='thisMonthAmount' class='text thisMonthAmount' name='current_month_amount[]'>";
        $cum_amount           = "<td class='txtRight text cumAmount' name='cum_amount[]'></td>";
        $remarks              = "<textarea type='text' value='' id='remarks' class='text claimRemarks' name='remarks[]'></textarea>";
        $clear                = "<td class='text'><a  class='clearClaimItem'><u>Clear</u></a></td>";

        $rows = "
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td class='txtRight'>{$amount}</td>
            <td class='txtRight'>{$current_month_amount}</td>
            {$cum_amount}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td class='txtRight'>{$amount}</td>
            <td class='txtRight'>{$current_month_amount}</td>
            {$cum_amount}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td class='txtRight'>{$amount}</td>
            <td class='txtRight'>{$current_month_amount}</td>
            {$cum_amount}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td class='txtRight'>{$amount}</td>
            <td class='txtRight'>{$current_month_amount}</td>
            {$cum_amount}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td class='txtRight'>{$amount}</td>
            <td class='txtRight'>{$current_month_amount}</td>
            {$cum_amount}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        ";

        $newRow = "<a  class='addSingleClaimRow btn btn-info mb10 mr10'>Add More Items</a>";

        $SQLClaimPayment = "
        SELECT * 
        FROM claim_payment
        WHERE (claim_seq IS NOT NULL OR claim_seq != '')
          AND project_id = '{$project_id}'
          AND project_claim_id = '{$project_claim_id}'
        ORDER BY claim_payment_id DESC
        LIMIT 1
        ";
        $resultClaimPayment = $db->sql_query($SQLClaimPayment);
        $rowClaimPayment    = $db->sql_fetchrow($resultClaimPayment);
        $claimSeq = $rowClaimPayment['claim_seq'];

        if($claimSeq != "") {
           $claimSeq++;
        } else {
           $claimSeq = "Progress Claim 01";
        }

        $header = "
        <tr>
            {$newRow}
        </tr>
        <tr>
            <td>{$formObj->getDateRow('Date', 'claim_date', $rowClaim['claim_date'])}</td>
            <td class='MainFields' colspan='4'>{$formObj->getTBRow('Project', 'project_title', $rowClaim['project_title'])}</td>
            <td class='MainFields' colspan='3'>{$formObj->getTBRow('Claim Sequence', 'claim_seq', $claimSeq)}</td>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th>Title</th>
            <th>Description</th>
            <th class='txtRight'>Contract Amount</th>
            <th class='txtRight'>This Month Amount</th>
            <th class='txtRight'>Cum Amount</th>
            <th>Remarks</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?widget=enggCrm_projectClaim&_spAction=addMultipleClaimItemFormSubmit&showHTML=0";

        $text = "
        <form id='addMultipleClaimItemForm' class='yform addMultipleClaimItemForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='thinlist' id='claimItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='project_claim_id' value='{$project_claim_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddSingleClaimRow() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');

        $title                = "<textarea type='text' value='' id='title' class='text claimTitle' name='title[]'></textarea>";
        $description          = "<textarea type='text' value='' id='description' class='text claimItemDescription' name='description[]'></textarea>";
        $amount               = "<input type='text' value='' id='amount' class='text claimAmount' name='amount[]'>";
        $prev_amount          = "<td class='txtRight text prev_amount' name='prev_amount[]'></td>";
        $current_month_amount = "<input type='text' value='' id='thisMonthAmount' class='text thisMonthAmount' name='current_month_amount[]'>";
        $cum_amount           = "<td class='txtRight text cumAmount' name='cum_amount[]'></td>";
        $remarks              = "<textarea type='text' value='' id='remarks' class='text claimRemarks' name='remarks[]'></textarea>";
        $clear                = "<td class='text'><a  class='clearPo'><u>Clear</u></a></td>";

        $rows = "
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td class='txtRight'>{$amount}</td>
            <td class='txtRight'>{$current_month_amount}</td>
            {$cum_amount}
            <td>{$remarks}</td>
            {$clear}
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getEditClaimLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');
        $expEdit = array('isEditable' => 0);

        $project_id       = $fn->getReqParam('project_id');
        $project_claim_id = $fn->getReqParam('project_claim_id');
        $rowClaim         = $fn->getRecordRowByID('project_claim', 'project_claim_id', $project_claim_id);
        
        $sqlClaimItems ="
        SELECT ct.*
        FROM claim_line_items ct
        WHERE project_claim_id = {$project_claim_id}
        ORDER BY claim_line_items_id ASC
        ";
        $resultClaimItems = $db->sql_query($sqlClaimItems);
        $rows     = "";
        $claimSeq = "";

        $statusArray = array(
           "In Progress"
          ,"On Hold"
          ,"Work Completed"
        );

        while ($rowClaimItems = $db->sql_fetchrow($resultClaimItems)) {
            $SQLClaimPayment = "
            SELECT * 
            FROM claim_payment
            WHERE claim_line_items_id = '{$rowClaimItems['claim_line_items_id']}'
            ORDER BY claim_payment_id DESC
            LIMIT 1
            ";
            $resultClaimPayment = $db->sql_query($SQLClaimPayment);
            $rowClaimPayment    = $db->sql_fetchrow($resultClaimPayment);
            $prevAmountPaid     = number_format($rowClaimPayment['amount'], 2);

            $SQLClaimPaymentTotal = "
            SELECT SUM(amount) AS totalAmount 
            FROM claim_payment
            WHERE claim_line_items_id = '{$rowClaimItems['claim_line_items_id']}'
            ORDER BY claim_payment_id DESC
            LIMIT 1
            ";
            $resultClaimPaymentTotal = $db->sql_query($SQLClaimPaymentTotal);
            $rowClaimPaymentTotal    = $db->sql_fetchrow($resultClaimPaymentTotal);
            $balanceAmount           = $rowClaimItems['amount'] - $rowClaimPaymentTotal['totalAmount'];

            $SQLClaimPaymentOverallTotal = "
            SELECT SUM(amount) AS totalAmount 
            FROM claim_payment
            WHERE claim_line_items_id = '{$rowClaimItems['claim_line_items_id']}'
            LIMIT 1
            ";
            $resultClaimPaymentOverallTotal = $db->sql_query($SQLClaimPaymentOverallTotal);
            $rowClaimPaymentOverallTotal    = $db->sql_fetchrow($resultClaimPaymentOverallTotal);

            $title = "
            <textarea type='text' id='title' class='text claimTitle' name='title[]'>{$rowClaimItems['title']}</textarea>
            ";

            $description = "
            <textarea type='text' id='description' class='text claimItemDescription' name='description[]'>{$rowClaimItems['description']}</textarea>
            <input type='hidden' class='totalClaimAmount' name='totalClaimAmount[]' value='{$rowClaimPaymentTotal['totalAmount']}'/>
            <input type='hidden' name='claim_line_items_id[]' value='{$rowClaimItems['claim_line_items_id']}'/>
            ";

            $amount = "
            <input type='text' id='amount' class='text claimAmount' name='amount[]' value='{$rowClaimItems['amount']}'>
            <input type='hidden' class='overallClaimTotalAmount' name='overallClaimTotalAmount[]' value='{$rowClaimPaymentOverallTotal['totalAmount']}'/>
            ";
            
            $remarks = "
            <textarea type='text' id='remarks' class='text claimRemarks' name='remarks[]'>{$rowClaimItems['remarks']}</textarea>
            ";
            
            $balanceAmountFormatted = number_format($balanceAmount, 2);
            
            $cum_amount = "
            <td class='txtRight text cumAmount' name='cum_amount[]'>
              {$balanceAmountFormatted}
            </td>
            ";

            $prev_amount = "
            <td class='txtRight text prev_amount' name='prev_amount[]'>{$prevAmountPaid}</td>
            ";

            $current_month_amount = "
            <input type='text' id='thisMonthAmount' class='text thisMonthAmount' name='current_month_amount[]'>
            ";

            $clear = "
            <td class='text'>
              <a  class='clearClaimItem'>
                <u>Clear</u>
              </a>
            </td>";

            $status = "
            <input type='hidden' class='claimItemStatus' name='claimItemStatus[]' value='{$rowClaimItems['status']}'/>
            <select name='claim_status[]' class='claimStatusDropdown'>
                {$cpUtil->getDropDown1($statusArray, $rowClaimItems['status'])}
            </select>
            ";

            $rows .= "
            <tr>
                <td>{$title}</td>
                <td>{$description}</td>
                <td class='txtRight'>{$amount}</td>
                <td class='txtCenter'>{$status}</td>
            </tr>
            ";

            $claimSeq = $rowClaimPayment['claim_seq'];
        }

        $newRow = "<a  class='addSingleClaimEditRow btn btn-info mb10 mt30 mr10'>Add More Items</a>";

        $header = "
        <tr style='background-color:#EAEAE8;'>
            <th>Title</th>
            <th>Description</th>
            <th class='txtRight'>Contract Amount</th>
            <th class='txtCenter'>Status</th>
        </tr>
        ";

        $formAction = "index.php?widget=enggCrm_projectClaim&_spAction=editMultipleClaimItemFormSubmit&showHTML=0";

        $text = "
        <form id='editMultipleClaimItemForm' class='yform editMultipleClaimItemForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <div class='float_box'>
                <div class='float_left'>
                  {$newRow}
                </div>
                <div class='float_left'>
                  {$formObj->getDateRow('Date', 'claim_date', $rowClaim['claim_date'])}
                </div>
                <div class='MainFields float_left col-md-6'>
                  {$formObj->getTBRow('Project', 'project_title', $rowClaim['project_title'])}
                </div>
            </div>
            <table class='thinlist' id='claimItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='project_claim_id' value='{$project_claim_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getAddSingleClaimEditRow() {
        $fn     = Zend_Registry::get('fn');
        $db     = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        $title       = "<textarea type='text' value='' id='title' class='text claimTitle' name='title[]'></textarea>";
        
        $description = "
        <textarea type='text' value='' id='description' class='text claimItemDescription' name='description[]'></textarea>
        <input type='hidden' name='claim_line_items_id[]' value=''/>
        ";
        
        $amount      = "<input type='text' value='' id='amount' class='text claimAmount' name='amount[]'>";
        $prev_amount = "<td class='txtRight text prev_amount' name='prev_amount[]'></td>";
        $cum_amount  = "<td class='txtRight text cumAmount' name='cum_amount[]'></td>";
        
        $statusArray = array(
           "In Progress"
          ,"On Hold"
          ,"Work Completed"
        );

        $status = "
        <select name='claim_status[]'>
            {$cpUtil->getDropDown1($statusArray, '')}
        </select>
        ";

        $rows = "
        <tr>
            <td>{$title}</td>
            <td>{$description}</td>
            <td class='txtRight'>{$amount}</td>
            <td class='txtCenter'>{$status}</td>
        </tr>
        ";

        return $rows;
    }

    /**
     *
     */
    function getAddClaimPaymentPortal($project_id='', $project_claim_id='') {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        if($project_id == ''){
            $project_id = $fn->getReqParam('project_id');
        }

        $SQL = "
        SELECT cp.*
              ,SUM(amount) AS claim_amount
              ,count(claim_payment_id) AS countRec
        FROM `claim_payment` cp
        WHERE cp.project_id = {$project_id}
          AND cp.project_claim_id = '{$project_claim_id}'
        GROUP BY cp.claim_seq
        ";
        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $rows = '';
        while ($row = $db->sql_fetchrow($result)) {
            $claim_date = $fn->getCPDate($row['date'], 'd-m-Y');

            $editLineItemView ="
            <div class='float_right'>
                <a href='javascript:void(0);' project_id='{$project_id}' project_claim_id='{$row['project_claim_id']}' claim_seq='{$row['claim_seq']}' class='editClaimPaymentLineItem'>Edit PC Items</a>
            </div>
            ";

            $updation_details = '';
            if ($row['modified_by']) {
                $updation_details = $row['modified_by'] . ' - <br/>' . $dateUtil->formatDate($row['modification_date'], 'DD-MM-YYYY HHH:MIN:SS');
            } else {
                $updation_details = $row['created_by'] . ' - <br/> ' . $dateUtil->formatDate($row['creation_date'], 'DD-MM-YYYY HHH:MIN:SS');
            }

            $confirmedClaimStatus = '';
            if($row['status'] == 'Confirmed') {
                $confirmedClaimStatus = 'confirmedClaim';
            }

            $claimAmount = number_format($row['claim_amount'], 2);

            $printClaimPDFLink = "index.php?widget=enggCrm_projectClaim&_spAction=printClaimPdf&project_id={$project_id}&project_claim_id={$row['project_claim_id']}&claim_seq={$row['claim_seq']}&showHTML=0";
            $printClaim = "<a href='{$printClaimPDFLink}' target='_blank'>Print PC</a>";

            $printClaimSummaryPDFLink = "index.php?widget=enggCrm_projectClaim&_spAction=printClaimSummaryPdf&project_id={$project_id}&project_claim_id={$row['project_claim_id']}&claim_seq={$row['claim_seq']}&showHTML=0";
            $printClaimSummary = "<a href='{$printClaimSummaryPDFLink}' target='_blank'>Print PC Summary</a>";

            $SQLCheck = "
            SELECT cp.*
            FROM `claim_payment` cp
            WHERE cp.project_id = {$row['project_id']}
              AND cp.claim_seq  = '{$row['claim_seq']}'
              AND cp.status     = 'Paid'
            ";
            $resultCheck  = $db->sql_query($SQLCheck);
            $numRowsCheck = $db->sql_numrows($resultCheck);

            if($numRowsCheck > 0) {
                if($row['countRec'] == $numRowsCheck) {
                    $statusRec = "Paid";
                } else {
                    $statusRec = "Partially Paid";
                }
            } else {
                $statusRec = "In Progress";
            }

            $rows .= "
            <tbody class='claimDetailRow'>
                <tr class='addClaimRow claimPaymentLinkedRow {$confirmedClaimStatus}'>
                    <td>{$claim_date}</td>
                    <td>
                        <a class='creationModificationDetails' record_id='{$row['claim_payment_id']}' field_name='claim_payment_id' table_name='claim_payment'>
                            <u>{$row['claim_seq']}</u>
                        </a>
                    </td>
                    <td class='txtRight'>{$claimAmount}</td>
                    <td>{$statusRec}</td>
                    <td class=''>
                      {$editLineItemView}
                    </td>
                    <td>
                      {$printClaim} | {$printClaimSummary}
                    </td>
                </tr>
            </tbody>
            ";
        }


        $text = '';

        if($numRows > 0)  {    
          $text = "
          <table class ='list'>
              <thead>
                  <tr>
                      <th class='claimPaymentHeadingBackground'>Date</th>
                      <th class='claimPaymentHeadingBackground'>Claim Seq</th>
                      <th class='claimPaymentHeadingBackground txtRight'>Amount</th>
                      <th class='claimPaymentHeadingBackground'>Status</th>
                      <th class='claimPaymentHeadingBackground txtRight'>Edit</th>
                      <th class='claimPaymentHeadingBackground'>Print</th>
                  </tr>
              </thead>
              {$rows}
          </table>
          ";

          return $text;
      }
    }

    /**
     *
     */
    function getAddClaimPaymentLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');
        $expEdit = array('isEditable' => 0);

        $project_id       = $fn->getReqParam('project_id');
        $project_claim_id = $fn->getReqParam('project_claim_id');
        $rowClaim         = $fn->getRecordRowByID('project_claim', 'project_claim_id', $project_claim_id);
        
        $sqlClaimItems ="
        SELECT ct.*
        FROM claim_line_items ct
        WHERE project_claim_id = {$project_claim_id}
          AND status != 'Work Completed'
        ORDER BY claim_line_items_id ASC
        ";
        $resultClaimItems = $db->sql_query($sqlClaimItems);
        $rows     = "";
        $claimSeq = "";

        $statusArray = array(
           "In Progress"
          ,"On Hold"
          ,"Work Completed"
        );

        while ($rowClaimItems = $db->sql_fetchrow($resultClaimItems)) {
            $SQLClaimPaymentTotal = "
            SELECT SUM(amount) AS totalAmount 
            FROM claim_payment
            WHERE claim_line_items_id = '{$rowClaimItems['claim_line_items_id']}'
            ORDER BY claim_payment_id DESC
            LIMIT 1
            ";
            $resultClaimPaymentTotal = $db->sql_query($SQLClaimPaymentTotal);
            $rowClaimPaymentTotal    = $db->sql_fetchrow($resultClaimPaymentTotal);
            $balanceAmount           = $rowClaimItems['amount'] - $rowClaimPaymentTotal['totalAmount'];
            $prevAmountPaid          = number_format($rowClaimPaymentTotal['totalAmount'], 2);

            $title = "
            <td id='title' class='text claimTitle' name='title[]'>{$rowClaimItems['title']}</td>
            ";

            $description = "
            <td id='description' class='text claimItemDescription' name='description[]'>{$rowClaimItems['description']}</td>
            <input type='hidden' class='totalClaimAmount' name='totalClaimAmount[]' value='{$rowClaimPaymentTotal['totalAmount']}'/>
            <input type='hidden' class='balanceAmount' name='balanceAmount[]' value='{$balanceAmount}'/>
            <input type='hidden' name='claim_line_items_id[]' value='{$rowClaimItems['claim_line_items_id']}'/>
            ";

            $contractAmount = number_format($rowClaimItems['amount'], 2);
            $amount = "
            <td class='text claimAmount txtRight' name='amount[]'>{$contractAmount}</td>
            ";
            
            $remarks = "
            <textarea type='text' id='remarks' class='text claimRemarks' name='remarks[]'>{$rowClaimItems['remarks']}</textarea>
            ";
            
            $balanceAmountFormatted = number_format($balanceAmount, 2);
            
            $cum_amount = "
            <td class='txtRight text cumAmount' name='cum_amount[]'>
              {$prevAmountPaid}
            </td>
            ";

            $prev_amount = "
            <td class='txtRight text prev_amount' name='prev_amount[]'>{$prevAmountPaid}</td>
            <input type='hidden' class='prevClaimAmount' name='prevClaimAmount[]' value='{$rowClaimPaymentTotal['totalAmount']}'/>
            ";

            $current_month_amount = "
            <input type='text' id='thisMonthAmount' class='text thisMonthAmount' name='current_month_amount[]'>
            ";

            $clear = "
            <td class='text'>
              <a  class='clearClaimItem'>
                <u>Clear</u>
              </a>
            </td>";

            $rows .= "
            <tr>
                {$title}
                {$description}
                {$amount}
                {$prev_amount}
                <td class='txtRight'>{$current_month_amount}</td>
                {$cum_amount}
                <td>{$remarks}</td>
                {$clear}
            </tr>
            ";
        }

        $SQLClaimPayment = "
        SELECT * 
        FROM claim_payment
        WHERE (claim_seq IS NOT NULL OR claim_seq != '')
        ORDER BY claim_payment_id DESC
        LIMIT 1
        ";
        $resultClaimPayment = $db->sql_query($SQLClaimPayment);
        $rowClaimPayment    = $db->sql_fetchrow($resultClaimPayment);
        $claimSeq = $rowClaimPayment['claim_seq'];

        if($claimSeq != "") {
           $claimSeq++;
        }

        $newRow = "<a  class='addSingleClaimRow btn btn-info mb10 mr10'>Add More Items</a>";
        $currentDate = date("Y-m-d");

        $header = "
        <tr>
            <td>{$formObj->getDateRow('Date', 'claim_date', $currentDate)}</td>
            <td colspan='3' class='MainFields'>{$formObj->getTBRow('Project', 'project_title', $rowClaim['project_title'], $expEdit)}</td>
            <td colspan='3' class='MainFields'>{$formObj->getTBRow('Claim Sequence', 'claim_seq', $claimSeq)}</td>
        </tr>
        <tr style='background-color:#EAEAE8;'>
            <th width='18%'>Title</th>
            <th>Description</th>
            <th class='txtRight'>Contract Amount</th>
            <th class='txtRight'>Prev Amount</th>
            <th class='txtRight'>This Month Amount</th>
            <th class='txtRight'>Cum Amount</th>
            <th>Remarks</th>
            <th></th>
        </tr>
        ";

        $formAction = "index.php?widget=enggCrm_projectClaim&_spAction=addMultipleClaimPaymentItemFormSubmit&showHTML=0";

        $text = "
        <form id='addMultipleClaimPaymentItemForm' class='yform addMultipleClaimPaymentItemForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <table class='thinlist' id='claimItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='project_claim_id' value='{$project_claim_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getEditClaimPaymentLineItem() {
        $fn      = Zend_Registry::get('fn');
        $db      = Zend_Registry::get('db');
        $dbUtil  = Zend_Registry::get('dbUtil');
        $cpUtil  = Zend_Registry::get('cpUtil');
        $formObj = Zend_Registry::get('formObj');
        $expEdit = array('isEditable' => 0);

        $project_id       = $fn->getReqParam('project_id');
        $project_claim_id = $fn->getReqParam('project_claim_id');
        $claim_seq        = $fn->getReqParam('claim_seq');
        
        $sqlClaimItems ="
        SELECT cp.*
               ,pc.claim_date
               ,pc.project_title
               ,ct.amount AS claimAmount
               ,ct.title
               ,ct.description
        FROM claim_payment cp
        LEFT JOIN claim_line_items ct ON (ct.claim_line_items_id = cp.claim_line_items_id)
        LEFT JOIN project_claim pc ON (pc.project_claim_id = cp.project_claim_id)
        WHERE ct.project_claim_id = '{$project_claim_id}'
          AND ct.project_id = '{$project_id}'
          AND cp.claim_seq  = '{$claim_seq}'
        ORDER BY ct.claim_line_items_id ASC
        ";
        $resultClaimItems = $db->sql_query($sqlClaimItems);
        $rows          = "";
        $claimSeq      = "";
        $claim_date    = "";
        $project_title = "";

        $statusArray = array(
           "In Progress"
          ,"On Hold"
          ,"Paid"
        );

        while ($rowClaimItems = $db->sql_fetchrow($resultClaimItems)) {
            $SQLClaimPayment = "
            SELECT SUM(amount) AS amount 
            FROM claim_payment
            WHERE claim_line_items_id = '{$rowClaimItems['claim_line_items_id']}'
              AND claim_payment_id < '{$rowClaimItems['claim_payment_id']}'
            ORDER BY claim_payment_id DESC
            LIMIT 1
            ";
            $resultClaimPayment = $db->sql_query($SQLClaimPayment);
            $rowClaimPayment    = $db->sql_fetchrow($resultClaimPayment);
            $prevAmountPaid     = number_format($rowClaimPayment['amount'], 2);
            $balancePrevAmount  = $rowClaimItems['claimAmount'] - $rowClaimPayment['amount'];

            $SQLClaimPaymentTotal = "
            SELECT SUM(amount) AS totalAmount 
            FROM claim_payment
            WHERE claim_line_items_id = '{$rowClaimItems['claim_line_items_id']}'
              AND claim_payment_id <= '{$rowClaimItems['claim_payment_id']}'
            ORDER BY claim_payment_id DESC
            LIMIT 1
            ";
            $resultClaimPaymentTotal = $db->sql_query($SQLClaimPaymentTotal);
            $rowClaimPaymentTotal    = $db->sql_fetchrow($resultClaimPaymentTotal);
            $balanceAmount           = $rowClaimItems['claimAmount'] - $rowClaimPaymentTotal['totalAmount'];

            $SQLClaimPaymentOverallTotal = "
            SELECT SUM(amount) AS totalAmount 
            FROM claim_payment
            WHERE claim_line_items_id = '{$rowClaimItems['claim_line_items_id']}'
              AND claim_payment_id   != '{$rowClaimItems['claim_payment_id']}'
            LIMIT 1
            ";
            $resultClaimPaymentOverallTotal = $db->sql_query($SQLClaimPaymentOverallTotal);
            $rowClaimPaymentOverallTotal    = $db->sql_fetchrow($resultClaimPaymentOverallTotal);

            $title = "
            <td type='text' id='title' class='text claimTitle' name='title[]'>{$rowClaimItems['title']}</td>
            ";

            $description = "
            <td type='text' id='description' class='text claimItemDescription' name='description[]'>{$rowClaimItems['description']}</td>
            <input type='hidden' class='totalClaimAmount' name='totalClaimAmount[]' value='{$rowClaimPaymentTotal['totalAmount']}'/>
            <input type='hidden' name='claim_line_items_id[]' value='{$rowClaimItems['claim_line_items_id']}'/>
            <input type='hidden' class='overallClaimTotalAmount' name='overallClaimTotalAmount[]' value='{$rowClaimPaymentOverallTotal['totalAmount']}'/>
            <input type='hidden' class='currentMonthClaimAmount' name='currentMonthClaimAmount[]' value='{$rowClaimItems['amount']}'/>
            <input type='hidden' class='contractAmount' name='contractAmount[]' value='{$rowClaimItems['claimAmount']}'/>
            ";

            $claimAmount = number_format($rowClaimItems['claimAmount'], 2);
            $amount = "
            <td class='text claimAmount txtRight' name='amount[]'>{$claimAmount}</td>
            ";

            if($rowClaimPayment['amount'] == "") {
                $rowClaimPayment['amount'] = 0;
            }

            $prev_amount = "
            <td class='txtRight text prev_amount' name='prev_amount[]'>
              {$prevAmountPaid}
              <input type='hidden' class='prevClaimAmount' name='prevClaimAmount[]' value='{$rowClaimPayment['amount']}'/>
              <input type='hidden' class='balancePrevAmount' name='balancePrevAmount[]' value='{$balancePrevAmount}'/>
            </td>
            ";
            
            $cumAmountFormatted = number_format($rowClaimPaymentTotal['totalAmount'], 2);
            
            $cum_amount = "
            <td class='txtRight text cumAmount' name='cum_amount[]'>
              {$cumAmountFormatted}
            </td>
            ";

            $status = "
            <select name='claim_status[]'>
                {$cpUtil->getDropDown1($statusArray, $rowClaimItems['status'])}
            </select>
            ";

            $current_month_amount = "
            <input type='text' id='thisMonthAmount' class='text thisMonthAmount' name='current_month_amount[]' value='{$rowClaimItems['amount']}'>
            <input type='hidden' class='claim_payment_id' name='claim_payment_id[]' value='{$rowClaimItems['claim_payment_id']}'/>
            ";

            $rows .= "
            <tr>
                {$title}
                {$description}
                {$amount}
                <td class='txtCenter'>{$status}</td>
                {$prev_amount}
                <td class='txtRight'>{$current_month_amount}</td>
                {$cum_amount}
            </tr>
            ";

            $claimSeq      = $rowClaimItems['claim_seq'];
            $claim_date    = $rowClaimItems['date'];
            $project_title = $rowClaimItems['project_title'];
        }

        $expNoEdit = array("isEditable" => 0);

        $header = "
        <tr style='background-color:#EAEAE8;'>
            <th>Title</th>
            <th>Description</th>
            <th class='txtRight'>Contract Amount</th>
            <th class='txtCenter'>Status</th>
            <th class='txtCenter'>Prev Amount</th>
            <th class='txtRight'>This Month Amount</th>
            <th class='txtRight'>Cum Amount</th>
        </tr>
        ";

        $formAction = "index.php?widget=enggCrm_projectClaim&_spAction=editMultipleClaimPaymentItemFormSubmit&showHTML=0";

        $text = "
        <form id='editMultipleClaimPaymentItemForm' class='yform editMultipleClaimPaymentItemForm' method='post' action='{$formAction}'>
            {$formObj->getTBRow('', "error_box1", '', $expEdit)}
            <div class='float_box'>
              <div class='float_left col-md-3 noPadding'>
                {$formObj->getDateRow('Date', 'claim_date', $claim_date)}
              </div>
              <div class='float_left MainFields col-md-5 noPadding'>
                {$formObj->getTBRow('Project', 'project_title', $project_title, $expNoEdit)}
              </div>
              <div class='float_left MainFields col-md-3 noPadding'>
                {$formObj->getTBRow('Claim Sequence', 'claim_seq', $claimSeq)}
              </div>
            </div>
            <table class='thinlist' id='claimItemTable'>
                {$header}
                {$rows}
            </table>
            <input type='hidden' name='project_id' value='{$project_id}' />
            <input type='hidden' name='project_claim_id' value='{$project_claim_id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getPrintClaimPdf() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        ini_set('memory_limit', '512M');
        set_time_limit(50000);

        include_once(CP_LIBRARY_PATH.'lib_php/tcpdf/tcpdf.php');
        include_once(CP_LOCAL_PATH.'lib/headfootPrintClaim.php');

        $pdf = new MYPDF_Local(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('USS');
        $pdf->SetSubject('Print Link');
        $pdf->SetTitle('Print Link');
        //$pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 04', PDF_HEADER_STRING);
        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(8, 12, 8);
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
        $pdf->AddPage("L");

        $project_claim_id = $fn->getReqParam('project_claim_id');
        $project_id       = $fn->getReqParam('project_id');
        $claim_seq        = $fn->getReqParam('claim_seq');

        $SQL = "
        SELECT cp.*
               ,pc.claim_date
               ,pc.project_title
               ,ct.amount AS claimAmount
               ,ct.title
               ,ct.description
               ,ct.remarks
               ,p.project_code
        FROM claim_line_items ct
        LEFT JOIN claim_payment cp ON (ct.claim_line_items_id = cp.claim_line_items_id)
        LEFT JOIN project_claim pc ON (pc.project_claim_id = cp.project_claim_id)
        LEFT JOIN project p ON (p.project_id = cp.project_id)
        WHERE ct.project_claim_id = '{$project_claim_id}'
          AND ct.project_id = '{$project_id}'
          AND cp.claim_seq  = '{$claim_seq}'
        ORDER BY ct.claim_line_items_id ASC
        ";
        $result  = $db->sql_query($SQL);
        $result2 = $db->sql_query($SQL);
        $company = $db->sql_fetchrow($result2);

        $claim_date = $fn->getCPDate($company['claim_date'], 'd-m-Y');

        $tbl1 = '
        <table border="0" width="100%" cellpadding="4" style="font-size:10px;font-weight:bold;">
            <tr>
                <td style="border-top: 1px solid #0e502a;"><br/><br/>PROJECT: '.$company['project_title'].'</td>
                <td style="border-top: 1px solid #0e502a;" align="right"><br/><br/>Date: '.$claim_date.'</td>
            </tr>
            <tr>
                <td>'.strtoupper($claim_seq).'</td>
            </tr>
        </table>
        ';

        $tbl2 = '
        <table border="1" width="100%" cellpadding="4">
            <tr style="font-size:10px;font-weight:bold;">
                <td width="5%"  rowspan="2" align="center" style="vertical-align:middle;"><br/><br/>Item</td>
                <td width="36%" rowspan="2" align="center" style="vertical-align:middle;"><br/><br/>Description of work</td>
                <td width="10%" align="center">CONTRACT AMOUNT</td>
                <td width="12%" align="center" colspan="2">PREVIOUS CLAIM AMOUNT</td>
                <td width="11%" align="center" colspan="2">THIS MONTH AMOUNT</td>
                <td width="12%" align="center" colspan="2">CUMULATIVE CLAIM</td>
                <td width="14%" align="center">REMARK</td>
            </tr>
            <tr style="font-size:10px;font-weight:bold;">
              <td align="center" width="10%">S$</td>
              <td align="center" width="3%">%</td>
              <td align="center" width="9%">S$</td>
              <td align="center" width="3%">%</td>
              <td align="center" width="8%">S$</td>
              <td align="center" width="3%">%</td>
              <td align="center" width="9%">S$</td>
              <td></td>
            </tr>
        ';

        $count = 1;
        $totalContractAmount        = 0;
        $totalPrevClaimAmount       = 0;
        $totalThisMonthClaimAmount  = 0;
        $totalCumulativeClaimAmount = 0;
        while ($row = $db->sql_fetchrow($result)) {
            if ($row['title']) {
                $tbl2 = $tbl2.'<tr style="font-size:10px;font-weight:bold;">
                                    <td></td>
                                    <td bgcolor="#f2eb55">'.strtoupper($row['title']).'</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                        ';
            }

            $SQLPrevClaim = "
            SELECT SUM(amount) AS amount
            FROM claim_payment
            WHERE claim_line_items_id = '{$row['claim_line_items_id']}'
              AND claim_payment_id < '{$row['claim_payment_id']}'
              AND project_id = '{$row['project_id']}'
            ORDER BY claim_payment_id DESC
            ";
            $resultPrevClaim = $db->sql_query($SQLPrevClaim);
            $rowPrevClaim    = $db->sql_fetchrow($resultPrevClaim);

            $cumulativeAmount = $rowPrevClaim['amount'] + $row['amount'];

            if($row['claimAmount'] > 0) {
                $totalContractAmount += $row['claimAmount'];
                $contractAmount = '<td align="right">'.number_format($row['claimAmount'], 2).'</td>';
            } else {
                $contractAmount = '<td align="center">-</td>';
            }

            if($rowPrevClaim['amount'] > 0) {
                $totalPrevClaimAmount += $rowPrevClaim['amount'];
                $prevClaimAmount = '<td align="right">'.number_format($rowPrevClaim['amount'], 2).'</td>';
            } else {
                $prevClaimAmount = '<td align="center">-</td>';
            }

            if($row['amount'] > 0) {
                $totalThisMonthClaimAmount += $row['amount'];
                $thisMonthClaimAmount = '<td align="right">'.number_format($row['amount'], 2).'</td>';
            } else {
                $thisMonthClaimAmount = '<td align="center">-</td>';
            }

            if($cumulativeAmount > 0) {
                $totalCumulativeClaimAmount += $cumulativeAmount;
                $cumulativeClaimAmount = '<td align="right">'.number_format($cumulativeAmount, 2).'</td>';
            } else {
                $cumulativeClaimAmount = '<td align="center">-</td>';
            }

            $tbl2 = $tbl2.'<tr style="font-size:10px;">
                            <td align="center" >'.$count.'</td>
                            <td>'.strtoupper($row['description']).'</td>
                            '.$contractAmount.'
                            <td align="center">NA</td>
                            '.$prevClaimAmount.'
                            <td align="center">NA</td>
                            '.$thisMonthClaimAmount.'
                            <td align="center">NA</td>
                            '.$cumulativeClaimAmount.'
                            <td>'.$row['remarks'].'</td>
                        </tr>
                ';

            $count++;
        }

        if($totalContractAmount > 0) {
            $totalContractAmountFormatted = '<td align="right">'.number_format($totalContractAmount, 2).'</td>';
        } else {
            $totalContractAmountFormatted = '<td align="center">-</td>';
        }

        if($totalPrevClaimAmount > 0) {
            $totalPrevClaimAmountFormatted = '<td align="right">'.number_format($totalPrevClaimAmount, 2).'</td>';
        } else {
            $totalPrevClaimAmountFormatted = '<td align="center">-</td>';
        }

        if($totalThisMonthClaimAmount > 0) {
            $totalThisMonthClaimAmountFormatted = '<td align="right">'.number_format($totalThisMonthClaimAmount, 2).'</td>';
        } else {
            $totalThisMonthClaimAmountFormatted = '<td align="center">-</td>';
        }

        if($totalCumulativeClaimAmount > 0) {
            $totalCumulativeClaimAmountFormatted = '<td align="right">'.number_format($totalCumulativeClaimAmount, 2).'</td>';
        } else {
            $totalCumulativeClaimAmountFormatted = '<td align="center">-</td>';
        }

        $tbl2 = $tbl2.'<tr bgcolor="#dcdfe6" style="font-size:10px;font-weight:bold; line-height:24px;">
                          <td colspan="2" align="right">TOTAL COST</td>
                          '.$totalContractAmountFormatted.'
                          <td></td>
                          '.$totalPrevClaimAmountFormatted.'
                          <td></td>
                          '.$totalThisMonthClaimAmountFormatted.'
                          <td></td>
                          '.$totalCumulativeClaimAmountFormatted.'
                          <td></td>
                      </tr>
              ';

        $tbl2 = $tbl2.'</table>';

        $pdf->ln(2);
        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->writeHTML($tbl2, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $company['project_code'] . '-Claim.pdf';
        $pdf->Output($download_title, 'I');
    }

    /**
     *
     */
    function getPrintClaimSummaryPdf() {
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
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

        $project_claim_id = $fn->getReqParam('project_claim_id');
        $project_id       = $fn->getReqParam('project_id');
        $claim_seq        = $fn->getReqParam('claim_seq');

        $SQLClaim = "
        SELECT  p.project_code
               ,pc.po_quote_no
               ,pc.ref_no
               ,pc.amount
               ,pc.description
               ,pc.claim_date
               ,pc.project_title
               ,pc.variation_order_submission
               ,pc.value_of_contract_work_done
               ,pc.vo_claim_work_done
               ,pc.less_previous_retention
               ,c.company_name
               ,CONCAT_WS('. ', cont.salutation, cont.first_name) AS contact_name
        FROM project_claim pc
        LEFT JOIN project p ON (p.project_id = pc.project_id)
        LEFT JOIN company c ON (c.company_id = p.company_id)
        LEFT JOIN contact cont ON (cont.contact_id = p.contact_id)
        WHERE pc.project_claim_id = '{$project_claim_id}'
          AND pc.project_id = '{$project_id}'
        ";
        $resultClaim = $db->sql_query($SQLClaim);
        $rowClaim    = $db->sql_fetchrow($resultClaim);

        $claim_date = $fn->getCPDate($rowClaim['claim_date'], 'd/m/Y');

        $SQLClaimPayment = "
        SELECT * 
        FROM claim_payment
        WHERE project_id = '{$project_id}'
          AND project_claim_id = '{$project_claim_id}'
          AND claim_seq = '{$claim_seq}'
        ORDER BY claim_payment_id DESC
        ";
        $resultClaimPayment = $db->sql_query($SQLClaimPayment);
        $rowClaimPayment    = $db->sql_fetchrow($resultClaimPayment);

        $SQLClaimPaymentTotal = "
        SELECT SUM(amount) AS amount
        FROM claim_payment
        WHERE project_id = '{$project_id}'
          AND project_claim_id = '{$project_claim_id}'
          AND claim_seq = '{$claim_seq}'
        ORDER BY claim_payment_id DESC
        ";
        $resultClaimPaymentTotal = $db->sql_query($SQLClaimPaymentTotal);
        $rowClaimPaymentTotal    = $db->sql_fetchrow($resultClaimPaymentTotal);
        
        $SQLPrevClaim = "
        SELECT *
        FROM claim_payment
        WHERE project_id = '{$project_id}'
          AND project_claim_id = '{$project_claim_id}'
          AND claim_payment_id < '{$rowClaimPayment['claim_payment_id']}'
        GROUP BY claim_seq
        ORDER BY claim_payment_id DESC
        ";
        $resultPrevClaim  = $db->sql_query($SQLPrevClaim);
        $numRowsPrevClaim = $db->sql_numrows($resultPrevClaim);
        $numRowsPrevClaim = $numRowsPrevClaim;

        $letters = range('a', 'z');
        $lessAlphabets = "(";
        for($i = 0; $i < $numRowsPrevClaim - 1; $i++) {
            $lessAlphabets .= $letters[$i].'+';
        }
        $lessAlphabets .= ")";
        $lessAlphabets = str_replace("+)", ")", $lessAlphabets);
        $lessAlphabets = str_replace("()", "", $lessAlphabets);

        $tbl1 = '
        <table border="0" width="100%" cellpadding="4" style="font-size:10px;font-weight:bold;">
            <tr>
                <td width="55%" style="border-top: 1px solid #0e502a;"><br/><br/><u>WORKS PROGRESS CLAIM</u></td>
                <td width="25%" style="border-top: 1px solid #0e502a;" align="right"><br/><br/>Ref No:</td>
                <td width="20%" style="border-top: 1px solid #0e502a;border-bottom: 1px solid #000000;" align="center"><br/><br/>'.$rowClaim['ref_no'].'</td>
            </tr>
        </table>
        ';

        $tbl2 = '
        <table border="0" width="100%" cellpadding="4" style="font-size:10px; line-height:18px;">
            <tr>
                <td width="20%">Our Progress Claim No.</td>
                <td width="55%" style="border-bottom: 1px solid #000000;" align="center">'.strtoupper($claim_seq).'</td>
                <td width="10%" align="right">Date</td>
                <td width="15%" style="border-bottom: 1px solid #000000;" align="center">'.$fn->getCPDate($rowClaimPayment['date'], 'd/m/Y').'</td>
            </tr>
            <tr>
                <td width="20%">Contractor / Claim By</td>
                <td width="80%" style="border-bottom: 1px solid #000000;" align="center">'.strtoupper($cpCfg['cp.companyName']).'</td>
            </tr>
            <tr>
                <td width="20%">Project Name</td>
                <td width="80%" style="border-bottom: 1px solid #000000;" align="center">'.strtoupper($rowClaim['project_title']).'</td>
            </tr>
            <tr>
                <td width="20%">Work Description</td>
                <td width="80%" style="border-bottom: 1px solid #000000;" align="center">'.$rowClaim['description'].'</td>
            </tr>
            <tr>
                <td width="20%">PO No./Quotation No.</td>
                <td width="55%" style="border-bottom: 1px solid #000000;" align="center">'.$rowClaim['po_quote_no'].'</td>
                <td width="10%" align="right">Date</td>
                <td width="15%" style="border-bottom: 1px solid #000000;" align="center">'.$claim_date.'</td>
            </tr>
            <tr>
                <td width="32%">1. Contact Sum</td>
                <td width="2%"  style="border-bottom: 1px solid #000000;font-weight:bold;">$</td>
                <td width="28%" style="border-bottom: 1px solid #000000;font-weight:bold;" align="right">'.number_format($rowClaim['amount'], 2).'</td>
            </tr>
            <tr>
                <td width="32%" style="color:red;">2. Variation Order Submission (Amount)</td>
                <td width="2%"  style="color:red;border-bottom: 1px solid #000000;font-weight:bold;">$</td>
                <td width="28%" style="color:red;border-bottom: 1px solid #000000;font-weight:bold;" align="right">'.number_format($rowClaim['variation_order_submission'], 2).'</td>
            </tr>
            <tr>
                <td width="32%" style="color:red;">3. Overall (Amount)</td>
                <td width="2%"  style="color:red;border-bottom: 1px solid #000000;font-weight:bold;">$</td>
                <td width="28%" style="color:red;border-bottom: 1px solid #000000;font-weight:bold;" align="right">'.number_format($rowClaim['amount'] + $rowClaim['variation_order_submission'], 2).'</td>
            </tr>
            <tr>
                <td width="32%">4. Value of Contract Work Done (Amount)</td>
                <td width="2%"  style="border-bottom: 1px solid #000000;font-weight:bold;">$</td>
                <td width="28%" style="border-bottom: 1px solid #000000;font-weight:bold;" align="right">'.number_format($rowClaim['value_of_contract_work_done'], 2).'</td>
            </tr>
            <tr>
                <td width="32%">5. VO Claim Work Done (Amount)</td>
                <td width="2%"  style="border-bottom: 1px solid #000000;font-weight:bold;">$</td>
                <td width="28%" style="border-bottom: 1px solid #000000;font-weight:bold;" align="right">'.number_format($rowClaim['vo_claim_work_done'], 2).'</td>
            </tr>
            <tr>
                <td width="100%">6. Less Previous Paid '.$lessAlphabets.'<br/></td>
            </tr>
        ';

        $SQLClaimPaymentSeq = "
        SELECT SUM(amount) AS amount
              ,claim_seq 
        FROM claim_payment
        WHERE project_id = '{$project_id}'
          AND project_claim_id = '{$project_claim_id}'
          AND claim_seq != '{$claim_seq}'
          AND claim_payment_id < '{$rowClaimPayment['claim_payment_id']}'
        GROUP BY claim_seq
        ORDER BY claim_seq ASC
        ";
        $resultClaimPaymentSeq = $db->sql_query($SQLClaimPaymentSeq);
      
        $count = 1;
        $countLetters = 0;
        $letters = range('A', 'Z');
        while ($rowClaimPaymentSeq = $db->sql_fetchrow($resultClaimPaymentSeq)) {
            $tbl2 = $tbl2.'<tr>
                              <td width="32%">'.$letters[$countLetters].'. '.$rowClaimPaymentSeq['claim_seq'].'</td>
                              <td width="2%"  style="border-bottom: 1px solid #000000;font-weight:bold;">$</td>
                              <td width="28%" style="border-bottom: 1px solid #000000;font-weight:bold;" align="right">'.number_format($rowClaimPaymentSeq['amount'], 2).'</td>
                              <td width="38%">(Amount)</td>
                          </tr>
                ';

            $count++;
            $countLetters++;
        }

        $thisMonthGSTAmount = ($cpCfg['cp.gstPercentage'] * $rowClaimPaymentTotal['amount']) / 100;

        $lessRetention = '';
       // if($countLetters > 0) {
            $lessRetention = '
            <tr>
                <td width="32%">7.Less Previous Retention:</td>
                <td width="2%"  style="color:red;border-bottom: 1px solid #000000;font-weight:bold;">$</td>
                <td width="28%" style="color:red;border-bottom: 1px solid #000000;font-weight:bold;" align="right">'.number_format($rowClaim['less_previous_retention'], 2).'</td>
            </tr>
            <br/>
            ';
        //}

        $tbl2 = $tbl2.''.$lessRetention.'
                        <tr>
                            <td width="22%">8.This Month Claim</td>
                            <td width="2%"  style="border-bottom: 1px solid #000000;font-weight:bold;">$</td>
                            <td width="28%" style="border-bottom: 1px solid #000000;font-weight:bold;" align="right">'.number_format($rowClaimPaymentTotal['amount'], 2).'</td>
                        </tr>
                        <tr>
                            <td width="22%">Add 7% GST</td>
                            <td width="2%"  style="border-bottom: 1px solid #000000;font-weight:bold;">$</td>
                            <td width="28%" style="border-bottom: 1px solid #000000;font-weight:bold;" align="right">'.number_format($thisMonthGSTAmount, 2).'</td>
                        </tr>
                        <tr>
                            <td width="22%">Total Amount Claimed</td>
                            <td width="2%"  style="border-bottom: 1px solid #000000;font-weight:bold;">$</td>
                            <td width="28%" style="border-bottom: 1px solid #000000;font-weight:bold;" align="right">'.number_format($rowClaimPaymentTotal['amount'] + $thisMonthGSTAmount, 2).'</td>
                        </tr>
                ';

        $tbl2 = $tbl2.'</table>';

        $tbl3 = '<table style="font-size:10px;">
                    <tr>
                      <td style="font-weight:bold;">Claimed By: '.strtoupper($cpCfg['cp.companyName']).'</td>
                      <td></td>
                      <td style="font-weight:bold;">Certified BY: '.strtoupper($rowClaim['company_name']).'</td>
                    </tr>
                    <tr>
                      <td style="line-height:55px;"></td>
                      <td style="line-height:55px;"></td>
                      <td style="line-height:55px;"></td>
                    </tr>
                    <tr>
                      <td style="border-top:1px solid #000000;">Authorised Signature & Co. Stamp<br/><font style="font-weight:bold;">Name: '.$cpCfg['cp.companyContactName'].'</font></td>
                      <td></td>
                      <td style="border-top:1px solid #000000;">Authorised Signature (Project Manager)<br/><font style="font-weight:bold;">Name: '.$rowClaim['contact_name'].'</font></td>
                    </tr>
                </table>
        ';

        $pdf->writeHTML($tbl1, true, false, false, false, '');
        $pdf->ln(-4);
        $pdf->writeHTML($tbl2, true, false, false, false, '');
        $pdf->writeHTML($tbl3, true, false, false, false, '');

        $download_title = $cpCfg['cp.companyName'] . '-' . $rowClaim['project_code'] . '-Claim-Summary.pdf';
        $pdf->Output($download_title, 'I');
    }
}