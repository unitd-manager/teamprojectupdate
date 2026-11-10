<?
class CP_Admin_Modules_WebBasic_ContentHistoryLink_View extends CP_Common_Lib_ModuleLinkViewAbstract
{
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');

        $formAction = "index.php?_spAction=add&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $fnMod = includeCPClass('ModuleFns', 'webBasic_contentHistory');

        $tabsDD = '';

        if ($cpCfg['m.webBasic.content.hasTab'] == 1){
            $sqlCombo = "
            SELECT tab_content_id
                  ,title
            FROM tab_content
            WHERE record_id = {$tv['srcRoomId']}
              AND room_name = 'content'
            ORDER BY title
            ";

            $tabsDD = "
            {$formObj->getDDRowBySQL('Tab', 'tab_content_id', $sqlCombo)}
            ";
        }

        $sortOrder = $fn->getNextSortOrder('content_history', "record_id={$tv['srcRoomId']} AND room_name='{$tv['module']}'");

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title_hist')}
        {$formObj->getDDRowByArr('Content Type', 'content_type', $fnMod->getContentRecordTypeArray(), 'Record')}
        {$tabsDD}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $mediaArrayObj = Zend_Registry::get('mediaArrayObj');
        $mediaArray = Zend_Registry::get('mediaArray');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('content_history', 'content_history_id', $id);

        $fnMod = includeCPClass('ModuleFns', 'webBasic_contentHistory');
        $formAction = "index.php?_spAction=save&lnkRoom={$tv['lnkRoom']}&showHTML=0";

        $tabsDD = '';

        if ($cpCfg['m.webBasic.content.hasTab'] == 1){
            $sqlCombo = "
            SELECT tab_content_id
                  ,title
            FROM tab_content
            WHERE record_id = {$row['record_id']}
              AND room_name = 'content'
            ORDER BY title
            ";

            $tabsDD = "
            {$formObj->getDDRowBySQL('Tab', 'tab_content_id', $sqlCombo, $row['tab_content_id'])}
            ";
        }

        $fieldset1 = "
        {$formObj->getTBRow('Title', 'title_hist', $ln->gfv($row, 'title', '0'))}
        {$formObj->getDDRowByArr('Content Type', 'content_type', $fnMod->getContentRecordTypeArray(), $row['content_type'])}
        {$tabsDD}
        {$formObj->getYesNoRRow('Published', 'published', $row['published'])}
        {$formObj->getTBRow('Sort Order', 'sort_order', $row['sort_order'])}
        ";

        $fieldset2 = $formObj->getHTMLEditor('Description', 'description_hist', $ln->gfv($row, 'description', '0'));

        $fields = "
        {$formObj->getFieldSetWrapped('Content Details', $fieldset1)}
        {$formObj->getFieldSetWrapped('Description', $fieldset2)}
        ";

        $mediaObj = $mediaArrayObj->getMediaObj('contentHistory', 'histPic', 'image');

        $mediaArrayObj->registerMedia($mediaObj, array(
        ));

        $mediaArray = $mediaArrayObj->mediaArray;

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$fields}
            <input type='hidden' name='content_history_id' value='{$id}' />
        </form>
        {$media->getRightPanelMediaDisplay('Picture', 'contentHistory', 'histPic', $row)}
        ";

        return $text;
    }
}
