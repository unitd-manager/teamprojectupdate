<?
class CP_Admin_Widgets_Project_AttendanceReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Serial No.</th>
                        <th>Date</th>
                        <th>Staff Name</th>
                        <th>Type of Leave</th>
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
        foreach($this->model->dataArray as $row){
            $serial_no += 1;
            
            $attendance_date = $dateUtil->formatDate($row['record_date'], 'DD-MM-YYYY');
            
            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$attendance_date}</td>
                <td>{$row['staff_name']}</td>
                <td>{$row['type_of_leave']}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}