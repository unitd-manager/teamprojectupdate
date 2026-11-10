<?
class CP_Admin_Widgets_EnterpriseIms_IncomeByCourse_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Course</th>
                        <th>Total Number of Students</th>
                        <th class='txtRight'>Total</th>
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

        foreach($this->model->dataArray as $row){
            $net_total = number_format($row['net_total'], 2);
            $rows .= "
            <tr>
                <td>{$row['course_title']}</td>
                <td>{$row['course_contact_count']}</td>
                <td class='txtRight'>{$net_total}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}