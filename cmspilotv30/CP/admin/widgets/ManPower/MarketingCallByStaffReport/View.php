<?
class CP_Admin_Widgets_ManPower_MarketingCallByStaffReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Staff Name</th>
                        <th>No of Calls</th>
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
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = '';
        $serial_no = 0;
        foreach($this->model->dataArray as $row){
            $serial_no += 1;            
            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['staff_name']}</td>
                <td>{$row['no_of_calls']}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}