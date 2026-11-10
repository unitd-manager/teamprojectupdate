<?
class CP_Admin_Widgets_AceIms_TeacherPaymentReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>TEACHER NAME</th>
                        <th>NRIC NO</th>
                        <th>COURSE</th>
                        <th>MODULE</th>
                        <th>INTAKE NO</th>
                        <th>DEPLOYMENT PERIOD</th>
                        <th>PAYMENT</th>
                        <th>PAYMENT STATUS</th>
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
                <td>{$row['first_name']}</td>
                <td>{$row['id_card_no']}</td>
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