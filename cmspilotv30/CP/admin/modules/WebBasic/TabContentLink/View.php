<?
class CP_Admin_Modules_WebBasic_TabContentLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $fnMod = includeCPClass('ModuleFns', 'webBasic_tabContent');

        $sortOrder = $fn->getNextSortOrder('tab_content', "record_id={$tv['srcRoomId']} AND room_name='{$tv['module']}'");

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title_hist')}
        {$formObj->getDDRowByArr('Content Type', 'content_type', $fnMod->getContentRecordTypeArray(), 'Record')}
        {$formObj->getYesNoRRow('Published', 'published')}
        {$formObj->getTBRow('Sort Order', 'sort_order', $sortOrder)}
        ";
        
        $fieldset2 = $formObj->getHTMLEditor('Description', 'description_hist');

        $fields = "
        {$formObj->getFieldSetWrapped('Content Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        ";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$fields}
            <input type='hidden' name='record_id' value='{$tv['srcRoomId']}' />
            <input type='hidden' name='room_name' value='{$tv['module']}' />
        </form>
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
        $ln = Zend_Registry::get('ln');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('tab_content', 'tab_content_id', $id);

        $fnMod = includeCPClass('ModuleFns', 'webBasic_tabContent');
        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title_hist', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowByArr('Content Type', 'content_type', $fnMod->getContentRecordTypeArray(), $row['content_type'])}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getTBRow('Sort Order', 'sort_order', $row['sort_order'])}
        ";
        
        $fieldset2 = $formObj->getHTMLEditor('Description', 'description_hist', $ln->gfv($row, 'description', '0'));

        $fields = "
        {$formObj->getFieldSetWrapped('Content Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        ";

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$fields}
            <input type='hidden' name='tab_content_id' value='{$id}' />
        </form>
        ";

        return $text;
    }
}
