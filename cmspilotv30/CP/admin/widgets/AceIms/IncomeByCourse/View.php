<?
class CP_Admin_Widgets_AceIms_IncomeByCourse_View extends CP_Common_Lib_WidgetViewAbstract
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
            
            $discount_amount = $this->getCalculateDiscountAmount($row['order_id'], $row['net_total']);
            $net_total = number_format(($row['net_total'] - $discount_amount), 2);
            $rows .= "
            <tr>
                <td>{$row['course_title']}</td>
                <td>{$row['net_total']}</td>
                <td class='txtRight'>{$discount_amount}</td>
            </tr>
            ";
        }
        
        $text = "
        {$rows}
        ";

        return $text;
    }

    /**
     *
     */
    function getCalculateDiscountAmount($order_id, $total_amount_before_discount) {
        $fn = Zend_Registry::get('fn');
        
        $order_item_rec = $fn->getRecordByCondition('order_item', "order_id = {$order_id} AND item_title = 'Discount'");
        
        $discount_amount = (($total_amount_before_discount * $order_item_rec['unit_price']) / 100);
        
        return $discount_amount;
    }
}