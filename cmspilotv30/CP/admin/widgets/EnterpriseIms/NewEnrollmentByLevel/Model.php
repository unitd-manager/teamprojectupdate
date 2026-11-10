<?
class CP_Admin_Widgets_EnterpriseIms_NewEnrollmentByLevel_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $fn = Zend_Registry::get('fn');

        $site_id = $fn->getSessionParam('cp_site_id');
        
        $appendSql = '';
        if ($site_id) {
            $appendSql = " AND cc.site_id = {$site_id}";
        }
        
        $current_year = date('Y');
        $next_year    = date('Y') + 1;

        $SQL = "
        SELECT DISTINCT c.title AS course_title
              ,b.title AS batch_title
              ,l.title AS level_title
			  ,(
			    SELECT COUNT(*) FROM course_contact cc
				WHERE
                ((l.level_id = '' or l.level_id is null)
                    AND cc.batch_id  = b.batch_id
                    AND cc.level_id IS NULL
                    AND b.status = 'Open'
                    AND cc.year_of_enrollment = {$current_year}
                    {$appendSql})
                
                or
                ((l.level_id > 0)
                    AND cc.batch_id  = b.batch_id
                    AND cc.course_id = c.course_id
                    AND cc.level_id  = l.level_id
                    AND b.status = 'Open'
                    AND cc.year_of_enrollment = {$current_year}
                    {$appendSql})

				) AS attendee_count_current
			  ,(
			    SELECT COUNT(*) FROM course_contact cc
				WHERE
                ((l.level_id = '' or l.level_id is null)
                    AND cc.batch_id  = b.batch_id
                    AND cc.level_id IS NULL
                    AND b.status = 'Open'
                    AND cc.year_of_enrollment = {$next_year}
                    {$appendSql})
                
                or
                ((l.level_id > 0)
                    AND cc.batch_id  = b.batch_id
                    AND cc.course_id = c.course_id
                    AND cc.level_id  = l.level_id
                    AND b.status = 'Open'
                    AND cc.year_of_enrollment = {$next_year}
                    {$appendSql})

				) AS attendee_count_next_year
				,b.max_enroll_count
        FROM batch b
        JOIN course c ON (c.course_id = b.course_id)
        LEFT JOIN course_contact cc ON (c.course_id = cc.course_id)
        LEFT JOIN level l ON (cc.level_id = l.level_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'cc';

        $searchVar->sqlSearchVar[] = "b.status = 'Open'";
        $searchVar->sortOrder = "b.batch_id ASC";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enterpriseIms_newEnrolment');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}