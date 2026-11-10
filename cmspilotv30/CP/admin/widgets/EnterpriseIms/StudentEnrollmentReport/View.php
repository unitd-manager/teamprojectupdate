<?
class CP_Admin_Widgets_EnterpriseIms_StudentEnrollmentReport_View extends CP_Common_Lib_WidgetViewAbstract
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

        $payment_mode = $fn->getReqParam('payment_mode');
        $new_student  = $fn->getReqParam('new_student');
        $site_id      = $fn->getReqParam('site_id');
        
        if(is_numeric($site_id)) {
            $siteRec = $fn->getRecordRowById('site', 'site_id', $site_id);
            $branch_name = $siteRec['title'];
        } else {
            $branch_name = "All Branches";
        }

        $student_type = '';
        if ($new_student != '') {
            $student_type = ($new_student == 1) ? "New Student" : "Old Student";
        }

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
                    <td>Mode of Payment : {$payment_mode}</td>
                    <td>Type of Student : {$student_type}</td>
                    <td>Total no of Students : {$this->model->getSqlForCount($new_student)}</td>
                </tr>
            </table>
            <table class='thinlist mt10'>
                <thead>
                    <tr>
					<th>S No</th>
					<th>Branch</th>
					<th>Name of Student</th>
					<th>Parent Name</th>
					<th>Class</th>
					<th>Session</th>
					<th>Mode of Payment</th>
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
        
        $new_student = $fn->getReqParam('new_student');
        
        $rows = '';
        $serial_no = 0;

        foreach($this->model->dataArray as $row){           
            if ($new_student == 1) {
                if ($row['contact_count'] == 1) {
                    $serial_no += 1;
                    $rows .= $this->getRows($row, $serial_no);
		        }
		    } else if ($new_student == 0 && $new_student != '') {
                if ($row['contact_count'] > 1) {
                    $serial_no += 1;
                    $rows .= $this->getRows($row, $serial_no);
		        }
		    } else {
                $serial_no += 1;
                $rows .= $this->getRows($row, $serial_no);
		    }
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getRows($row, $serial_no) {
        return $rows = "
	    <tr>
            <td>{$serial_no}</td>
	    	<td>{$row['branch_title']}</td>
	    	<td>{$row['student_name']}</td>
	    	<td>{$row['parent_name']}</td>
	    	<td>{$row['course_title']}</td>
	    	<td>{$row['batch_title']}</td>
	    	<td>{$row['mode_of_payment']}</td>
	    </tr>
	    ";
    }
}