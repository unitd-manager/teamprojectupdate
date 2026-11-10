<?
class CP_Admin_Widgets_Project_DetailTaskSummaryReport_View extends CP_Common_Lib_WidgetViewAbstract
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
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $current_year = date('Y');
        $current_month = date('m');

        if ($start_date == '') {
            $start_date = $current_year . '-' . $current_month . '-' . '01';
            //$start_date = date('Y-m-d', mktime (0,0,0,date("m")-12, date("d"), date("Y")));
        }

        if ($end_date == '') {
            $end_date = date('Y-m-d');
        }

        $start_date  = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        $end_date    = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='6'>Summary</th>
                </thead>
                <tr>
                    <!--<td>Status : {$status}</td>-->
                    <td>Enquiry Start Date : {$start_date}</td>
                    <td>Enquiry End Date : {$end_date}</td>
                </tr>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>Task History Title</th>
                        <th>Staff Name</th>
                        <th>Task Description</th>
                        <th>Task Hrs Date</th>
                        <th>Timesheet Description</th>
                        <th>Status</th>
                        <th>Total Hours</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
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
           $start_date = $fn->getCPDate($row['Start_date'],"d-m-Y");
           $entry_date = $fn->getCPDate($row['entry_date'],"d-m-Y");
           $company = $row['Company_name'];
           $project = $row['Project_name'];
           $tasktitle = $row['Task_title'];
           $taskhistitle = $row['Task_history_title'];
           $desc = $row['Description'];
           $status = $row['Status'];
           $hrs = $row['Hours'];
           $name = $row['staff_name'];
           $time_desc = $row['timesheet_description'];

            $rows .= "
            <tr>
                <td>{$project}</td>
                <td>{$taskhistitle}</td>
                <td>{$name}</td>
                <td>{$desc}</td>
                <td>{$entry_date}</td>
                <td>{$time_desc}</td>
                <td>{$status}</td>
                <td>{$hrs}</td>

            </tr>
            ";
            $counter++;
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}