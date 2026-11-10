<?
class CPL_Admin_Widgets_Payroll_VacationReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $current_date = date('Y-m-d');

        if ($start_date != '' && $end_date == '') {
            $end_date   = $current_date;
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
        } else if ($start_date != '' && $end_date != '') {
        } else if ($start_date == '' && $end_date == ''){
            $start_date = date('Y-m-') . '01';
            $end_date   = $current_date;
        }

        $start_date_formatted = $dateUtil->formatDate($start_date, 'DD MMM YYYY');;
        $end_date_formatted   = $dateUtil->formatDate($end_date, 'DD MMM YYYY');;

        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th colspan='2' class='txtCenter'>Summary</th>
            </thead>
            <tr>
                <td><b>Start Date :</b> {$start_date_formatted}</td>
                <td><b>End Date :</b> {$end_date_formatted}</td>
            </tr>
        </table>
        <div class = 'tableOuter scroll-pane'>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th class='txtCenter'>S.No</th>
                    <th class='txtCenter'>Name</th>
                    <th class='txtCenter'>WP No</th>
                    <th class='txtCenter'>NRIC / Fin No</th>
                    <th class='txtCenter'>Vacation Date</th>
                    <th class='txtCenter'>WP Cancellation</th>
                    <th class='txtCenter'>Till to Date</th>
                </tr>
            </thead>
            {$this->getRowsHTML()}
        </table>
        </div>
        ";
        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $count = 1;

        foreach($this->model->dataArray as $row){
            $termination_date_formatted = '';
            if ($row['citizen'] == 'Citizen' || $row['citizen'] == 'PR') {
                $id_no = $row['nric_no'];
            } else {
                $id_no = $row['fin_no'];
            }

            //Finding whether any job info record available for employee as Current
            $recCount = $fn->getRecordCount('job_information', "employee_id = '{$row['employee_id']}' AND status = 'Current'");
            if ($recCount == 0) {
                $sqlJobInfo = "
                SELECT termination_date FROM job_information
                WHERE employee_id = {$row['employee_id']}
                ";
                $resultJobInfo = $db->sql_query($sqlJobInfo);
                $rowJobInfo = $db->sql_fetchrow($resultJobInfo);

                $termination_date_formatted = $dateUtil->formatDate($rowJobInfo['termination_date'], 'DD MMM YYYY');
            }

            $from_date_formatted = $dateUtil->formatDate($row['from_date'], 'DD MMM YYYY');

            $rows .= "
            <tbody class='employeeSummary'>
                <tr>
                    <td class='txtCenter'>{$count}</td>
                    <td>{$row['employee_name']}</td>
                    <td class='txtCenter'>{$row['work_permit']}</td>
                    <td class='txtCenter'>{$id_no}</td>
                    <td class='txtCenter'>{$from_date_formatted}</td>
                    <td class='txtCenter'>{$termination_date_formatted}</td>
                    <td class='txtCenter'>Ask Client</td>
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
}