<?
class CP_Admin_Widgets_Logistics_IncomeByMonth_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Year</th>
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
        $rows = '';

        foreach($this->model->dataArray as $row){

            $invoice_amount_monthly = number_format($row['invoice_amount_yearly'], 2);
            
            if ($row['invoice_year']) {
                $rows .= "
                <tr>
                    <td>{$row['invoice_year']}</td>
                    <td class='txtRight'>{$invoice_amount_monthly}</td>
                </tr>
                ";    
            }
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}