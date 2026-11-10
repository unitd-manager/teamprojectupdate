<?
class CP_Www_Widgets_Event_EventItem_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT ei.*
              ,e.title AS event_title
        FROM event_item ei
        LEFT JOIN event e ON (e.event_id = ei.event_id)
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;

        $searchVar->sqlSearchVar[] = "ei.event_id = {$c->eventId}";

        $searchVar->sortOrder = $c->orderBy;
    }

    /**
     *
     */
    function getDataArray(){
        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'event_eventItem');

        $this->dataArray = $dataArray ;
        return $dataArray;
    }

}