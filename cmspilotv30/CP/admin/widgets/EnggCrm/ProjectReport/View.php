<?
class CP_Admin_Widgets_EnggCrm_ProjectReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
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

        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th colspan='4'>Summary</th>
            </thead>
            <tr>
                <td>Category : {$category}</td>
                <td>Status : {$status}</td>
                <td>Project Start Date : {$start_date}</td>
                <td>Project End Date : {$end_date}</td>
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
            <tbody>
                {$this->getRowsHTML()}
            </tbody>
        </table>
        </div>
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
            
            $rows .= "
            <tr>
                <td>{$count}</td>
                <td>{$row['project_code']}</td>
                <td>{$row['title']}</td>
                <td>{$row['category']}</td>
                <td>{$start_date}</td>
                <td>{$actual_finish_date}</td>
                <td>{$row['company_name']}</td>
                <td>{$row['contact_name']}</td>
                <td>{$row['status']}</td>
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