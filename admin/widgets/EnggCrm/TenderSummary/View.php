<?
class CPL_Admin_Widgets_EnggCrm_TenderSummary_View extends CP_Common_Lib_WidgetViewAbstract
{
    /*
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        
        $text = "
        <h2>Tender Summary</h2>
		<div class='tableOuter scroll-pane'>
    		<table class='thinlist list mt10'>
    			<thead>
    				<tr>
    					<th>Quot Ref No</th>
                        <th>Desc</th>
                        <th>Main Con</th>
                        <th>Contact</th>
                        <th>Submission Date</th>
                        <th class='txtRight'>Bid Amount</th>
                        <th class='txtRight'>Bid Amount R</th>
                        <th class='txtRight'>Bid Amount R1</th>
                        <th>Submitted</th>
                        <th>Awarded</th>
                        <th>Price Submitted</th>
                        <th>Remarks</th>
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

    /*
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
       
        $rows               = '';
        $overall_purchase   = 0;
        $overall_before_gst = 0;
        $overall_after_gst  = 0;

        foreach ($this->model->dataArray as $row) {
            $status = '';
            $date = '';
            $awarding_status = '';
            $title = substr($row['title'], 0, 25);
            $quoteRec = $fn->getRecordByCondition('quote', "opportunity_id = {$row['opportunity_id']}", 'opportunity_id ASC');
            if($quoteRec['quote_status'] == 'Submitted'){
                $status = 'Yes';
            }

            if($quoteRec['project_id'] > 0){
                $awarding_status = 'Yes';
            }
            
            if($row['actual_submission_date']){
                $date =$fn->getCPDate($row['actual_submission_date'], 'd-m-Y');
            }

		    $rows .= "
			<tr>
                <td>{$quoteRec['quote_code']}</td>
                <td>{$title}</td>
                <td>{$row['company_name']}</td>
                <td>{$row['first_name']}</td>
                <td>{$date}</td>
                <td>{$quoteRec['total_amount']}</td>
                <td></td>
                <td></td>
                <td>{$status}</td>
                <td>{$awarding_status}</td>
                <td>{$quoteRec['created_by']}</td>
                <td></td>
			</tr>
			";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

}