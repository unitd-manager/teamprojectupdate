<?
class CP_Admin_Widgets_Tradingsg_PosSalesSummary_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $cpCfg = Zend_Registry::get('cpCfg');

        $sumTxt = '';
        if ($cpCfg['m.ecommerce.order.hasDiscount']){
            $sumTxt = "SUM(oi.unit_price * oi.qty) + o.shipping_charge - o.discount";
        } else {
            $sumTxt = "SUM(oi.unit_price * oi.qty) + o.shipping_charge";
        }

        $SQL = "
        SELECT o.*
              ,SUM(oi.cost_price*oi.qty) AS Amount
              ,($sumTxt) AS order_amount
              ,(SELECT SUM(srh.qty_return * srh.price) FROM sales_return_history srh
               WHERE o.order_id = srh.order_id
               AND srh.status IS NULL
               ) as sales_return_amount
        FROM `order` o
        LEFT JOIN order_item oi on (o.order_id=oi.order_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';
        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $staff_id   = $fn->getReqParam('staff_id');
        $current_date = date('Y-m-d');
        $current_year = date('Y');
        $current_month = date('m');

        $start_date = $current_year . '-' . $current_month . '-' . '01';
        $end_date = $current_year . '-' . $current_month . '-' . '31';
        $searchVar->sqlSearchVar[] = "o.order_date BETWEEN '{$start_date}' AND '{$end_date}' AND o.record_type = 'POS'";
        $searchVar->groupBy = "o.order_id";
        $searchVar->sortOrder = 'o.order_date DESC';
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_posSalesSummary');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}