<?
class CP_Admin_Widgets_Pms_GiroFailureReport_View extends CP_Common_Lib_WidgetViewAbstract
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

        $site_id = $fn->getReqParam('site_id');
        $year    = $fn->getReqParam('year');
        $month   = $fn->getReqParam('month');
        
        if(is_numeric($site_id)) {
            $siteRec = $fn->getRecordRowById('site', 'site_id', $site_id);
            $branch_name = $siteRec['title'];
        } else {
            $branch_name = "All Branches";
        }

        $rowsHTML = $this->getRowsHTML();
        $text = '';

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
					<th>DDA</th>
					<th>Parent Name</th>
					<th>Student Name</th>
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
            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['branch_name']}</td>
                <td>{$row['dda']}</td>
                <td>{$row['parent_name']}</td>
                <td>{$row['first_name']}</td>
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