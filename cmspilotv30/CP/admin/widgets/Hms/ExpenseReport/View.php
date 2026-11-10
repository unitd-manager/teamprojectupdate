<?
class CP_Admin_Widgets_Hms_ExpenseReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $text = "
        <h2>Expence Report</h2>
        <div class = 'tableOuter scroll-pane'>
        <table class='thinlist'>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                {$this->getRowsHTML()}
            </tbody>
        </table>
        </div>
        ";
        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';
        $count = 1;

        foreach($this->model->dataArray as $row){
            $expense_date = $fn->getCPDate($row['creation_date'],"d-m-Y");

            $rows .= "
            <tr>
                <td>{$expense_date}</td>
                <td>{$row['title']}</td>
                <td>{$row['status']}</td>
                <td>{$row['from_location']}</td>
            </tr>
            ";
            $count++;
        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}