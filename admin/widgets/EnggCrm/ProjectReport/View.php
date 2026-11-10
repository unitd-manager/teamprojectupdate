<?
class CPL_Admin_Widgets_EnggCrm_ProjectReport_View extends CP_Admin_Widgets_EnggCrm_ProjectReport_View
{
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $dateUtil = Zend_Registry::get('dateUtil');

        $category   = $fn->getReqParam('category');
        $status     = $fn->getReqParam('status');
        $start_date = $dateUtil->formatDate($fn->getReqParam('start_date'), 'DD-MM-YYYY');
        $end_date   = $dateUtil->formatDate($fn->getReqParam('end_date'), 'DD-MM-YYYY');

        if ($start_date == '') {
            $start_date = date('d-m-Y', mktime (0,0,0,date("m")-12, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('d-m-Y');
        }

        if($tv['module'] == 'common_dashboard'){
            $heading = "
            <div id='projectSummaryDisplay' class='inner'>
                {$this->getProjectSummaryDisplay()}
            </div>
            ";
        } else {
            $heading = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='4'>Project Summary</th>
                </thead>
                <tr>
                    <td><b>Category :</b> {$category}</td>
                    <td><b>Status :</b> {$status}</td>
                    <td><b>Project Start Date :</b> {$start_date}</td>
                    <td><b>Project End Date :</b> {$end_date}</td>
                </tr>
            </table>
            <div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Project code</th>
                        <th>Project Title</th>
                        <th>Category</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Client Company</th>
                        <th>Contact</th>
                        <th>Status</th>
                    </tr>
                </thead>
                {$this->getRowsHTML()}
            </table>
            </div>
            ";            
        }
        $text = "
        {$heading}
        ";
        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $count = 1;

        foreach($this->model->dataArray as $row){
        
            $start_date = $dateUtil->formatDate($row['start_date'], 'DD-MM-YYYY');
            $actual_finish_date = $dateUtil->formatDate($row['estimated_finish_date'], 'DD-MM-YYYY');
            //$amount = $row['add_hourly_rate'] * $row['employee_hours'];
            $costing_details = $this->getCostingDetails($row['project_id']);

            $awarded_amount = '';
            if($row['price'] > 0){
                $awarded_amount = number_format($row['price'],2);
                $awarded_amount = '$'.$awarded_amount;
            }
            
            $rows .= "
            <tbody class='projectSummary'>
                <tr>
                    <td>{$count}</td>
                    <td class='projectCode'>{$row['project_code']}</td>
                    <td>{$row['title']} (Awarded Cost : {$awarded_amount})</td>
                    <td>{$row['category']}</td>
                    <td>{$start_date}</td>
                    <td>{$actual_finish_date}</td>
                    <td>{$row['company_name']}</td>
                    <td>{$row['contact_name']}</td>
                    <td>{$row['status']}</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td class='costingDetailsMain' colspan='7'>{$costing_details}</td>
                </tr>
            </tbody>
            ";                

            $count++;
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

    function getProjectSummaryDisplay() {
        $fn       = Zend_Registry::get('fn');
        $db       = Zend_Registry::get('db');
        $dateUtil = Zend_Registry::get('dateUtil');
        $dbUtil   = Zend_Registry::get('dbUtil');
        $cpUtil   = Zend_Registry::get('cpUtil');

        $SQLStatus = $fn->getValueListSQL('projectStatus');
        $sqlCat    = $fn->getValueListSQL('projectCategory');
        $category  = $fn->getReqParam('category');
        $status    = $fn->getReqParam('status');
        $month     = $fn->getReqParam('month');

        $rows = '';
        $count = 1;

        if ($status != '') {
            $sqlStatusAppend = "p.status = '{$status}'";
        } else {
            $sqlStatusAppend = "p.status = 'WIP'";            
        }

        $sqlCatAppend = '';
        if ($category != '') {
            $sqlCatAppend = "AND p.category = '{$category}'";
        }

        $sqlMonthAppend = '';
        if ($month != '') {
            $sqlMonthAppend = "AND DATE_FORMAT(p.start_date, '%m') = '{$month}'";
        }

        $SQLStaff = "
        SELECT e.team, e.employee_id, e.project_manager
        FROM staff s
        LEFT JOIN employee e ON (e.employee_id = s.employee_id)
        WHERE s.staff_id = {$_SESSION['staff_id']}
        ";
        
        $resultStaff  = $db->sql_query($SQLStaff);
        $rowStaff = $db->sql_fetchrow($resultStaff);

        $appendSQL = "";
        if ($_SESSION['userGroupName'] != 'Super Administrator' && $_SESSION['userGroupName'] != 'SATHISH' && $_SESSION['userGroupName'] != 'SHANKAR' && $_SESSION['userGroupName'] != 'Admin and Purchase') {
            if($rowStaff['project_manager'] == 1){
                $appendSQL = "AND p.project_manager_id = '{$rowStaff['employee_id']}'";
            } else {                
                $appendSQL = "AND p.project_id IN (select pe.project_id FROM project_employee pe LEFT JOIN (job_information j) ON (j.employee_id = pe.employee_id) WHERE pe.employee_id = '{$rowStaff['employee_id']}' AND (j.designation = 'Engineer' OR j.designation = 'Assistant Engineer'))";
            }
        }

        $SQL = "
        SELECT p.*
              ,p.title AS Project_name
              ,c.company_id
              ,c.company_name 
              ,o.price
              ,CONCAT_WS(' ', cont.first_name, cont.last_name) AS contact_name
        FROM `project` p
        LEFT JOIN company c ON (c.company_id = p.company_id)
        LEFT JOIN (contact cont) ON (p.contact_id = cont.contact_id)
        LEFT JOIN (opportunity o) ON (p.opportunity_id = o.opportunity_id)
        WHERE p.project_id != ''
       
        ORDER BY p.project_id DESC
        ";
        $result  = $db->sql_query($SQL);

        $total_price_for_summary = 0;
        while ($row = $db->sql_fetchrow($result)) {
            $start_date = $dateUtil->formatDate($row['start_date'], 'DD-MM-YYYY');
            $actual_finish_date = $dateUtil->formatDate($row['estimated_finish_date'], 'DD-MM-YYYY');
            //$amount = $row['add_hourly_rate'] * $row['employee_hours'];
            $costing_details = $this->getCostingDetails($row['project_id']);

            $costingDetailsRow = "";
            if ($_SESSION['userGroupName'] == 'Super Administrator' || $_SESSION['userGroupName'] == 'SATHISH' || $_SESSION['userGroupName'] == 'SHANKAR' || $_SESSION['userGroupName'] == 'Admin and Purchase') {
                
                $costingDetailsRow = "
                <tr>
                    <td></td>
                    <td></td>
                    <td class='costingDetailsMain' colspan='7'>{$costing_details}</td>
                </tr>
                ";
            }

            $awarded_amount = '';
            if($row['price'] > 0){
                $awarded_amount = number_format($row['price'],2);
                $awarded_amount = 'S$ '.$awarded_amount;
            }

            /*SELECT FORMAT(SUM(i.invoice_amount + ((i.invoice_amount * i.gst_percentage) / 100)),2) AS sum_invoice_amount*/
            $SQLInv    = "
            SELECT SUM(i.invoice_amount - i.discount) AS sum_invoice_amount
            FROM invoice i
            LEFT JOIN `order` o ON (o.order_id = i.order_id)
            WHERE o.project_id = {$row['project_id']}
              AND status != 'Cancelled'
            ";
            $resultInv = $db->sql_query($SQLInv);
            $rowInv    = $db->sql_fetchrow($resultInv);

            if ($row['price'] > 0 ) {
                $percentageOfWork = round(($rowInv['sum_invoice_amount'] * 100) / $row['price'], 2);
            } else {
                $percentageOfWork = 0;
            }
             
            $project_id  = $fn->getReqParam('project_id');

            $projectRec = $fn->getRecordRowByID('project', 'project_id', $project_id);

            $SQLProj = "
            SELECT DISTINCT e.employee_id
             ,COUNT(e.first_name) AS title
            FROM employee e
            LEFT JOIN (project_employee pe) ON (pe.employee_id = e.employee_id)
            WHERE pe.project_id = {$row['project_id']}
            ";
            $resultProj = $db->sql_query($SQLProj);
            $rowProj    = $db->sql_fetchrow($resultProj);

            $historyLink   = "index.php?widget=enggCrm_projectReport&_spAction=viewAttendanceLog&project_id={$row['project_id']}&showHTML=0";

            $rows .= "
            <tbody class='projectSummary'>
                <tr>
                    <td>{$count}</td>
                    <td class='projectCode bold'><a href='index.php?_topRm=project&module=enggCrm_project&_action=edit&project_id={$row['project_id']}' target='_blank'>{$row['project_code']}</a></td>
                    <td class='bold'><a href='index.php?_topRm=project&module=enggCrm_project&_action=edit&project_id={$row['project_id']}' target='_blank'>{$row['company_name']}</a></td>
                    <td class='bold'>{$row['title']} (Awarded Cost : {$awarded_amount})</td>
                    <td class='bold'>{$row['category']}</td>
                    <td class='bold'>{$start_date}</td>
                    <td class='bold'>{$actual_finish_date}</td>
                    <td class='bold'>{$row['status']}</td>
                     <!--<td class='bold' align='right'>{$percentageOfWork}%</td>
                    <td class='bold'>{$rowProj['title']}<a href='$historyLink' class='viewAttendanceLog ml10'>View</a></td>-->

                </tr>
                {$costingDetailsRow}
            </tbody>
            ";                

            $count++;
            $total_price_for_summary += $row['price'];
        }

        $arrMonth = array (
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
        
        $total_price_for_summary_formatted = number_format($total_price_for_summary, 2);
        $text = "
        <h2 class='ui-widget-header ui-corner-top'>
            <div class='floatbox invoiceSummaryfilter'>
                <div class='float_left'>
                    Project Summary
                </div>
                <div class='float_right mb5 ml10'>
                    <select name='category' class='ml10 projectCategoryFilter'>
                        <option value=''>Select Category</option>
                        {$dbUtil->getDropDownFromSQLCols1($db, $sqlCat, $category)}
                    </select>
                </div>
                <div class='float_right mb5 ml10'>
                    <select name='month' class='ml10 mr10 projectCategoryFilter'>
                        <option value=''>Select Month</option>
                        {$cpUtil->getDropDownFromArr($arrMonth, $month)}
                    </select>
                </div>
            </div>
        </h2>
        <div class = 'tableOuter scroll-pane'>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th colspan='10' class='totalHighlightInDashboard txtCenter'>Total Amount: S$ {$total_price_for_summary_formatted}</th>
                </tr>
                <tr bgcolor='#90ee90'>
                    <th>S.No</th>
                    <th>Project code</th>
                    <th>Client Company</th>
                    <th>Project Title</th>
                    <th>Category</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                     <!--<th>% of Work</th>
                   <th>Workers</th>-->
                </tr>
            </thead>
            {$rows}
        </table>
        </div>
        ";

        return $text;
    }
   /**
     *
     */
    function getviewAttendanceLog() {
        $db       = Zend_Registry::get('db');
        $fn       = Zend_Registry::get('fn');
        $cpCfg    = Zend_Registry::get('cpCfg');
        $dateUtil = Zend_Registry::get('dateUtil');

        $project_id = $fn->getReqParam('project_id');
        $attendance_id = $fn->getReqParam('attendance_id');
       
        $rows = '';
        $class = '';

        $SQL = "
        SELECT a.*
           ,CONCAT_WS(' ', s.first_name, s.last_name) AS staff_name
        FROM attendance a
        LEFT JOIN (staff s) ON (a.staff_id = s.staff_id) 
        WHERE a.project_id = '{$project_id}'
        ";
        $result  = $db->sql_query($SQL);
        
        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
            <tr> 
                <td>{$row['staff_name']}</td>
                <td>{$row['record_date']}</td>
                <td>{$row['time_in']}</td>
                <td>{$row['leave_time']}</td>
            </tr>
             ";
        }

        $text = "
        <table class='thinlist list mt10'>
             <thead>
                <th>Employee Name</th>
                <th>Date</th>
                <th>Check In</th>
                <th>Check Out</th>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";

        return $text;
    }

    /**
     *
     */
    function getCostingDetails($project_id) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = '';
        $count = 1;

        $SQLOC="
        SELECT SUM(oc.amount) AS other_comp_amount
        FROM other_comp_emp oc
        WHERE project_id = '{$project_id}'
        ";
        $resultOC   = $db->sql_query($SQLOC);
        $rowOC = $db->sql_fetchrow($resultOC);

        $sqlPop = "
        SELECT cs.*
        FROM costing_summary cs
        WHERE cs.project_id = {$project_id}
        ";
        $resultPop = $db->sql_query($sqlPop);
        $rowPop = $db->sql_fetchrow($resultPop);
        $projectedCostFields = array(
            'total_material_price',
            'transport_charges',
            'total_labour_charges',
            'salesman_commission',
            'finance_charges',
            'office_overheads',
            'other_charges',
            'total_cost',
            'profit',
            'profit_percentage'
        );
        if (!is_array($rowPop)) {
            $rowPop = array();
        }
        foreach ($projectedCostFields as $field) {
            $rowPop[$field] = (float) ($rowPop[$field] ?? 0);
        }
        if (!is_array($rowOC)) {
            $rowOC = array();
        }
        $rowOC['other_comp_amount'] = (float) ($rowOC['other_comp_amount'] ?? 0);
        //while ($rowPop = $db->sql_fetchrow($resultPop)) {
        $projected_total_material_price = number_format($rowPop['total_material_price'],2);
        $projected_transport_charges = number_format($rowPop['transport_charges'],2);
        $projected_total_labour_charges = number_format($rowPop['total_labour_charges'],2);
        $projected_salesman_commission = number_format($rowPop['salesman_commission'],2);
        $projected_finance_charges = number_format($rowPop['finance_charges'],2);
        $projected_office_overheads = number_format($rowPop['office_overheads'],2);
        $projected_other_charges = number_format($rowPop['other_charges'],2);
        $projected_total_cost = number_format($rowPop['total_cost'] + $rowOC['other_comp_amount'],2);

        $other_comp_amount = '';
        if($rowOC['other_comp_amount'] > 0) {
            $other_comp_amount = "(+{$rowOC['other_comp_amount']})";
        }

        $rows .= "
        <tr>
            <td bgcolor='#dddddd'>Projected</td>
            <td align='right'>{$projected_total_material_price}</td>
            <td align='right' class='productTitle'>{$projected_transport_charges}</td>
            <td align='right'>{$projected_total_labour_charges} {$other_comp_amount}</td>
            <td align='right'>{$projected_salesman_commission}</td>
            <td align='right'>{$projected_finance_charges}</td>
            <td align='right'>{$projected_office_overheads}</td>
            <td align='right'>{$projected_other_charges}</td>
            <td align='right'>{$projected_total_cost}</td>
        <tr>
        ";
        //}

        $SQL1 = "
        SELECT SUM(po.qty * po.cost_price) AS total_material_price
        FROM po_product po
        LEFT JOIN (purchase_order p) ON (p.purchase_order_id = po.purchase_order_id)
        WHERE p.project_id = {$project_id}
        ";
        $result1  = $db->sql_query($SQL1);
        $row1 = $db->sql_fetchrow($result1);
        if (!is_array($row1)) {
            $row1 = array();
        }
        $row1['total_material_price'] = (float) ($row1['total_material_price'] ?? 0);

        $sql2 = "
        SELECT SUM(cs.amount) AS transport_charges
        FROM actual_costing_summary cs
        WHERE cs.title = 'Transport Charges'
          AND cs.project_id = {$project_id}
        ";
        $result2 = $db->sql_query($sql2);
        $row2 = $db->sql_fetchrow($result2);
        if (!is_array($row2)) {
            $row2 = array();
        }
        $row2['transport_charges'] = (float) ($row2['transport_charges'] ?? 0);

        $sql3 = "
        SELECT SUM(cs.amount) AS salesman_commission
        FROM actual_costing_summary cs
        WHERE cs.title = 'Salesman Commission'
          AND cs.project_id = {$project_id}
        ";
        $result3 = $db->sql_query($sql3);
        $row3 = $db->sql_fetchrow($result3);
        if (!is_array($row3)) {
            $row3 = array();
        }
        $row3['salesman_commission'] = (float) ($row3['salesman_commission'] ?? 0);

        $sql4 = "
        SELECT SUM(cs.amount) AS finance_charges
        FROM actual_costing_summary cs
        WHERE cs.title = 'Finance Charges'
          AND cs.project_id = {$project_id}
        ";
        $result4 = $db->sql_query($sql4);
        $row4 = $db->sql_fetchrow($result4);
        if (!is_array($row4)) {
            $row4 = array();
        }
        $row4['finance_charges'] = (float) ($row4['finance_charges'] ?? 0);

        $sql5 = "
        SELECT SUM(cs.amount) AS office_overheads
        FROM actual_costing_summary cs
        WHERE cs.title = 'Office Overheads'
          AND cs.project_id = {$project_id}
        ";
        $result5 = $db->sql_query($sql5);
        $row5 = $db->sql_fetchrow($result5);
        if (!is_array($row5)) {
            $row5 = array();
        }
        $row5['office_overheads'] = (float) ($row5['office_overheads'] ?? 0);

        $sql6 = "
        SELECT SUM(cs.amount) AS other_charges
        FROM actual_costing_summary cs
        WHERE cs.title = 'Other Charges'
          AND cs.project_id = {$project_id}
        ";
        $result6 = $db->sql_query($sql6);
        $row6 = $db->sql_fetchrow($result6);
        if (!is_array($row6)) {
            $row6 = array();
        }
        $row6['other_charges'] = (float) ($row6['other_charges'] ?? 0);

        $total_cost = $row1['total_material_price'] + $row2['transport_charges'] + $row3['salesman_commission'] + $row4['finance_charges'] + $row5['office_overheads'] + $row6['other_charges'];

        $actualRows = '';
        $colorTM = 'green';
        $colorTC = 'green';
        $colorTLC = 'green';
        $colorSC = 'green';
        $colorFC = 'green';
        $colorOO = 'green';
        $colorOC = 'green';
        $colorTA = 'green';

        $arrowTM = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='25'/>";
        $arrowTC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='25'/>";
        $arrowTLC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='25'/>";
        $arrowSC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='25'/>";
        $arrowFC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='25'/>";
        $arrowOO = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='25'/>";
        $arrowOC = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='25'/>";
        $arrowTA = "<img class='' src='/admin/images/up-arrow.png' alt='Up Arrow' width='25'/>";

        $total_material_price_cal = ($rowPop['total_material_price'] * $cpCfg['projectReportPercentage']) / 100;
        $total_material_price_calc = $rowPop['total_material_price'] - $total_material_price_cal;
        if($rowPop['total_material_price'] < $row1['total_material_price']) {
            $colorTM = 'red';
            $arrowTM = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='25'/>";
        } else if ($row1['total_material_price'] > $total_material_price_calc){
            $colorTM = 'orange';
            $arrowTM = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='25'/>";
        }

        $transport_charges_cal = ($rowPop['transport_charges'] * $cpCfg['projectReportPercentage']) / 100;
        $transport_charges_calc = $rowPop['transport_charges'] - $transport_charges_cal;
        if($rowPop['transport_charges'] < $row2['transport_charges']) {
            $colorTC = 'red';
            $arrowTC = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='25'/>";
        } else if ($row2['transport_charges'] > $transport_charges_calc){
            $colorTC = 'orange';
            $arrowTC = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='25'/>";
        }
        /*if($rowPop['total_labour_charges'] < $row1['total_labour_charges']) {
            $colorTLC = 'red';
        }*/
        $salesman_commission_cal = ($rowPop['salesman_commission'] * $cpCfg['projectReportPercentage']) / 100;
        $salesman_commission_calc = $rowPop['salesman_commission'] - $salesman_commission_cal;
        if($rowPop['salesman_commission'] < $row3['salesman_commission']) {
            $colorSC = 'red';
            $arrowSC = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='25'/>";
        } else if ($row3['salesman_commission'] > $salesman_commission_calc){
            $colorSC = 'orange';
            $arrowSC = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='25'/>";
        }

        $finance_charges_cal = ($rowPop['finance_charges'] * $cpCfg['projectReportPercentage']) / 100;
        $finance_charges_calc = $rowPop['finance_charges'] - $finance_charges_cal;
        if($rowPop['finance_charges'] < $row4['finance_charges']) {
            $colorFC = 'red';
            $arrowFC = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='25'/>";
        } else if ($row4['finance_charges'] > $finance_charges_calc){
            $colorFC = 'orange';
            $arrowFC = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='25'/>";
        }

        $office_overheads_cal = ($rowPop['office_overheads'] * $cpCfg['projectReportPercentage']) / 100;
        $office_overheads_calc = $rowPop['office_overheads'] - $office_overheads_cal;
        if($rowPop['office_overheads'] < $row5['office_overheads']) {
            $colorOO = 'red';
            $arrowOO = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='25'/>";
        } else if ($row5['office_overheads'] > $office_overheads_calc){
            $colorOO = 'orange';
            $arrowOO = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='25'/>";
        }

        $other_charges_cal = ($rowPop['other_charges'] * $cpCfg['projectReportPercentage']) / 100;
        $other_charges_calc = $rowPop['other_charges'] - $other_charges_cal;
        if($rowPop['other_charges'] < $row6['other_charges']) {
            $colorOC = 'red';
            $arrowOC = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='25'/>";
        } else if ($row6['other_charges'] > $other_charges_calc){
            $colorOC = 'orange';
            $arrowOC = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='25'/>";
        }

        $total_cost_cal = ($rowPop['total_cost'] * $cpCfg['projectReportPercentage']) / 100;
        $total_cost_calc = $rowPop['total_cost'] - $total_cost_cal;
        if($rowPop['total_cost'] < $total_cost) {
            $colorTA = 'red';
            $arrowTA = "<img class='' src='/admin/images/down-arrow.png' alt='Down Arrow' width='25'/>";
        } else if ($total_cost > $total_cost_calc){
            $colorTA = 'orange';
            $arrowTA = "<img class='' src='/admin/images/up-arrow-yellow.png' alt='Up Arrow' width='25'/>";
        }

        $total_material_price = number_format($row1['total_material_price'],2);
        $transport_charges = number_format($row2['transport_charges'],2);
        $salesman_commission = number_format($row3['salesman_commission'],2);
        $finance_charges = number_format($row4['finance_charges'],2);
        $office_overheads = number_format($row5['office_overheads'],2);
        $other_charges = number_format($row6['other_charges'],2);
        $total_cost = number_format($total_cost,2);
        //if($total_cost > 0) {
            $actualRows = "
            <tr>
                <td bgcolor='#dddddd'>Actual</td>
                <td align='right' style='color:{$colorTM}'><span class='float_left'>{$arrowTM}</span>{$total_material_price}</td>
                <td align='right' style='color:{$colorTC}' class='productTitle'><span class='float_left'>{$arrowTC}</span>{$transport_charges}</td>
                <td align='right' style='color:{$colorTLC}'></td>
                <td align='right' style='color:{$colorSC}'><span class='float_left'>{$arrowSC}</span>{$salesman_commission}</td>
                <td align='right' style='color:{$colorFC}'><span class='float_left'>{$arrowFC}</span>{$finance_charges}</td>
                <td align='right' style='color:{$colorOO}'><span class='float_left'>{$arrowOO}</span>{$office_overheads}</td>
                <td align='right' style='color:{$colorOC}'><span class='float_left'>{$arrowOC}</span>{$other_charges}</td>
                <td align='right' style='color:{$colorTA}'><span class='float_left'>{$arrowTA}</span>{$total_cost}</td>
            <tr>
            ";
        //}

        $text = "
        <div class='costingDetails mt5'>
            <table class='paymentDetails'>
                <tr bgcolor='#dddddd'>
                    <td bgcolor='#ffffff'><b>Profit :{$rowPop['profit']}({$rowPop['profit_percentage']}%)</b></td>
                    <td>Total Material</td>
                    <td class='productTitle'>Transport Charges</td>
                    <td class='poAmt'>Total Labour Charges</td>
                    <td>Salesman Commission</td>
                    <td>Finance Charges</td>
                    <td>Office Overheads</td>
                    <td>Other Charges</td>
                    <td>TOTAL COST</td>
                </tr>
                {$rows}
                {$actualRows}
            </table>
        </div>
        ";

        return $text;
    }
}
