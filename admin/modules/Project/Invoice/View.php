<?
class CPL_Admin_Modules_EnggCrm_Invoice_View extends CP_Admin_Modules_EnggCrm_Invoice_View
{
    /**
     *
     */
    function getQuickSearch() {
        $cpUtil = Zend_Registry::get('cpUtil');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $project_id   = $fn->getReqParam('project_id');
        $company_id   = $fn->getReqParam('company_id');
        $company_name = $fn->getReqParam('company_name');
        $status       = $fn->getReqParam('status');
        $yearMonth    = $fn->getReqParam('yearMonth');
        $paid_to_kk   = $fn->getReqParam('paid_to_kk');

        $SQLComp = "
        SELECT DISTINCT a.company_id
              ,a.company_name 
        FROM company a
        JOIN (project b) ON (a.company_id = b.company_id)
        JOIN (invoice c) ON (b.project_id = c.project_id)
        ORDER BY company_name
        ";

        $SQLStatus = $fn->getValueListSQL('invoiceStatus');

        $SQLMonth = "
        SELECT DISTINCT DATE_FORMAT(start_date, '%Y-%m') AS yearMonthStart
              ,DATE_FORMAT(start_date, '%b %Y') AS monthYear
        FROM project
        WHERE DATE_FORMAT( start_date, '%b %Y') IS NOT NULL
        ORDER BY yearMonthStart DESC
         ";

        $kkArray = array(
            "Yes"
           ,"No"
        );

        $spArray = array(
            "Flagged"
           ,"Not-Flagged"
        );

        $branch = '';
        if ($cpCfg['m.enggCrm.hasMultiBranches'] == 1){
            $branch_id = $fn->getReqParam('branch_id');
            $fnModBranch = getCPModuleObj('project_branch');
            $sqlBranch = $fnModBranch->model->getBranchSQL();
            $branch = "
            <td>
                <select name='branch_id'>
                    <option value=''>Branch</option>
                    {$dbUtil->getDropDownFromSQLCols2($db, $sqlBranch, $branch_id)}
                </select>
            </td>
            ";
        }

        $text = "
        <td>
            <select name='company_id'>
                <option value=''>Client Name</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLComp, $company_id)}
            </select>
        </td>
        {$branch}
        <td>
            <select name='status'>
                <option value=''>Status</option>
                {$dbUtil->getDropDownFromSQLCols1($db, $SQLStatus, $status)}
            </select>
        </td>
        <td>
            <select name='yearMonth'>
                <option value=''>Start Month</option>
                {$dbUtil->getDropDownFromSQLCols2($db, $SQLMonth, $yearMonth)}
            </select>
        </td>
        <td>
            <select name='paid_to_kk'>
                <option value=''>Paid to KK</option>
                {$cpUtil->getDropDown1($kkArray, $paid_to_kk)}
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
    function getReportsMenu1() {
        $pager = Zend_Registry::get('pager');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "";

        if ($tv['action'] == "detail") {
            $record_id      = $fn->getReqParam('record_id');
            $printReportUrl = "index.php?_spAction=printReport&record_id={$record_id}&showHTML=0&roomName={$tv['module']}&report=";
            $printUrl = "index.php?_spAction=printUrl&record_id={$record_id}&showHTML=0&roomName={$tv['module']}&report=";

            $text = "
			<ul class='printOptions'>
            	<li><a href='{$printUrl}invoiceWOQuote' target ='_blank'>Invoice (HK$)</a>
            	<li><a href='{$printUrl}invoiceWOQuote' target ='_blank'>Invoice Address (HK$)</a>
			</ul>
			";
		} else {

            $searchQueryString = $pager->removeQueryString(array("_spAction"));

            $printChartUrl = "{$searchQueryString}&_spAction=charts&chartName=";
            $text = "
            <h2>Reports:</h2>
            <ul class='printOptions'>
                <li><a href='{$printChartUrl}barChartInvoice'>Total Invoices Raised vs. Paid by Month</a>
                <li><a href='{$printChartUrl}barChartInvoice'>Last Invoices Report - Sorted by Client</a>
                <li><a href='{$printChartUrl}barChartInvoice'>Last Invoices Report - Sorted by Age</a>
                <li><a href='{$printChartUrl}barChartInvoice'>All Outstanding Invoices Report - Sorted by Client</a>
                <li><a href='{$printChartUrl}barChartInvoice'>All Outstanding Invoices Report - Sorted by Age</a>
			</ul>
            ";
        }
        return $text;
    }

}