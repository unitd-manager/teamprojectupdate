<?
class CPL_Admin_Widgets_EnggCrm_ContractReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /*
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $company_id = $fn->getReqParam('company_id');

        $companyRec = $fn->getRecordRowById('company', 'company_id', $company_id);

        $current_date = date('Y-m-d');
        if ($start_date != '' && $end_date == '') {
            $end_date   = $current_date;
        } else if ($start_date == '' && $end_date != ''){
            $start_date = substr($end_date, 0, 8) . '01';
        } else if ($start_date != '' && $end_date != '') {
        } else {
            $start_date = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
            $end_date = $current_date;
        }

        $start_date = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        $end_date   = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');
        
        $text = "
		<div class='tableOuter scroll-pane'>
			<table class='thinlist mt10'>
				<thead>
					<tr>
                        <th>S.No</th>
                        <th>Contract No</th>
                        <th>Client Name</th>
                        <th>Shop Name</th>
                        <th>Location</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Renewal Due</th>
                        <th>Value</th>
                        <th>Service Due</th>
					</tr>
				</thead>
				<tbody>
					{$this->getRowsHTML()}
				</tbody>
			</table>
		</div>
        ";

        //<th class='balanceAmtLbl'>Balance</th>
        return $text;
    }

    /*
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows                = '';
        $overall_sales       = 0;
        $overall_purchase    = 0;
        $gstAmount           = 0;
        $totAlamount         = 0;
        $totalPurchaseAmount = 0;
        $overall_Discount    = 0;
        $profit              = 0;
        $overall_gst         = 0;
        $overall_profit      = 0;
        $appendSql           = '';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $count = 1;

        foreach ($this->model->dataArray as $row) {
            $current_date = date('Y-m-d');
            $start_date = $fn->getCPDate($row['start_date'], 'd-m-Y');
            $end_date = $fn->getCPDate($row['end_date'], 'd-m-Y');
            $renewal_due = $fn->getCPDate($row['renewal_due'], 'd-m-Y');

            $renewalDate = new DateTime($row['renewal_due']);
            $currentDate = new DateTime();
            $renewalInterval = $renewalDate->diff($currentDate)->days;
    
            $highlightRenewalDue = "";
            if ($renewalDate > $currentDate && $renewalInterval <= 30) {
                $highlightRenewalDue = "style='background-color: yellow; font-weight: bold;'";
            }

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

            $rowStyle = $highlightRow ? "style='background-color: pink;'" : "";

            $rows .= "
            <tr class='purchaseSalesSummary' {$rowStyle}>            
                <td class=''>{$count}</td>
                <td class=''>{$row['ref_no']}</td>
                <td class=''>{$row['company_name']}</td>
                <td class=''>{$row['renewal_shop']}</td>
                <td class=''>{$row['renewal_location']}</td>
                <td>{$start_date}</td>
                <td>{$end_date}</td>
                <td {$highlightRenewalDue}>{$renewal_due}</td>
                <td>{$row['contract_value']}</td>
                <td>{$serviceDueText}</td>
            </tr>
            ";

            $count++;
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}