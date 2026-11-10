<?
class CP_Admin_Widgets_EnterpriseIms_NewEnrolment_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {

        $current_year = date('Y');
        $next_year    = date('Y') + 1;

        $text = "
        <h2>List of Current Programmes (Class)</h2>
        <div class='tableOuter'>
		<table class='thinlist list'>
			<thead>
				<tr>
					<th>Class</th>
					<th>Session</th>
					<th class='txtCenter'>Max Enroll</th>
					<th class='txtCenter'>No of Attendee {$current_year}</th>
					<th class='txtCenter'>No of Attendee {$next_year}</th>
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

    /**
     *
     */
    function getRowsHTML() {
        $rows = '';

        foreach($this->model->dataArray as $row){
            $rows .= "
			<tr>
				<td>{$row['course_title']}</td>
				<td>{$row['batch_title']}</td>
				<td class='txtCenter'>{$row['max_enroll_count']}</td>
				<td class='txtCenter'>{$row['attendee_count_current']}</td>
				<td class='txtCenter'>{$row['attendee_count_next_year']}</td>
			</tr>
			";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}