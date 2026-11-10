<?
class CP_Admin_Widgets_Tradingsg_LeadFollowUp_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT c.*
              ,c.company_name
              ,c.contact_date
              ,c.status
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS staff_name
        FROM call_registry c
        LEFT JOIN staff s ON (s.staff_id = c.staff_id)

        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');

        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'c';
        $month     = $fn->getReqParam('month');
        $year      = $fn->getReqParam('year');

        if ($year == '') {
            $year = date('Y');
        }

        if ($month == '') {
            $month = date('m');
        }

        $startDate = $year . '-' . $month . '-' . '01 00:00:00';
        $endDate   = $year . '-' . $month . '-' . '31 23:59:59';

        $searchVar->sqlSearchVar[] = "c.contact_date BETWEEN '{$startDate}' AND '{$endDate}'";

        if($fn->getSessionParam('userGroupType') == 'User'){
            $searchVar->sqlSearchVar[] = "s.staff_id = {$fn->getSessionParam('staff_id')}";
        }

        //if ($staff_id != '' ) {
        if($fn->getSessionParam('userGroupType') != 'Super Administrator'){
            $searchVar->sqlSearchVar[] = "s.staff_id = {$fn->getSessionParam('staff_id')}";
        }
        $searchVar->sortOrder = 'c.contact_date DESC';
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'tradingsg_leadFollowUp');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}