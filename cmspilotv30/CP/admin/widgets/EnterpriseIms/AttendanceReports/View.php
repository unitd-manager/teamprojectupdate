<?
class CP_Admin_Widgets_EnterpriseIms_AttendanceReports_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S/No</th>
                        <th>Reg No</th>
                        <th>Student Name</th>
                        <th>Course Title</th>
                        <th class=''>Percentage Attended</th>
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

        $serial_no = 0;
        $subject_id = $fn->getReqParam('subject_id');
        foreach($this->model->dataArray as $row){
            $serial_no += 1;
            $subjectRec['title']= '';
            if($row['total_present_days'] == 0){
                $percent = 0;
            }
            else{
                $percent = ($row['total_present_days'] / $row['total_attendance_days']) * 100;
                $percent = number_format($percent,2);
            }
            if($subject_id != ''){
                $subjectRec = $fn->getRecordRowByID('subject', 'subject_id', 
                $subject_id);
            }

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['registration_no']}</td>
                <td>{$row['trainee_name']}</td>
                <td>{$row['course_title']}</td>
                <td class=''>{$percent}%</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}