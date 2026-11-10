<?
class CP_Admin_Widgets_Pms_IncomeByStudent_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Reg No</th>
                        <th>Student Name</th>
                        <th>Course Title</th>
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
        $rows = '';
        $serial_no = 0;

        foreach($this->model->dataArray as $row){
            $serial_no += 1;
            $net_total = number_format($row['net_total'], 2);
            $amount_paid = number_format($row['amount_paid'], 2);
            
            $due = number_format($row['net_total'] - $row['amount_paid'], 2);
            
            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['registration_no']}</td>
                <td>{$row['contact_name']}</td>
                <td>{$row['course_title']}</td>
                <td class='txtRight'>{$net_total}</td>
                <td class='txtRight'>{$due}</td>
                <td class='txtRight'>{$amount_paid}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}