<?
class CP_Admin_Widgets_Pms_StaffAttendanceReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Name</th>
                        <th class='txtCenter'>Leave Taken</th>
                        <th class='txtCenter'>Time In</th>
                        <th class='txtCenter'>Time Out</th>
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
            
            $on_leave = ($row['on_leave'] == 1) ? "Yes" : "No";

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$attendance_date}</td>
                <td>{$row['staff_name']}</td>
                <td class='txtCenter'>{$on_leave}</td>
                <td class='txtCenter'>{$row['time_in']}</td>
                <td class='txtCenter'>{$row['leave_time']}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}