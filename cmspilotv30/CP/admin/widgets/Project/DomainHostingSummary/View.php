<?
class CP_Admin_Widgets_Project_DomainHostingSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $cpUtil = Zend_Registry::get('cpUtil');

        $duration = $fn->getReqParam('duration');

        $durationArray = array(
             '6'  => "Next 6 Months"
            ,'9'  => "Next 9 Months"
            ,'12' => "Next 12 Months"
            ,'p3' => "Past 3 Months"
        );

        $text = "
        <div id='' class='inner'>
        <h2 class='floatbox ui-widget-header ui-corner-top' class='floatbox'>
            <div class='float_left m10'>
                Renewal Summary
            </div>
        	
            <div class='float_right  mt5 mb5'>
                <td class='fieldValue'>
                    <select name='duration'>
                        <option value='3'>Next 3 Months</option>
                    	{$cpUtil->getDropDownFromArr($durationArray, $duration)}
                    </select>
                </td>
            </div>
        </h2>
		<div class = 'tableOuter scroll-pane'>
            {$this->getRenewalListInDashboard()}
		</div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getRenewalListInDashboard() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $duration = $fn->getReqParam('duration');

        $current_date = date('Y-m-d');

        if ($duration == 'p3') {
            $current_date = date("Y-m-d", strtotime("-3 month"));
            $end_date = date('Y-m-d');
        } else if ($duration) {
	        $end_date = date("Y-m-d", strtotime("+{$duration} month"));
        } else {
	        $end_date = date("Y-m-d", strtotime("+3 month"));
        }

        $sql = "
        SELECT r.*       	  	
      	      ,c.company_name
              ,c.status AS company_status
        FROM renewals r
        LEFT JOIN company c ON (r.company_id = c.company_id)
        WHERE c.status = 'Active'
          AND r.renewal_id != ''
          AND r.end_date BETWEEN '{$current_date}' AND '{$end_date}'
        ORDER BY r.end_date ASC, c.company_name ASC
        ";
        $result = $db->sql_query($sql);
        $rows = '';
        $count = 1;
        while ($row = $db->sql_fetchrow($result)) {
            /* Check whether new renewal data is created */
            $sqlRenewalCheck = "
            SELECT renewal_id FROM renewals
            WHERE start_date > '{$row['end_date']}'
              AND company_id = {$row['company_id']}
            ";
            $resultRenewalCheck = $db->sql_query($sqlRenewalCheck);
            $numRowsRenewalCheck = $db->sql_numrows($resultRenewalCheck);

            $renewed = "No";
            $background_color = "#A6A6A6";
            if ($numRowsRenewalCheck > 0) {
                $renewed = "Yes";
                $background_color = "#fff";
            } else if ($row['company_status'] == 'Old') {
                $background_color = "#fff";
            }

            $company_url = "/admin/index.php?_topRm=opportunity&module=project_company&record_id=" . $row['company_id'] . "&_action=edit";

            $rows .= "
			<tr style='background-color: {$background_color}'>
				<td>{$count}</td>
				<td><a href='{$company_url}' style='color:#000;'><u>{$row['company_name']}</u></a></td>
                <td>{$row['company_status']}</td>
				<td>{$row['renewal_type']}</td>
				<td>{$row['currency']} {$row['amount']}</td>
				<td>{$dateUtil->formatDate($row['end_date'], 'DD-MM-YYYY')}</td>
                <td class='txtCenter'>{$renewed}</td>
			</tr>
			";
			$count++;
        }

        $text = "
        <table class ='thinlist'>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Company Name</th>
                    <th>Company Status</th>
                    <th>Renewal Type</th>
                    <th>Amount</th>
                    <th>End Date</th>
                    <th>Renewed</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>
        ";
        return $text;
    }
}