<?
class CP_Admin_Widgets_Hms_PatientVisitChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%d') AS day
              ,count(ev.patient_visit_id) AS patients_visited
              ,ev.employee_id
        FROM employee_visit ev
        LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = ev.patient_visit_id)
        ";

        $SQL = "
        SELECT pv.check_up_date
              ,DATE_FORMAT(pv.check_up_date, '%d') AS day
              ,count(pv.patient_visit_id) AS patients_visited
        FROM patient_visit pv
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'pv';
        $month          = date('m');
        $year           = date('Y');

        $start_date = $year . '-' . $month . '-' . '01';
        $end_date = $year . '-' . $month . '-' . '31';
        
        $searchVar->sqlSearchVar[] = "pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        $searchVar->sqlSearchVar[] = "pv.status != 'Cancelled'";
        $searchVar->groupBy = "pv.check_up_date";
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
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_patientVisitChart');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}