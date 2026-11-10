<?
class CP_Admin_Widgets_Tradingsg_LeadByStaff_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\
		$siteLocation = '' ;
		if($cpCfg['cp.hasMultiUniqueSites']){
			$siteLocation = "
			<th>Location</th>
			";
		}

        $text = "
        <h2>Lead By Staff</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>S.No</th>
					<th>Date</th>
					<th>Client</th>
					<th>Meeting Notes</th>
					<th>Staff</th>
					{$siteLocation}
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
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
       
        $rows = '';
		$count = 1 ;
		$siteTitle = '' ;
		
        foreach($this->model->dataArray as $row){

			// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\

			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
			    $siteRec = $fn->getRecordRowById('site', 'site_id', $row['site_id']);

				$siteTitle = "
				<td>{$siteRec['title']}</td>
				";
			}

			if($row['contact_date']){
				$contactDate = $fn->getCPDate($row['contact_date'],"d-m-Y");
			
			    $rows .= "
				<tr>
					<td>{$count}</td>
					<td>{$contactDate}</td>
					<td>{$row['company_name']}</td>
					<td>{$row['comments']}</td>			
					<td>{$row['staff_name']}</td>
					{$siteTitle}
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