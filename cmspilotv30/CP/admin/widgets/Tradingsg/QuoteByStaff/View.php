<?
class CP_Admin_Widgets_Tradingsg_QuoteByStaff_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Quote By Staff</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>S.No</th>
					<th>Date</th>
					<th>Staff</th>
					<th>Client</th>
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
		$count = 1;
		
        foreach($this->model->dataArray as $row){
			if($row['quote_date']){
				$follow_up_date = $fn->getCPDate($row['quote_date'],"d-m-Y");
			
			    $rows .= "
				<tr>
					<td>{$count}</td>
					<td>{$follow_up_date}</td>
					<td>{$row['staff_name']}</td>
					<td>{$row['company_name']}</td>
					<td>{$row['status']}</td>
				</tr>
				";                
			}	
				$count++;
                               
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}