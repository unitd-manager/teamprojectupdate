<?
class CP_Admin_Widgets_AceIms_InvoiceSummaryReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>TYPE</th>
                        <th>DATE</th>
                        <th>NUMBER</th>
                        <th>ACCOUNT</th>
                        <th>AMOUNT</th>
                        <th>BALANCE</th>
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
        $db = Zend_Registry::get('db');
        $rows = '';
        $serial_no = 0;
        
        foreach($this->model->dataArray as $row){
            $serial_no += 1;

            $rows .= "
            <tr>
                <td></td>
                <td>{$row['invoice_date']}</td>
                <td></td>
                <td></td>
                <td>{$row['invoice_amount']}</td>
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