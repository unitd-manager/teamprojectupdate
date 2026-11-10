<?
class CP_Admin_Widgets_Pms_TraineeNotLinked_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>List of Students to be linked to Batch</h2>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Trainee</th>
					<th>Course</th>
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
				<td>
                    <a href='index.php?_topRm=main&module=pms_contact&_action=edit&record_id={$row['contact_id']}'>
                        {$row['first_name']}
                    </a>
                </td>
				<td>{$row['course_title']}</td>
			</tr>
			";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}