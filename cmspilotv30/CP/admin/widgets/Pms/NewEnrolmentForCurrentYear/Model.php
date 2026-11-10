<?
class CP_Admin_Widgets_Pms_NewEnrolmentForCurrentYear_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $year = date('Y');
        $SQL = "
        SELECT c.title AS course_title
              ,b.title AS batch_title
              ,co.first_name AS student_name
              ,p.first_name AS parent_name
              ,p.mode_of_payment
              ,cc.course_contact_id
              ,cc.creation_date
			  ,(SELECT COUNT(*)
				FROM course_contact cco
				WHERE cco.contact_id = co.contact_id
				  AND cco.year_of_enrollment <= {$year}
                  AND co.status = 'Active'
				) AS contact_count
        FROM course_contact cc
        JOIN course c ON (c.course_id = cc.course_id)
        JOIN contact co ON (co.contact_id = cc.contact_id)
        JOIN batch b ON (b.batch_id = cc.batch_id)
        JOIN parent p ON (p.parent_id = cc.parent_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'cc';
        $year = date('Y');

        $searchVar->sqlSearchVar[] = "cc.year_of_enrollment = {$year}";
        $searchVar->sqlSearchVar[] = "co.status = 'Active'";

        $searchVar->sortOrder = "cc.course_contact_id DESC";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'pms_newEnrolmentForCurrentYear');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}