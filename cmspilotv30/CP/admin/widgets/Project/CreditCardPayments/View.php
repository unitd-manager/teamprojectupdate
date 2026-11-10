<?
class CP_Admin_Widgets_Project_CreditCardPayments_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Credit Card Payments</h2>
			<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Card</th>
						<th>Amount</th>
						<th>Due Date</th>
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
            $expCard = array('displayText' => $row['type_of_card']);
            $typeOfCard = $fn->getRecordDetailLink('project_creditCardPayments', 'record_id',
                                $row['credit_card_payment_id'], $expCard);
                                
            if ($row['status'] == 'Due') {
                $class = 'paymentDue';
            } else {
                $class = '';
            }

            $rows .= "
			<tr class='{$class}'>
				<td>{$typeOfCard}</td>
				<td>{$row['amount']}</td>
				<td>{$row['due_date']}</td>
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