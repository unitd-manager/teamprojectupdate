<?
class CP_Admin_Widgets_AgileIms_IncomeByStudent_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Invoice Date</th>
                        <th>Student/Company Name</th>
                        <th>Invoice Type</th>
                        <th>Invoice Status</th>
                        <th class='txtRight'>Income (net total)</th>
                        <th class='txtRight'>Outstanding Fees / Amount</th>
                        <th class='txtRight'>Paid</th>
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
        $dateUtil = Zend_Registry::get('dateUtil');

        $rows = '';
        $serial_no = 0;
        $overall_net_total = 0;
        $overall_due_total = 0;
        $overall_paid_total = 0;

        foreach($this->model->dataArray as $row){
            $serial_no += 1;
            $amount_paid = $this->model->getReceiptAmountForInvoice($row['invoice_id']);
            $due = $row['invoice_amount'] - $amount_paid;

            $net_total_formatted   = number_format($row['invoice_amount'], 2);
            $due_amount_formatted  = number_format($due, 2);
            $amount_paid_formatted = number_format($amount_paid, 2);
            
            $invoice_date = $dateUtil->formatDate($row['invoice_date'], 'DD-MM-YYYY');

            $overall_net_total += $row['invoice_amount'];
            $overall_due_total += $due;
            $overall_paid_total += $amount_paid;

            if ($row['contact_id']) {
                $contactRec = $fn->getRecordRowById('contact','contact_id',$row['contact_id']);
                $name = $contactRec['first_name'];
            } else {
                $companyRec = $fn->getRecordRowById('company','company_id',$row['company_id']);
                $name = $companyRec['title'];
            }

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$invoice_date}</td>
                <td>{$name}</td>
                <td>{$row['invoice_type']}</td>
                <td>{$row['status']}</td>
                <td class='txtRight'>{$net_total_formatted}</td>
                <td class='txtRight'>{$due_amount_formatted}</td>
                <td class='txtRight'>{$amount_paid_formatted}</td>
            </tr>
            ";
        }

        $formatted_overall_net_total  = number_format($overall_net_total, 2);
        $formatted_overall_due_total  = number_format($overall_due_total, 2);
        $formatted_overall_paid_total = number_format($overall_paid_total, 2);
        
        $text = "
        {$rows}
        <tr class='txtRight'>
            <th colspan='5'>Total Amount</th>
            <th>{$formatted_overall_net_total}</th>
            <th>{$formatted_overall_due_total}</th>
            <th>{$formatted_overall_paid_total}</th>
        </tr>
        ";

        return $text;
    }
}