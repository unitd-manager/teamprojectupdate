<?
class CP_Admin_Widgets_EnterpriseIms_StudentStatusReports_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Reg No</th>
                        <th>NRIC NO</th>
                        <th>Course Title</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Gender</th>
                        <th>Nationality</th>
                        <th>DOB</th>
                        <th>Mobile</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Parent Name</th>
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
                <td>{$serial_no}</td>
                <td>{$row['trainee_name']}</td>
                <td>{$row['registration_no']}</td>
                <td>{$row['id_card_no']}</td>
                <td>{$row['course_title']}</td>
                <td>{$fn->getCPDate($row['valid_date_from'], 'd-M-Y')}</td>
                <td>{$fn->getCPDate($row['valid_date_to'], 'd-M-Y')}</td>
                <td>{$row['gender']}</td>
                <td>{$row['nationality']}</td>
                <td>{$fn->getCPDate($row['date_of_birth'], 'd-M-Y')}</td>
                <td>{$row['mobile']}</td>
                <td>{$row['phone']}</td>
                <td>{$row['course_status']}</td>
                <td>{$row['emergency_contact_name']}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}