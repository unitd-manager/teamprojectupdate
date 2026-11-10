<?
class CP_Admin_Widgets_Pms_NewEnrolmentForCurrentYear_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>List of New Students Enrollment</h2>
		<table class='thinlist list'>
			<thead>
				<tr>
					<th>Student Name</th>
					<th>Parent Name</th>
					<th>Class</th>
					<th>Batch</th>
					<th>Mode of Payment</th>
				</tr>
			</thead>
			<tbody>
				{$this->getRowsHTML()}
			</tbody>
		</table>
        ";
        return $text;
    }

    function getRowsHTML() {
        $rows = '';

        foreach($this->model->dataArray as $row){
            if($row['contact_count'] == 1){
                $rows .= "
    			<tr>
    				<td>{$row['student_name']}</td>
    				<td>{$row['parent_name']}</td>
    				<td>{$row['course_title']}</td>
    				<td>{$row['batch_title']}</td>
    				<td>{$row['mode_of_payment']}</td>
    			</tr>
    			";
		    }
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}