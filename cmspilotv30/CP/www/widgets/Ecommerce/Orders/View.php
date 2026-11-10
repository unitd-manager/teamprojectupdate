<?
class CP_Www_Widgets_Ecommerce_Orders_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqCalculation-0.4.08');
    /**
     *
     */
    function getWidget($exp = array()){
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <h1>{$ln->gd($c->heading)}</h1>
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th class='txtCenter'>Order No</th>
                        <th>Name of Buyer</th>
                        <th>Purpose</th>
                        <th>Mode of payment</th>
                        <th class='txtRight'>Quantity</th>
                        <th class='txtRight'>Amount S$</th>
                        <th class='txtRight'>Amount Paid S$</th>
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
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $dbUtil = Zend_Registry::get('dbUtil');
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $rows = '';

        foreach ($this->model->dataArray as $key => $row) {
            $name = $row['shipping_first_name'] .  ' ' . $row['shipping_last_name'];

            $total_amount = number_format($row['order_amount'], $c->decimals);
            $total_qty = number_format($this->getTotalQty($row['order_id']));
            $amount_paid = number_format($row['amount_paid']);

            $SQL = "
            SELECT *
            FROM order_item
            WHERE order_id = {$row['order_id']}
            ";
            $itemsRec = $dbUtil->getSQLResultAsArray($SQL);

            $items = '';
            foreach ($itemsRec as $itemKey => $item) {
                $items .= "
                <tr>
                    <td>{$item['item_title']}</td>
                    <td class='qty'>{$item['qty']}</td>
                    <td>{$item['notes']}</td>
                </tr>
                ";
            }

            $rows .= "
            <tr>
                <td class='txtCenter'>{$row['order_id']}
                    <a href='#' class='printIcon printOrder' order_id='{$row['order_id']}'></a>
                </td>
                <td>{$name}</td>
                <td>
                    <table class='thinlist orderItems'>
                        <tr>
                            <th class='item'>Item</th>
                            <th class='qty'>Qty</th>
                            <th class='notes'>Notes</th>
                        </tr>
                        {$items}
                    </table>
                </td>
                <td>{$row['payment_method']}</td>
                <td class='txtRight'>{$total_qty}</td>
                <td class='txtRight'>{$total_amount}</td>
                <td class='txtRight amtPaid' amount='{$row['order_amount']}' order_id='{$row['order_id']}'>{$amount_paid}</td>
            </tr>
            ";
        }

        return $rows;
    }

    /**
     *
     */
    function getQtyText($row) {
        $cpUtil = Zend_Registry::get('cpUtil');
        $c = &$this->controller;

        if($c->showQtyDropDown){
            $stock = $c->maxQuantity;

            $arr = array();
            for($i = 0; $i <= $stock; $i++){
                $arr[] = $i;
            }
            $text = "
            <select class='input' name='qty_{$row[$c->keyFldName]}' {$c->keyFldName}='{$row[$c->keyFldName]}'>
                {$cpUtil->getDropDown1($arr)}
            </select>
            ";
        } else {
            $text = "
            <input class='input' type='hidden' name='qty_{$row[$c->keyFldName]}' {$c->keyFldName}='{$row[$c->keyFldName]}'>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getTotalQty($order_id) {
        $db = Zend_Registry::get('db');
        $total_amount = '';

        $SQL = "
        SELECT SUM(qty) AS totalQty
        FROM order_item
        WHERE order_id = '{$order_id}'
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        return $totalQty = $row['totalQty'];
    }

    /**
     *
     */
    function getPrintOrder() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;

        checkLoggedIn();
        $dataArray = $this->model->getDataArray();

        if (count($dataArray) > 0){
            $row = $dataArray[0];
            $name = $row['shipping_first_name'] .  ' ' . $row['shipping_last_name'];
            $total_amount = number_format($row['order_amount'], $c->decimals);
            $total_qty = number_format($this->getTotalQty($row['order_id']));
            $amount_paid = number_format($row['amount_paid']);
            
            $text = "
            <div class='printOrder'>
            <div class='floatbox'>
                <div class='button float_right'>
                    <a class='cpPrint'>{$ln->gd('cp.lbl.print')}</a>
                </div>
            </div>
            <table class='thinlist'>
            
                <tr>
                    <td colspan='2'><h2>Payment Receipt</h2></td>
                </tr>
                
                <tr>
                   <th>Order Id</th>
                   <td>{$row['order_id']}</td>
                </tr>
                
                <tr>
                   <td>Name of the Buyer</td>
                   <td>{$name}</td>
                </tr>
                
                <tr>
                   <td>Quantity</td>
                   <td>{$total_qty}</td>
                </tr>
                
                <tr>
                   <td>Amount</td>
                   <td>{$total_amount}</td>
                </tr>
                
                <tr>
                   <td>Amount Paid</td>
                   <td>{$amount_paid}</td>
                </tr>
                
                </table>            
            </div>
            ";
        }
        
        return $text;
    }
}