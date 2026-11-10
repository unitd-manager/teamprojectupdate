<?
class CP_Admin_Modules_AceIms_SubsidyLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    /**
     *
     */
    function getList($dataArray, $linkRecType) {
        $listObj = Zend_Registry::get('listObj');
        $listLinkObj = Zend_Registry::get('listLinkObj');

        $rows       = '';
        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listLinkObj->getListRowHeaderLink($row, $rowCounter)}
            {$listObj->getListDataCell($row['title'])}
            {$listLinkObj->getListRowEndLink($linkRecType, $row['subsidy_id'])}
            ";
            
            $rowCounter++;
        }

        $text = "
        {$listLinkObj->getListHeaderLink()}
        {$listLinkObj->getListHeaderCellLink($linkRecType,"Title", "a.title")}
        {$listLinkObj->getListHeaderEndLink($linkRecType)}
        {$rows}
        {$listLinkObj->getListFooterLink()}
        ";
        
        return $text;
    }
    
    /**
     *
     */
    function getEdit(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $sqlType = $fn->getValueListSQL('subsidyType');
        $expVl = array('sqlType' => 'OneField');

        $exp = array('isEditable' => 0);
        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('course_subsidy', 'course_subsidy_id', $id);
        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title', $row['title'])}
                {$formObj->getTBRow('Amount', 'fees', $row['fees'], $exp)}
                {$formObj->getTBRow('Value', 'value', $row['value'])}
                {$formObj->getDDRowBySQL('Type', 'type', $sqlType, $row['type'], $expVl)}
            </fieldset>
            <input type='hidden' name='course_subsidy_id' value='{$id}' />
        </form>
        ";
        return $text;
    }
    
    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";
        $sqlType = $fn->getValueListSQL('subsidyType');
        $expVl = array('sqlType' => 'OneField');
        $exp = array('isEditable' => 0);

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            <fieldset>
                {$formObj->getTBRow('Title', 'title')}
                {$formObj->getTBRow('Amount', 'fees', '', $exp)}
                {$formObj->getTBRow('Value', 'value')}
                {$formObj->getDDRowBySQL('Type', 'type', $sqlType, '', $expVl)}
            </fieldset>
            <input type='hidden' name='{$fn->getSrcRoomKeyFldName()}' value='{$tv['srcRoomId']}' />
        </form>
        ";

        return $text;
    }
}
