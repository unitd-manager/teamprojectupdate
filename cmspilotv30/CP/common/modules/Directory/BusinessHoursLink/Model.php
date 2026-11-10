<?
class CP_Common_Modules_Directory_BusinessHoursLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    function getSQL() {
        $SQL = "
        SELECT bh.*
        FROM business_hours bh
        ";

        return $SQL;
    }

    function getSQLForPager() {
    }

    /**
     *
     */
    function setSearchVar($linkRecType) {
        $searchVar = Zend_Registry::get('searchVar');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $searchVar->mainTableAlias = 'bh';
        $relDataOnly = $fn->getIssetParam($this->expForSearchVar, 'relationalDataOnly');

        if ($relDataOnly){
            $fn->setSearchVarForLinkData($searchVar, $linkRecType, 'bh.business_hours_id');
        }

		$searchVar->sortOrder = "bh.week_day";
    }
    
    /**
     *
     */
    function getFields(){
        $fn = Zend_Registry::get('fn');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'week_day'); 
        $fa = $fn->addToFieldsArray($fa, 'start_time');
        $fa = $fn->addToFieldsArray($fa, 'start_time2');
        $fa = $fn->addToFieldsArray($fa, 'end_time');
        $fa = $fn->addToFieldsArray($fa, 'end_time2');
        
        return $fa;
    }

    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $fa = $this->getFields();
        $fa['business_id'] = $tv['srcRoomId'];
        $id = $fn->addRecord($fa);
    }

    /**
     *
     */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        
        $fa = $this->getFields();
        $id = $fn->saveRecord($fa);
    }
}