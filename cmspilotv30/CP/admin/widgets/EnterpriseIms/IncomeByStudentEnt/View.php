<?
class CP_Admin_Widgets_EnterpriseIms_IncomeByStudentEnt_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>NRIC No</th>
                        <th>Course</th>
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
            
            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['contact_name']}</td>
                <td>{$row['id_card_no']}</td>
                <td>{$row['course_title']}</td>
                <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 1)}
                </td>
                <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 2)}
                </td>
                <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 3)}
                </td>
                <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 4)}
                </td>
                <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 5)}
                </td>
                <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 6)}
                </td>
                <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 7)}
                </td>
                <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 8)}
                </td>
                <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 9)}
                </td>
                <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 10)}
                </td>
                <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 11)}
                </td>
                <td>{$this->getStudentPaymentStatus($row['order_id'],$row['contact_id'], 12)}
                </td>
            </tr>
            ";
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
        SELECT i.status FROM invoice i
        WHERE i.contact_id = {$contact_id}
          AND i.invoice_month = {$month}
          AND i.order_id = {$order_id}
        ";
        $resultInv = $db->sql_query($SQLInv);
        $rowInv = $db->sql_fetchrow($resultInv);
        
        return $rowInv['status'];
    }
}