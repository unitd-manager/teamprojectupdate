<?
class CP_Admin_Widgets_AceIms_PaymentOutstandingReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>S/No</th>
                        <th>Student Name</th>
                        <th>Parent Name</th>
                        <th>Jan</th>
                        <th>Feb</th>
                        <th>Mar</th>
                        <th>Apr</th>
                        <th>May</th>
                        <th>Jun</th>
                        <th>Jul</th>
                        <th>Aug</th>
                        <th>Sep</th>
                        <th>Oct</th>
                        <th>Nov</th>
                        <th>Dec</th>
                        <th>Total Due</th>
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
    /**
     *
     */
    function getRowsHTML() {
        $rows = '';
        $serial_no = 0;

        foreach($this->model->dataArray as $row){
            
            $balance_amount = $this->getOutstandingBalanceAmount($row['order_id']);
            /* Displaying the Students who owe to pay and neglecting the paid students */
            if ($balance_amount) {
                $serial_no += 1;

                $balance_amount_formatted = number_format($balance_amount, 2);
                $rows .= "
                <tr>
                    <td>{$serial_no}</td>
                    <td>{$row['contact_name']}</td>
                    <td>{$row['parent_name']}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 1)}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 2)}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 3)}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 4)}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 5)}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 6)}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 7)}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 8)}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 9)}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 10)}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 11)}</td>
                    <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 12)}</td>
                    <td>{$balance_amount_formatted}</td>
                </tr>
                "; 
            }
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getStudentPaymentStatus($order_id, $contact_id, $month) {
        $db = Zend_Registry::get('db');
        
        $year = date('Y');
        
        $start_date = $year . '-01-01';
        $end_date = $year . '-12-31';

        $SQLInv = "
        SELECT i.status
              ,i.invoice_amount
              ,i.discount_amount
        FROM invoice i
        WHERE i.contact_id = {$contact_id}
          AND i.invoice_month = {$month}
          AND i.order_id = {$order_id}
        ";
        $resultInv  = $db->sql_query($SQLInv);
        $numRowsInv = $db->sql_numrows($resultInv);
        
        $text = "";
        
        if ($numRowsInv) {
            $rowInv = $db->sql_fetchrow($resultInv);
            
            if ($rowInv['status'] == 'Paid') {
                $text = $rowInv['status'];
            } else {
                $balance_amount = $rowInv['invoice_amount'] - $rowInv['discount_amount'];
                $balance_amount_formatted = number_format($balance_amount, 2);
                $text = $rowInv['status'] . " [" . $balance_amount . "]";
            }
        }
        
        return $text;
    }

    /**
     *
     */
    function getOutstandingBalanceAmount($order_id) {
        $db = Zend_Registry::get('db');

        $sqlOrder = "
        SELECT SUM(i.invoice_amount) AS total_invoice_amount_due
              ,SUM(i.discount_amount) AS total_invoice_amount_discounted
        FROM invoice i
        LEFT JOIN (`order` o) ON (i.order_id = o.order_id)
        WHERE i.order_id = {$order_id}
          AND (i.status = 'Due'
           OR i.status = 'Partial Payment')
        ";
        $resultOrder = $db->sql_query($sqlOrder);        
        $rowOrder = $db->sql_fetchrow($resultOrder);
        
        $balance_amount = $rowOrder['total_invoice_amount_due'] - $rowOrder['total_invoice_amount_discounted'];

        return $balance_amount;
    }
}