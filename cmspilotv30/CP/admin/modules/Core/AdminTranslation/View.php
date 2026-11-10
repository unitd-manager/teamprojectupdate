<?
class CP_Admin_Modules_Core_AdminTranslation_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $jssKeys = array('jqUiTableEdit-0.6.1');

    /**
     *
     */
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');

        $lnArray = $cpCfg['cp.availableLanguages'];

        $rows  = "";
        $buttons = "";
        $textValue = "";

        $rowCounter = 0;
        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $buttons = '';
            foreach ($lnArray as $key => $value){
                $fieldValueOrig = $ln->gfv($row, "value", 0, $key);
                $fieldValue    = substr(strip_tags($fieldValueOrig) , 0, 100) . "";
                $fieldValueHid = htmlspecialchars($fieldValueOrig);

                $rec_id = $row['admin_translation_id'];
                $buttons .= "
                <td langKey='{$key}' class='translation' rec_id='{$row['admin_translation_id']}'>
                    <div class='display'>{$fieldValue}</div>
                    <div class='btns'>
                        <button type='button' class='saveTrans'>Save</button>
                        <button type='button' class='cancelTrans'>Cancel</button>
                    </div>
                    <input type='hidden' id='prev__{$rec_id}__{$key}' value='{$fieldValueHid}' />
                </td>
                ";
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['key_text'])}
            {$buttons}
            {$listObj->getListDataCell($row['admin_translation_id']  , "center")}
            {$listObj->getListRowEnd($row['admin_translation_id'])}
            ";
            $rowCounter++ ;
        }

        foreach ($lnArray as $key => $value){
            $fieldName = $ln->getFieldPrefix($key). 'value';
            $textValue .= $listObj->getListHeaderCell("Text ({$value})", "at.{$fieldName}");
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.core.adminTranslation.lbl.key', 'Key'), 'at.key_text')}
        {$textValue}
        {$listObj->getListHeaderCell($ln->gd('m.core.adminTranslation.lbl.id', 'ID'), 'at.admin_translation_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
      	";

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array('cpm.core.adminTranslation.enableEditFunctions()'));
        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $key_text = $fn->getReqParam('key_text');

        $exp = array('useKey' => 1);

        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.core.adminTranslation.lbl.keyText', 'Key Text'), 'key_text')}
        ";

        $text = "
        {$formObj->getFieldSetWrapped('Key Details', $fieldset)}
        ";

        return $text;
    }

    /**
     *
     */
    function getEdit($row){
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $formObj->mode = $tv['action'];

        $fnMod = includeCPClass('ModuleFns', 'core_adminTranslation');
        $arr = $fnMod->getAdminTranslationGroupArray();

        $fielset1  = "
        {$formObj->getTBRow($ln->gd('m.core.adminTranslation.lbl.key', 'Key'), 'key_text', $row['key_text'])}
		";

        $fielset2  = "
        {$formObj->getTARow($ln->gd('m.core.adminTranslation.lbl.value', 'Value'), 'value', $ln->gfv($row, 'value', '0'), array('changeNlToBr' => false))}
		";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.core.adminTranslation.lbl.adminTranslationDetails', 'AdminTranslation Details'), $fielset1)}
        {$formObj->getFieldSetWrapped($ln->gd('m.core.adminTranslation.lbl.value', 'Value'), $fielset2)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getQuickSearch() {
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');

        $text = "
        <td>
            <a href='index.php?_spAction=updateLangFiles&_topRm={$tv['topRm']}'>Update Configuration Files</a>
        </td>
        ";
        $text = '';

        return $text;
    }

}