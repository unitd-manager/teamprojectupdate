<?
class CP_Admin_Widgets_EnterpriseIms_TraineeByCourse_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Number of trainee</th>
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
            $rows .= "
            <tr>
                <td>{$row['course_title']}</td>
                <td>{$row['attendee_count']}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}