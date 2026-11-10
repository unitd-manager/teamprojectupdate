<?
class CP_Admin_Widgets_Logistics_ListOfBookingDetails_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>List of Booking Details</h2>
			<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Company Name</th>
						<th>Booking Date</th>
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
        $class = '';
        foreach($this->model->dataArray as $row){                            

            $rows .= "
			<tr class='{$class}'>
				<td>{$row['company_name']}</td>
				<td>{$row['booking_date']}</td>
				<td>{$row['status']}</td>
			</tr>
			";                
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}