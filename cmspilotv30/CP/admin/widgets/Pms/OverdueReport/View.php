<?
class CP_Admin_Widgets_Pms_OverdueReport_View extends CP_Common_Lib_WidgetViewAbstract
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
        $month      = $fn->getReqParam('month');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        
        /*
        if (($start_date != '' || $end_date != '') && ($year != '' || $month != '')) {
            return "<div class='txtCenter'><strong>Choose either the Date range or Month and the Year. Both cannot be chosen at the same time.</strong></div>";
        } else if (($year != '' && $month == '') || ($year == '' && $month != '')) {
            return "<div class='txtCenter'><strong>Choose both Month and Year.</strong></div>";
        } else if ($start_date == '' && $end_date == '' && $year == '' && $month == '') {
            return "<div class='txtCenter'><strong>Choose either Date range or Month and Date.</strong></div>";
        }
        */
        
        if ($month == '' || $year == '') {
            return "<div class='txtCenter alertText'><strong>Choose both Month and the Year</strong></div>";
        }
        
        $entered_date = $year . '-' . $month . '-01';
        if ($entered_date > date('Y-m-d')) {
            return "<div class='txtCenter alertText'><strong>Entered date is future date. Choose current or past date.</div>";
        }
        
        if (is_numeric($site_id)) {
            $siteRec = $fn->getRecordRowById('site', 'site_id', $site_id);
            $branch_name = $siteRec['title'];
        } else {
            $branch_name = "All Branches";
        }

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        $prefix_month = '';
        if ($month) {
            switch ($month) {
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
        }

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='3'>Summary</th>
                </thead>
                <tr>
                    <td>Branch : {$branch_name}</td>
                    <td>Month : {$prefix_month}</td>
                    <td>Year : {$year}</td>
                </tr>
            </table>
            <table class='thinlist mt10'>
                <thead>
                    <tr>
					<th>S.No</th>
					<th>Branch</th>
					<th>Parent Name</th>
					<th>Registration No</th>
					<th>Student Name</th>
					<th class='txtRight'>Total Due</th>
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

        foreach ($this->model->dataArray as $row) {
            if ($row['total_amount_payable'] > 0) {
                $rows .= "
                <tr>
                    <td>{$serial_no}</td>
                    <td>{$row['site_title']}</td>
                    <td>{$row['parent_name']}</td>
                    <td>{$row['registration_no']}</td>
                    <td>{$row['first_name']}</td>
                    <td class='txtRight'>{$row['total_amount_payable']}</td>
                </tr>
                ";
                $serial_no++;
            }
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}