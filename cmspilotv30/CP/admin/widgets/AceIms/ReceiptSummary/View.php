<?
class CP_Admin_Widgets_AceIms_ReceiptSummary_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Date</th>
                        <th>Receipt Code</th>
                        <th>Parent</th>
                        <th>Mode of Payment</th>
                        <th class='txtRight'>Amount</th>
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
        $fn = Zend_Registry::get('fn');

        $rows = '';
        $print_total = '';
        $grand_total = 0;
        $mode_of_payment = '';
        $total_for_payment_mode = 0;
        
        $payment_mode = $fn->getReqParam('payment_mode');
        
        foreach($this->model->dataArray as $row){
            if ($mode_of_payment == '') {
                $print_total = 0;
                $mode_of_payment = $row['mode_of_payment'];
                $total_for_payment_mode = $row['amount'];
            } else if ($mode_of_payment == $row['mode_of_payment']) {
                $print_total = 0;
                $total_for_payment_mode += $row['amount'];
                $mode_of_payment = $row['mode_of_payment'];
            } else if ($mode_of_payment != $row['mode_of_payment']) {
                $print_total = 1;
                $mode_of_payment = $row['mode_of_payment'];
                $payment_total = $total_for_payment_mode;
                $total_for_payment_mode = $row['amount'];
            }

            if ($print_total == 1) {
                $payment_total = number_format($payment_total, 2);
                $print_total = "
                <tr class='highlight'>
                    <td colspan='4'>Total</td>
                    <td class='txtRight'><strong>{$payment_total}</strong></td>
                </tr>
                ";
            } else {
                $print_total = "";
            }
            
            $grand_total += $row['amount'];

            $rows .= "
            {$print_total}
            <tr>
                <td>{$row['receipt_date']}</td>
                <td>{$row['receipt_code']}</td>
                <td>{$row['first_name']}</td>
                <td>{$row['mode_of_payment']}</td>
                <td class='txtRight'>{$row['amount']}</td>
            </tr>
            ";
            
        }
        
        $total_for_payment_mode = number_format($total_for_payment_mode, 2);
        
        $grand_total_text = '';
        if ($payment_mode == 'All') {
            $grand_total = number_format($grand_total, 2);
            $grand_total_text = "
            <tr>
                <td colspan='4' class='highlight'>Grand Total</td>
                <td class='txtRight highlight'><strong>{$grand_total}</strong></td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        <tr>
            <td colspan='4' class='highlight'>Total</td>
            <td class='txtRight highlight'><strong>{$total_for_payment_mode}</strong></td>
        </tr>
        {$grand_total_text}
        ";

        return $text;
    }
}