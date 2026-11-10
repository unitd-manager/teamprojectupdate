<?
class CP_Common_Modules_Edukite_Student_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getSQL() {
        $fn = Zend_Registry::get('fn');
        $year  = $fn->getReqParam('year');

        $SQLYearAppend = '';

        if ($year != ''){
            $startYear = $year .'-01-01';
            $endYear   = $year .'-12-31';

            $SQLYearAppend .= "AND s.date_joined BETWEEN '{$startYear}' AND '{$endYear}'";
        }

        $SQL = "
        SELECT DISTINCT s.*
              ,CONCAT_WS(' ', s.first_name, s.last_name ) AS student_name
              ,gc.name AS country
        FROM `student` s
        LEFT JOIN geo_country gc ON (s.address_country_code = gc.country_code)
        LEFT JOIN class_student cs ON (cs.student_id = s.student_id)
        LEFT JOIN class c ON (c.class_id = cs.class_id)
        LEFT JOIN student_year_group syg ON (syg.student_id = s.student_id)
        LEFT JOIN year_group yg ON (yg.year_group_id = syg.year_group_id)
        {$SQLYearAppend }
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar($linkRecType = '') {
        $searchVar = Zend_Registry::get('searchVar');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $searchVar->mainTableAlias = 's';

        $class_id        = $fn->getReqParam('class_id');
        $year_group_id   = $fn->getReqParam('year_group_id');
        $year            = $fn->getReqParam('year');
        $status = $fn->getReqParam('status');

        /*if ($_SESSION['record_status'] != ''){
            $status = $_SESSION['record_status'];
        } else {
            $status = $fn->getReqParam('status');
        }*/

        if($status == 'Archive'){
            $searchVar->sqlSearchVar[] = "s.status = 'Archive'";
        } else {
            $searchVar->sqlSearchVar[] = "s.status = 'Active'";
        }

        if ($tv['record_id'] != '') {
            $searchVar->sqlSearchVar[] = "s.student_id = '{$tv['record_id']}'";
        } else {
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 's.student_id');

        }

        if ($class_id != '') {
            $searchVar->sqlSearchVar[] = "cs.class_id = {$class_id}";
        }

        if ($year_group_id != '') {
            $searchVar->sqlSearchVar[] = "syg.year_group_id = {$year_group_id}";
        }

        if ($year != ''){
            $startYear = $year .'-01-01';
            $endYear   = $year .'-12-31';

            $searchVar->sqlSearchVar[] = "s.date_joined BETWEEN '{$startYear}' AND '{$endYear}'";
        }

        if ($tv['keyword'] != "") {
            $searchVar->sqlSearchVar[] = "(
               s.first_name LIKE '%{$tv['keyword']}%'
            OR s.last_name LIKE '%{$tv['keyword']}%'
            )";
        }
        $searchVar->sortOrder = "s.last_name";
    }
}
