<?
class CP_Admin_Widgets_Tradingsg_EnquiryFollowUp_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Enquiry Follow Up</h2>
			<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Date</th>
						<th>Enquiry Name</th>
						<th>Client Name</th>
						<th>Phone No</th>
						<th>Email</th>
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
                                
			$date = $fn->getCPDate($row['follow_up_date'], "d-m-Y");
            $enqUrl = "index.php?_topRm=order&module=tradingsg_enquiry&_action=edit&record_id={$row['enquiry_id']}";
            //$oppLink   = "index.php?_topRm={$tv['topRm']}&module=project_opportunity&opportunity_id={$row['opportunity_id']}&_action=detail";

            $rows .= "
			<tr>
				<td>{$date}</td>
                <td><a href='{$enqUrl}'>{$row['title']}</td>
				<td>{$row['company_name']}</td>
				<td>{$row['phone']}</td>
				<td>{$row['email']}</td>
			</tr>
			";                
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}