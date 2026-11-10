<?
class CP_Admin_Widgets_ManPower_OpportunityByMonthReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget() {
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Serial No</th>
                        <th>Opportunity Title</th>
                        <th>Position</th>
                        <th>No. of Position</th>
                        <th>Staff Name</th>
                        <th class='txtRight'>Value</th>
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

        $rows         = '';
        $serial_no    = 0;
        $total_amount = 0;
        foreach($this->model->dataArray as $row){
            $serial_no += 1;

            $estimated_value = '';
            if ($row['estimated_value']) {
                $estimated_value = number_format($row['estimated_value'], 2);
            }

            $rows .= "
            <tr>
                <td>{$serial_no}</td>
                <td>{$row['title']}</td>
                <td>{$row['position']}</td>
                <td>{$row['no_of_position']}</td>
                <td>{$row['staff_name']}</td>
                <td class='txtRight'>{$estimated_value}</td>
            </tr>
            ";

            $total_amount += $row['estimated_value'];
        }

        $total_amount = number_format($total_amount, 2);

        $text = "
        {$rows}
        <tr>
            <td class='txtRight' colspan='5'><strong>Total</strong></td>
            <td class='txtRight'>{$total_amount}</td>
        </tr>
        ";

        return $text;
    }
}