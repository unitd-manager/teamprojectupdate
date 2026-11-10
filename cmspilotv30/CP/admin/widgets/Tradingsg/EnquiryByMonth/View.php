<?
class CP_Admin_Widgets_Tradingsg_EnquiryByMonth_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

	// **** THIS CONDITION HAS BEEN USED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS & VMSJ **** \\
		$siteLocation = '' ;
		if($cpCfg['cp.hasMultiUniqueSites']){
			$siteLocation = "
			<th>Location</th>
			";
		}

        $text = "
        <h2>Enquiry By Month</h2>
		<div class = 'tableOuter scroll-pane'>
		<table class='thinlist'>
			<thead>
				<tr>
					<th>S.No</th>
					<th>Title</th>
					<th>Client</th>
					<th>Status</th>
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

        $rows  = '';
        $count = 1;
		$siteTitle = '' ;

        foreach($this->model->dataArray as $row){

			// **** THIS CONDITION HAS BEEN ADDED ONLY FOR MULTI LOCATION SITE IN BLOSSOMS **** \\

			if($cpCfg['cp.hasMultiUniqueSites'] == 1){
			    $siteRec = $fn->getRecordRowById('site', 'site_id', $row['site_id']);

				$siteTitle = "
				<td>{$siteRec['title']}</td>
				";
			}

                              
            $rows .= "
			<tr>
				<td>{$count}</td>
				<td>{$row['title']}</td>
				<td>{$row['company_name']}</td>
				<td>{$row['status']}</td>
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