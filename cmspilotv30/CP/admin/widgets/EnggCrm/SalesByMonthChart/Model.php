<?
class CP_Admin_Widgets_EnggCrm_SalesByMonthChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DATE_FORMAT(o.order_date, '%b %Y') AS order_month
              ,(SUM(oi.unit_price*oi.qty)) AS order_amount_monthly
        FROM order_item oi
        LEFT JOIN (`order` o) ON (oi.order_id   = o.order_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $last12Month = date('Y-m-d',mktime (0,0,0,date("m")-12,1, date("Y")));
        $today       = date('Y-m-d');

        $searchVar->sqlSearchVar[] = "(o.order_date BETWEEN '{$last12Month}' AND '{$today}')";
        $searchVar->groupBy = "DATE_FORMAT(o.order_date, '%Y-%m')";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enggCrm_salesByMonthChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }
}