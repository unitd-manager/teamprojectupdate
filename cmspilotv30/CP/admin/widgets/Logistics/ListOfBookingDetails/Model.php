<?
class CP_Admin_Widgets_Logistics_ListOfBookingDetails_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT b.*
        ,c.company_name
        FROM booking b
        LEFT JOIN company c ON (c.company_id = b.company_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;

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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'logistics_listOfBookingDetails');
        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}