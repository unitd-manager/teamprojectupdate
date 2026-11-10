<?
class CP_Admin_Widgets_Tradingsg_EnquiryActivityByStaff_View extends CP_Common_Lib_WidgetViewAbstract
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
        <h2>Enquiry Activity By Staff</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>S.No</th>
					<th>Staff</th>
					<th>Enquiry Title</th>
					<th>Date</th>
					<th>Activity</th>
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

        $today  = date('Y-m-d');
	
        foreach($this->model->dataArray as $row){

			// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\

			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
			    $siteRec = $fn->getRecordRowById('site', 'site_id', $row['site_id']);

				$siteTitle = "
				<td>{$siteRec['title']}</td>
				";
			}
	
			$comment_date = $fn->getCPDate($row['comment_date'],"d-m-Y");

	
		    $rows .= "
			<tr>
				<td>{$count}</td>
				<td>{$row['staff_name']}</td>
				<td>{$row['title']}</td>
				<td>{$comment_date}</td>
				<td>{$row['comments']}</td>
				{$siteTitle}
			</tr>
			";                
            $count++;                
       }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}