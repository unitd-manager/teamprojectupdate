<?
class CP_Admin_Widgets_Pms_GiroFailureByMonthReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $site_id    = $fn->getReqParam('site_id');
        $year       = $fn->getReqParam('year');
        $from_month = $fn->getReqParam('from_month');
        $to_month   = $fn->getReqParam('to_month');

        if(is_numeric($site_id)) {
            $siteRec = $fn->getRecordRowById('site', 'site_id', $site_id);
            $branch_name = $siteRec['title'];
        } else {
            $branch_name = "All Branches";
        }

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        switch ($from_month) {
            case 1: $prefix_month = 'January';
            break;
            case 2: $prefix_month = 'February';
            break;
            case 3: $prefix_month = 'March';
            break;
            case 4: $prefix_month = 'April';
            break;
            case 5: $prefix_month = 'May';
            break;
            case 6: $prefix_month = 'June';
            break;
            case 7: $prefix_month = 'July';
            break;
            case 8: $prefix_month = 'August';
            break;
            case 9: $prefix_month = 'September';
            break;
            case 10: $prefix_month = 'October';
            break;
            case 11: $prefix_month = 'November';
            break;
            case 12: $prefix_month = 'December';
            break;
        }

        switch ($to_month) {
            case 1: $prefix_month_to = 'January';
            break;
            case 2: $prefix_month_to = 'February';
            break;
            case 3: $prefix_month_to = 'March';
            break;
            case 4: $prefix_month_to = 'April';
            break;
            case 5: $prefix_month_to = 'May';
            break;
            case 6: $prefix_month_to = 'June';
            break;
            case 7: $prefix_month_to = 'July';
            break;
            case 8: $prefix_month_to = 'August';
            break;
            case 9: $prefix_month_to = 'September';
            break;
            case 10: $prefix_month_to = 'October';
            break;
            case 11: $prefix_month_to = 'November';
            break;
            case 12: $prefix_month_to = 'December';
            break;
        }

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='4'>Summary</th>
                </thead>
                <tr>
                    <td>Branch : {$branch_name}</td>
                    <td>From Month : {$prefix_month}</td>
                    <td>To Month : {$prefix_month_to}</td>
                    <td>Year : {$year}</td>
                </tr>
            </table>
            <table class='thinlist mt10'>
                <thead>
                    <tr>
					<th>S.No</th>
					<th>Branch</th>
					<th>DDA</th>
					<th>Parent Name</th>
					<th>Student Name</th>
                    <th>Month</th>
                    <th class='txtRight'>Amount</th>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            ";
        }

        return $text;
    }
    
    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        
        $rows = '';
        $serial_no = 1;

        foreach($this->model->dataArray as $row){
            $prefix_month = $row['invoice_month'];

            switch ($prefix_month) {
                case 1: $prefix_month = 'January';
                break;
                case 2: $prefix_month = 'February';
                break;
                case 3: $prefix_month = 'March';
                break;
                case 4: $prefix_month = 'April';
                break;
                case 5: $prefix_month = 'May';
                break;
                case 6: $prefix_month = 'June';
                break;
                case 7: $prefix_month = 'July';
                break;
                case 8: $prefix_month = 'August';
                break;
                case 9: $prefix_month = 'September';
                break;
                case 10: $prefix_month = 'October';
                break;
                case 11: $prefix_month = 'November';
                break;
                case 12: $prefix_month = 'December';
                break;
            }

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['branch_name']}</td>
                <td>{$row['dda']}</td>
                <td>{$row['parent_name']}</td>
                <td>{$row['first_name']}</td>
                <td>{$prefix_month}</td>
                <td class='txtRight'>{$row['invoice_amount']}</td>
            </tr>
            ";
            $serial_no++;
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}