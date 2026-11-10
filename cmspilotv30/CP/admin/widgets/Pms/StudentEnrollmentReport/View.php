<?
class CP_Admin_Widgets_Pms_StudentEnrollmentReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $year_of_joining    = $fn->getReqParam('year');
        $site_id            = $fn->getReqParam('site_id');
        
        if(is_numeric($site_id)) {
            $siteRec = $fn->getRecordRowById('site', 'site_id', $site_id);
            $branch_name = $siteRec['title'];
        } else {
            $branch_name = "All Branches";
        }

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist summaryTable'>
                <thead>
                    <th colspan='3'>Summary</th>
                </thead>
                <tr>
                    <td>Branch : {$branch_name}</td>
                    <td>Enrollment Year : {$year_of_joining}</td>
                    <td class='txtRight'>Total no of Students : {$this->model->getSqlForCount()}</td>
                </tr>
            </table>
            <table class='thinlist mt10'>
                <thead>
                    <tr>
    					<th>S.No</th>
    					<th>Branch</th>
    					<th>Name of Student</th>
    					<th>Reg No.</th>
    					<th>NRIC No.</th>
    					<th>Gender</th>
                        <th>Parent Name</th>
                        <th>DDA</th>
                        <th class='txtRight'>Amount Paid</th>
    			    </tr>
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            ";
        }

        return $text;
    }
    
    function getRowsHTML() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $dateUtil = Zend_Registry::get('dateUtil');

        $year_of_joining = $fn->getReqParam('year');
        
        $rows = '';
        $serial_no = 1;
        $total_receipt_amount = 0;

        foreach($this->model->dataArray as $row){
            /* Finding course contact id (SQL showing duplicate data, so writing separately */
            $sqlCc = "
            SELECT order_id, creation_date FROM course_contact
             WHERE year_of_enrollment = '{$year_of_joining}' AND contact_id = {$row['contact_id']}
            ";
            $resultCc = $db->sql_query($sqlCc);
            $ccRec = $db->sql_fetchrow($resultCc);

            $registration_date = $dateUtil->formatDate($ccRec['creation_date'], 'YYYY-MM-DD');
            $date = $registration_date . ' 00:00:00';

            $sqlReceipt = "
            SELECT SUM(amount) AS receipt_amount
            FROM receipt
            WHERE date = '{$date}'
              AND order_id = '{$ccRec['order_id']}'
            ";
            $resultReceipt = $db->sql_query($sqlReceipt);
            $rowReceipt = $db->sql_fetchrow($resultReceipt);

            $receipt_amount = $rowReceipt['receipt_amount'];
            if ($rowReceipt['receipt_amount'] == '') {
                $receipt_amount = '0.00';
            }

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['branch_name']}</td>
                <td>{$row['student_name']}</td>
                <td>{$row['registration_no']}</td>
                <td>{$row['id_card_no']}</td>
                <td>{$row['gender']}</td>
                <td>{$row['parent_name']}</td>
                <td>{$row['dda']}</td>
                <td class='txtRight'>{$receipt_amount}</td>
            </tr>
            ";
            $serial_no++;
            $total_receipt_amount += $receipt_amount;
        }

        $total_receipt_amount = number_format($total_receipt_amount, 2);
        
        $text = "
        {$rows}
        <tr>
            <td colspan='8' class='txtRight'><strong>Sub Total</strong></td>
            <td colspan='8' class='txtRight'><strong>{$total_receipt_amount}</strong></td>
        </tr>
        ";

        return $text;
    }
}