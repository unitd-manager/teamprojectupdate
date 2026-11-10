<?
class CP_Admin_Widgets_Tradingsg_SalesByYearChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT DATE_FORMAT(o.order_date, '%Y') AS order_year
              ,(SUM(oi.unit_price)) AS order_amount_yearly
        FROM `order` o
        LEFT JOIN order_item oi ON (o.order_id = oi.order_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'o';

        $searchVar->groupBy = "DATE_FORMAT(o.order_date, '%Y')";
    }

    /**
     *
     */
    function getDataArray() {

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_salesByYearChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}