<?
class CP_Admin_Widgets_Pms_NewEnrolment_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT c.title AS course_title
              ,b.title AS batch_title
              ,b.venue 
              ,b.start_time 
              ,b.end_time 
              ,t.first_name 
			  ,(SELECT COUNT(*)
				FROM course_contact cc
				WHERE cc.batch_id = b.batch_id
                AND b.status = 'Current'
				) AS attendee_count
        FROM batch b
        JOIN course c ON (c.course_id = b.course_id)
        JOIN teacher t ON (t.teacher_id = b.teacher_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'b';

        $searchVar->sqlSearchVar[] = " b.status = 'Current'";
        $searchVar->sortOrder = "b.batch_id ASC";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_newEnrolment');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}