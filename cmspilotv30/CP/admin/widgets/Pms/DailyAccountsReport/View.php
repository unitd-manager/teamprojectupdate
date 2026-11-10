<?
class CP_Admin_Widgets_Pms_DailyAccountsReport_View extends CP_Common_Lib_WidgetViewAbstract
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
                        <th>Date</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th class='txtRight'>Income</th>
                        <th class='txtRight'>Expense</th>
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
        $fn = Zend_Registry::get('fn');
        
        foreach($this->model->dataArray as $month => $row){
            $expAmount = '';
            $incAmount = '';
            
            if($row['type'] == 'Income'){
                $incAmount = $row['amount'];
            }
            else if($row['type'] == 'Expense'){
                $expAmount = $row['amount'];
            }
            
            $rows .= "
            <tr>
                <td>{$fn->getCPDate($row['date'], 'd-M-Y')}</td>
                <td class=''>{$row['title']}</td>
                <td class=''>{$row['description']}</td>
                <td class='txtRight'>{$incAmount}</td>
                <td class='txtRight'>{$expAmount}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}