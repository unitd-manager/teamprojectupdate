<?
class CP_Admin_Widgets_EnterpriseIms_TraineeNotLinked_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT DISTINCT cc.course_contact_id
              ,c.title AS course_title
              ,cc.contact_id
              ,ct.first_name 
        FROM course_contact cc 
        JOIN course c ON (c.course_id = cc.course_id)
        JOIN contact ct ON (ct.contact_id = cc.contact_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'cc';

        $searchVar->sqlSearchVar[] = " cc.batch_id IS NULL";
        //$searchVar->sqlSearchVar[] = " ct.status = 'New'";
    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'enterpriseIms_traineeNotLinked');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }

}