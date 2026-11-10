<?
class CP_Admin_Widgets_Tradingsg_TodaysActivity_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $text = "
        <h2>Today's Activity</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>Module</th>
					<th>Code</th>
					<th>Created/Modified</th>
					<th>Staff Name</th>
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
				//$quote_date = $fn->getCPDate($row['quote_date'],"d-m-Y");
        	if($row['MODULE']=='QUOTE'){
        		$todaylink = "index.php?_topRm=order&module=tradingin_quote&record_id={$row['id']}&_action=edit";
        	}
        	else if ($row['MODULE']=='ORDER - QUOTE' || $row['MODULE']=='ORDER - POS') {
        		$todaylink = "index.php?_topRm=finance&module=tradingin_order&record_id={$row['id']}&_action=edit";
        	}
        	else if ($row['MODULE']=='INVOICE - QUOTE' || $row['MODULE']=='INVOICE - POS'){
        		$todaylink = "index.php?_topRm=finance&module=tradingin_order&record_id={$row['id']}&_action=edit";
        	}
        	else if ($row['MODULE']=='RECEIPT - QUOTE' || $row['MODULE']=='RECEIPT - POS'){
        		$todaylink = "index.php?_topRm=finance&module=tradingin_order&record_id={$row['id']}&_action=edit";
        	}

        	if($row['CREATED_BY']!='' && $row['MODIFIED_BY']!=''){
        		$status = 'Modified';
        		$staffname = $row['MODIFIED_BY'];
        	}
        	else if($row['CREATED_BY']!= '' && $row['MODIFIED_BY']==''){
				$status = 'Created';
				$staffname = $row['CREATED_BY'];
			}
			if($row['CREATED_BY']=='' && $row['MODIFIED_BY']==''){
        		$status = '';
        	}

			    $rows .= "
				<tr>
					<td>{$row['MODULE']}</td>
					<td><a href='$todaylink'><u>{$row['CODE']}</u></a></td>
					<td>{$status}</td>
					<td>{$staffname}</td>
				</tr>
				";

        }

        $text = "
        {$rows}
        ";

        return $text;
    }

}