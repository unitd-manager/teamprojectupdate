<?
class CP_Admin_Widgets_EnggCrm_OpportunityReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        $status     = $fn->getReqParam('status');
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');

        if ($start_date == '') {
            $start_date = date('Y-m-d', mktime (0,0,0,date("m")-12, date("d"), date("Y")));
        }
        
        if ($end_date == '') {
            $end_date = date('Y-m-d');
        } 

        $start_date  = $dateUtil->formatDate($start_date, 'DD-MM-YYYY');
        $end_date    = $dateUtil->formatDate($end_date, 'DD-MM-YYYY');

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='6'>Opportunity Report</th>
                </thead>
                <tr>
                    <td>Status : {$status}</td>
                    <td>Enquiry Start Date : {$start_date}</td>
                    <td>Enquiry End Date : {$end_date}</td>
                </tr>
            </table>
    		<div class = 'tableOuter scroll-pane'>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Opp.code</th>
                        <th>Opp.Title</th>
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Category</th>
                        <th>Enquiry Date</th>
                        <th>Follow up Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            </div>
            ";
        }

        return $text;
    }
    
    /**
     *
     */
    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $counter = 1;
        foreach($this->model->dataArray as $row){
            $commentRec   = $fn->getRecordRowById('comment', 'comment_id', $row['comment_record_id']);
            $comment_date = $dateUtil->formatDate($commentRec['comment_date'], 'DD-MM-YYYY');
            
            $commentsData = '';
            if ($commentRec['comments']) {
                $commentsData = $comment_date . ' -  ' . $commentRec['comments'];
            }

            $rows .= "
            <tr>
                <td>{$counter}</td>
                <td>{$row['opportunity_code']}</td>
                <td>{$row['title']}</td>
                <td>{$row['company_name']}</td>
                <td>{$row['contact_name']}</td>
                <td>{$row['category']}</td>
                <td>{$row['enquiry_date']}</td>
                <td>{$row['follow_up_date']}</td>
                <td>{$row['status']}</td>
            </tr>
            ";
            $counter++;
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}