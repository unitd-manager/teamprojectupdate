<?
class CP_Admin_Widgets_Labsg_TreatmentHistoryChart_Model extends CP_Common_Lib_WidgetModelAbstract
{
    function getSQL(){

        $SQL = "
        SELECT  t.*
        FROM `treatment` t
        LEFT JOIN (treatment_visit tv) ON (t.treatment_id = tv.treatment_id)
        LEFT JOIN (patient_visit pv) ON (tv.patient_visit_id = pv.patient_visit_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'pv';
        
        $current_year = date('Y');
        $current_month = date('m');
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $modelHelper = Zend_Registry::get('modelHelper');

        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'labsg_patientVisitChart');
        $this->dataArray = $dataArray;
        return $this->dataArray;
    }
}