<?
class CPL_Admin_Widgets_EnggCrm_InvoiceByYearReports_View extends CP_Admin_Widgets_EnggCrm_InvoiceByYearReports_View
{
    /**
     *
     */
    function getWidget() {
        $fn    = Zend_Registry::get('fn');

        $c = &$this->controller;

        $record_type = $fn->getReqParam('record_type');

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        $text = "
        <table class='thinlist summaryTable'>
            <thead>
                <th colspan='2' class='txtCenter'>Summary</th>
            </thead>
            <tr>
                <td><b>Category :</b> {$record_type}</td>
                <td>Invoice by Year</td>
            </tr>
        </table>

		<div class = 'tableOuter scroll-pane'>
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
        </div>
        ";

        return $text;
    }
    
    /**
     *
     */
    function getRowsHTML() {
        $rows = '';
        foreach($this->model->dataArray as $row){
            $total = number_format($row['invoice_amount_yearly'], 2);
            
            if ($row['invoice_year']) {
                $rows .= "
                <tr>
                    <td>{$row['invoice_year']}</td>
                    <td class='txtRight'>{$total}</td>
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