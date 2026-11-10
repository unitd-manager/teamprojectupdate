<?
class CP_Admin_Widgets_AceIms_InvoiceListingReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>INVOICE NO</th>
                        <th>DATE</th>
                        <th>CUSTOMER NAME</th>
                        <th>PROJECT</th>
                        <th>CONSULTANT</th>
                        <th>QUOTATION NO</th>
                        <th>SO NO</th>
                        <th>JOB NO</th>
                        <th>SERVICES(JOB CATEGORY)</th>
                        <th>SALES PERSON</th>
                        <th>BILLING STATUS</th>
                        <th>AMOUNT BEFORE TAX</th>
                        <th>TAX AMOUNT</th>
                        <th>TIME STAMP</th>
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
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
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