<?
class CP_Admin_Widgets_Tradingsg_LeadFollowUp_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Lead Follow Up</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Date</th>
					<th>Staff Name</th>
					<th>Client Name</th>
					<th>Status</th>
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

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');

        $rows = '';

        //$today  = date('Y-m-d');

        foreach($this->model->dataArray as $row){

			//if($row['contact_date'] > $today || $row['contact_date'] == $today){
				$contactDate = $fn->getCPDate($row['contact_date'],"d-m-Y");

			    $rows .= "
				<tr>
					<td>{$contactDate}</td>
					<td>{$row['staff_name']}</td>
					<td>{$row['company_name']}</td>
					<td>{$row['status']}</td>
				</tr>
				";
			//}

        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}