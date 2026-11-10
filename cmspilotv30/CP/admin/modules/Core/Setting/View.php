<?
class CP_Admin_Modules_Core_Setting_View extends CP_Common_Lib_ModuleViewAbstract
{
    function getList($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $rows  = "";
        $value = "";

        $rowCounter = 0;

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){

            if ($row['value_type'] == "Yes No"){
                $value .= $listObj->getListDataCell($fn->getYesNo($row['value']));
            } else{
                $value .= $listObj->getListDataCell($row['value'] );
            }

            $rows .= "
            {$listObj->getListRowHeader($row, $rowCounter)}
            {$listObj->getGoToDetailText($rowCounter, $row['key_text'])}
            {$listObj->getListDataCell($row['description'])}
            {$listObj->getListDataCell($row['value'])}
            {$listObj->getListDataCell($row['setting_id'], 'center')}
            {$listObj->getListRowEnd($row['setting_id'])}
            ";
            $rowCounter++;
        }

        $text = "
        {$listObj->getListHeader()}
        {$listObj->getListHeaderCell($ln->gd('m.core.setting.lbl.key', 'Key'), 'a.key_text')}
        {$listObj->getListHeaderCell($ln->gd('m.core.setting.lbl.description', 'Description'), 'a.description')}
        {$listObj->getListHeaderCell($ln->gd('m.core.setting.lbl.value', 'Value'), 'a.value')}
        {$listObj->getListHeaderCell($ln->gd('m.core.setting.lbl.id', 'ID'), 'a.setting_id', 'headerCenter')}
        {$listObj->getListHeaderEnd()}
        {$rows}
        {$listObj->getListFooter()}
        ";

        return $text;
    }

    /**
     *
     */
    function getNew(){
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $fieldset = "
        {$formObj->getTBRow($ln->gd('m.core.setting.lbl.keyText', 'Key Text'), 'key_text')}
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

        if ($row['value_type'] == 'Yes No'){
            $value = $formObj->getYesNoRRow($ln->gd('m.core.setting.lbl.value', 'Value'), 'value', $row['value']);
        } else if ($row['value_type'] == 'Text Field'){
            $value = $formObj->getTBRow($ln->gd('m.core.setting.lbl.value', 'Value'), 'value', $row['value']);
        } else if ($row['value_type'] == 'Number Field'){
            $value = $formObj->getTBRow($ln->gd('m.core.setting.lbl.value', 'Value'), 'value', $row['value']);
        } else{
            $value = $formObj->getTARow($ln->gd('m.core.setting.lbl.value', 'Value'), 'value', $row['value']);
        }

        $fnMod = includeCPClass('ModuleFns', 'core_setting');

        $fielset1 = "
        {$formObj->getTBRow($ln->gd('m.core.setting.lbl.key', 'Key'), 'key_text', $row['key_text'])}
        {$formObj->getTBRow($ln->gd('m.core.setting.lbl.description', 'Description'), 'description', $row['description'])}
        {$value}
        {$formObj->getDDRowByArr($ln->gd('m.core.setting.lbl.valueType', 'Value Type'), 'value_type', $fnMod->getSettingsValueTypeArray(), $row['value_type'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped($ln->gd('m.core.setting.lbl.settingDetails', 'Setting Details'), $fielset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
    }

    /**
     *
     */
    function getQuickSearch() {
    }
}