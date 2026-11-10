<?
class CP_Admin_Widgets_EnggCrm_OpportunityQuotation_View extends CP_Common_Lib_WidgetViewAbstract
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

        $status     = $fn->getReqParam('status');
        //$start_date = $fn->getReqParam('start_date');
        //$end_date   = $fn->getReqParam('end_date');

        /*if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-12, date("d"), date("Y")));
        }
        
        if ($end_date == '') {
            $end_date = date('Y-m-d');
        } 
        if ($start_date != '' && $end_date!= '' && ($end_date < $start_date)) {
            return "<div class='txtCenter'>Start date should not be after End date</div>";
        }


        if ($start_date != '' && $end_date == '') {
            $end_date = date('Y-m-d');
        } else if ($start_date == '' && $end_date != ''){
            $start_date = substr($end_date, 0, 8) . '01';
        } else if ($start_date != '' && $end_date != '') {
        } else {
            $start_date = date('Y-m-d',mktime (0,0,0,date("m")-1,date("d"), date("Y")));
            $end_date   = date('Y-m-d');
        }*/
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $current_date = date('Y-m-d');
        $current_year = date('Y');
        $current_month = date('m');

        $start_date = $current_year . '-' . $current_month . '-' . '01';
        $end_date = $current_year . '-' . $current_month . '-' . '31';
        $searchVar->sqlSearchVar[] = "o.enquiry_date='{$current_date}'";

        $start_date  = $dateUtil->formatDate($start_date, 'DD-MMM-YYYY');
        $end_date    = $dateUtil->formatDate($end_date, 'DD-MMM-YYYY');

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='6'>Opportunity Quotation</th>
                </thead>
                <tr>
                    <td>Status : {$status}</td>
                    <td>Enquiry Start Date : {$start_date}</td>
                    <td>Enquiry End Date : {$end_date}</td>
                </tr>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Opp.code</th>
                        <th>Quote Code</th>
                        <th>Company</th>
                        <th>Amount for quote</th>
                        <th>Status</th>
                    </tr>
                </thead>
                {$rowsHTML}
            </table>
            </div>
            ";
        }

        return $text;
    }
    
    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $counter = 1;
        foreach($this->model->dataArray as $row){

            
        $quote_details = $this->getquoteDetails($row['quote_id']);

            $rows .= "
            <tbody class='opportunityQuotationSummary'>
            <tr>
                <td>{$counter}</td>
                <td>{$row['opportunity_code']}</td>
                <td class='quoteVal'>{$row['quote_code']}</td>
                <td>{$row['company_name']}</td>
                <td>{$row['amount']}</td>
                <td>{$row['status']}</td>
            </tr>
            <tr>
                <td class='quoteDetailsMain' colspan='6'>{$quote_details}</td>
            </tr>
            </tbody>
            ";
            $counter++;
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     * 
     */
    function getquoteDetails($quote_id) {
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = '';
        $count = 1;


        $sqlquote = "
        SELECT qi.*
        FROM quote_items qi
        LEFT JOIN (quote q) ON (q.quote_id = qi.quote_id)
        WHERE qi.quote_id = {$quote_id}
        ";

        $resultquote = $db->sql_query($sqlquote);
        while ($rowquote = $db->sql_fetchrow($resultquote)) {

            $total_amount = number_format($rowquote['quantity'] * $rowquote['amount'], 2);


            $rows .= "
            <tr>
                <td>{$count}</td>
                <td>{$rowquote['description']}</td>
                <td>{$rowquote['quantity']}</td>
                <td>{$rowquote['amount']}</td>
                <td>{$total_amount}</td>
            <tr>
            ";
            $count++;
        }

        $text = "
        <div class='quoteDetails mt5'>
            <table class='paymentDetails'>
            <tr>
                <td>S.No</td>
                <td>Description</td>
                <td>Quantity</td>
                <td>Unit Price</td>
                <td>Amount</td>
            </tr>
            {$rows}
            </table>
        </div>
        ";
        
        return $text;
    }
}