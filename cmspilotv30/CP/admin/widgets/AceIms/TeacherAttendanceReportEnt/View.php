<?
class CP_Admin_Widgets_AceIms_TeacherAttendanceReportEnt_View extends CP_Common_Lib_WidgetViewAbstract
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
        
        $teacher_id = $fn->getReqParam('teacher_id');
        
        if ($teacher_id) {
            $header = '';
        } else {
            $header = "<th>Teacher Name</th>";
        }
        
        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S/No</th>
                        {$header}
                        <th>Date</th>
                        <th>Time-In</th>
                        <th>Time-Out</th>
                        <th>Teaching hrs</th>
                        <th class='txtRight'>Amount</th>
                    </tr>
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
        $rows = '';
        $fn = Zend_Registry::get('fn');

        $teacher_id = $fn->getReqParam('teacher_id');

        $serial_no = 0;
        $total_amount = 0;

        foreach($this->model->dataArray as $row){
            $serial_no += 1;
            
            if ($teacher_id) {
                $teacher_name = '';
                $total_amount += $row['amount'];
            } else {
                $teacher_name = "<td>{$row['teacher_name']}</td>";
            }

            $date = $fn->getCPDate($row['date'], 'd-m-Y');

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                {$teacher_name}
                <td>{$date}</td>
                <td>{$row['time_in']}</td>
                <td>{$row['time_out']}</td>
                <td></td>
                <td class='txtRight'>{$row['amount']}</td>
            </tr>
            ";
        }
        
        if ($teacher_id) {
            $rows .= "
            <tr>
                <td class='txtRight' colspan='5'><strong>Total</strong></td>
                <td class='txtRight'>{$total_amount}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}