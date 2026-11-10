<?
class CP_Admin_Widgets_Hms_TreatmentHistory_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT  t.*
        FROM `treatment` t
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 't';

        $start_date = $fn->getReqParam('start_date');
        $end_date   = $fn->getReqParam('end_date');
        $month      = $fn->getReqParam('month');
        $year       = $fn->getReqParam('year');
        $current_date = date('Y-m-d');
        $current_year = date('Y');
        $current_month = date('m');

        $start_date = $current_year . '-' . $current_month . '-' . '01';
        $end_date = $current_year . '-' . $current_month . '-' . '31';
        //$searchVar->sqlSearchVar[] = "pv.check_up_date BETWEEN '{$start_date}' AND '{$end_date}' ";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_treatmentHistory');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}