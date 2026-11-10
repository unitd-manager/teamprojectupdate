<?
class CP_Admin_Widgets_AceIms_IncomeExpenses_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != "") {
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th class='txtCenter'>Income</th>
                        <th class='txtCenter'>Expense</th>
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
        
        foreach ($this->model->dataArray as $month => $row) {
            $income  = $row['income'];
            $expense = $row['expense'];
            
            if ($month =='Total') {
                $month = "<b>{$month}</b>";
                $income = "<b>{$income}</b>";
                $expense = "<b>{$expense}</b>";
            }
            
            $rows .= "
            <tr>
                <td class=''>{$month}</td>
                <td class='txtRight'>{$income}</td>
                <td class='txtRight'>{$expense}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }
}