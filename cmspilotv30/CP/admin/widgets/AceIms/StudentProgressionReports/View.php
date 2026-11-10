<?
class CP_Admin_Widgets_AceIms_StudentProgressionReports_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Student Name</th>
                        <th>Reg No</th>
                        <th>NRIC NO</th>
                        <th>Course Title</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Subject</th>
                        <th>Marks</th>
                        <th>Grade</th>
                        <th>Exam Type</th>
                        <th>Exam Date</th>
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
        $fn = Zend_Registry::get('fn');
        $rows = '';
        $serial_no = 0;

        foreach($this->model->dataArray as $row){
            $serial_no += 1;
            $batchRec = $fn->getRecordRowByID('batch', 'batch_id', 
            $row['batch_id']);
            $subjectRec = $fn->getRecordRowByID('subject', 'subject_id', 
            $batchRec['subject_id']);

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['trainee_name']}</td>
                <td>{$row['registration_no']}</td>
                <td>{$row['id_card_no']}</td>
                <td>{$row['course_title']}</td>
                <td>{$fn->getCPDate($row['valid_date_from'], 'd-M-Y')}</td>
                <td>{$fn->getCPDate($row['valid_date_to'], 'd-M-Y')}</td>
                <td>{$subjectRec['title']}</td>
                <td>{$row['marks']}</td>
                <td>{$row['grade']}</td>
                <td>{$row['exam_type']}</td>
                <td>{$fn->getCPDate($row['exam_date'], 'd-M-Y')}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}