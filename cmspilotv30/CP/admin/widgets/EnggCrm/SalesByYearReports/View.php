<?
class CP_Admin_Widgets_EnggCrm_SalesByYearReports_View extends CP_Common_Lib_WidgetViewAbstract
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
            <h2>Sales by Year</h2>
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
        }

        return $text;
    }
    
    /**
     *
     */
    function getRowsHTML() {
        $rows = '';
        foreach($this->model->dataArray as $row){
            $total = number_format($row['order_amount_yearly'], 2);
            
            if ($row['order_year']) {
                $rows .= "
                <tr>
                    <td>{$row['order_year']}</td>
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