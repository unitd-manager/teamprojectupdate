<?
class CP_Admin_Widgets_Pms_NewEnrollmentByLevel_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>List of Current Programs</h2>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Course</th>
					<th>Batch</th>
					<th>Venue</th>
					<th>Start Time</th>
					<th>End Time</th>
					<th>Trainer</th>
					<th>No of Attendee</th>
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
            $rows .= "
			<tr>
				<td>{$row['course_title']}</td>
				<td>{$row['batch_title']}</td>
				<td>{$row['venue']}</td>
				<td>{$row['start_time']}</td>
				<td>{$row['end_time']}</td>
				<td>{$row['first_name']}</td>
				<td>{$row['attendee_count']}</td>
			</tr>
			";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}