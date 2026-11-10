<?
class CP_Admin_Widgets_Pms_EnrollmentBySummaryReport_View extends CP_Common_Lib_WidgetViewAbstract
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

        $site_id   = $fn->getReqParam('site_id');
        $course_id = $fn->getReqParam('course_id');
        $batch_id  = $fn->getReqParam('batch_id');
        
        if(is_numeric($site_id)) {
            $siteRec = $fn->getRecordRowById('site', 'site_id', $site_id);
            $branch_name = $siteRec['title'];
        } else {
            $branch_name = "All Branches";
        }

        $course_name = '';
        if ($course_id) {
            $courseRec = $fn->getRecordRowById('course', 'course_id', $course_id);
            $course_name = $courseRec['title'];
        }

        $batch_name = '';
        if ($batch_id) {
            $batchRec = $fn->getRecordRowById('batch', 'batch_id', $batch_id);
            $batch_name = $batchRec['title'];
        }

        $summaryRec = $this->model->getSqlForCount();

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='4'>Summary</th>
                </thead>
                <tr>
                    <td>Branch : {$branch_name}</td>
                    <td>Class : {$course_name}</td>
                    <td>Session : {$batch_name}</td>
                    <td class='txtRight'>Total no of Data : {$summaryRec}</td>
                </tr>
            </table>
            <table class='thinlist mt10'>
                <thead>
                    <tr>
    					<th>S.No</th>
    					<th>Branch</th>
    					<th>Name of Student</th>
    					<th>Reg No.</th>
    					<th>NRIC No.</th>
    					<th>Gender</th>
    					<th>Year of Joining</th>
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

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        
        $rows = '';
        $serial_no = 1;

        foreach($this->model->dataArray as $row){
            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['branch_name']}</td>
                <td>{$row['student_name']}</td>
                <td>{$row['registration_no']}</td>
                <td>{$row['id_card_no']}</td>
                <td>{$row['gender']}</td>
                <td>{$row['year_of_joining']}</td>
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