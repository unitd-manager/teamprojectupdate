<?
class CP_Admin_Widgets_AceIms_BankReconcillationReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>DATE</th>
                        <th>CHEQUE NO</th>
                        <th>PAYEE</th>
                        <th>AMOUNT</th>
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
                <td>{$row['invoice_date']}</td>
                <td></td>
                <td></td>
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