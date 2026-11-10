<?
class CP_Admin_Widgets_Tradingsg_SalesByMonthChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        
        $SQL = "
        SELECT DATE_FORMAT(o.order_date, '%b %Y') AS order_month
              ,(SUM(oi.unit_price)) AS order_amount_monthly
        FROM `order` o
        LEFT JOIN order_item oi ON (o.order_id = oi.order_id)
        ";

        $SQL = "
        SELECT DATE_FORMAT(o.order_date, '%b %Y') AS order_month
              ,o.order_id
              ,(SUM
                   (SELECT (i.unit_price * i.qty) AS total_amount
                    FROM order_item i
                    WHERE i.order_id = o.order_id
                   )
                ) AS order_amount_monthly
        FROM `order` o
        LEFT JOIN order_item oi ON (o.order_id = oi.order_id)
        ";

        $SQL = "
        SELECT o.*
        FROM `order` o
        ";

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_salesByMonthChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}