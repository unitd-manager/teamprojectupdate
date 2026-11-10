<?
class CP_Admin_Modules_Pos_Valuelist_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $rowCounter = 0;

    function getList($dataArray, $mode = 'Global'){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        foreach ($cpCfg['m.pos.valuelist.recordTypeArr'] AS $vlKey => $val){
            $SQL = "
            SELECT * 
            FROM valuelist
            WHERE key_text = '{$vlKey}'
            ORDER BY value
            ";
            $result = $db->sql_query($SQL);

            $dataArray = $dbUtil->getResultsetAsArray($result);
            
            $rows .= "
            <div class='outer' group='{$vlKey}'>
                <div class='header' onClick=\"document.location='#{$vlKey}'\">
                    {$val}
                </div>
                <div class='pane'>
                    {$this->getList2($dataArray)}
                </div>
            </div>
            ";
        }
        
        $text = "
        <div id='settings'>
            {$rows}
        </div>
        ";

        $currentId = getReqParam('currentId', '', true);
        if ($currentId != ''){
            $rec = $fn->getRecordRowByID('valuelist', 'valuelist_id', $currentId);
            $group = $rec['key_text'];
            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                var parent = $(\"#settings div.outer[group='{$group}']\");
                $('.pane', parent).show();
            "));
        }
        
        return $text;
    }

    function getList2($dataArray){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows  = "";
        $value = "";
        $vlArray     = $cpCfg['m.pos.valuelist.recordTypeArr'];
        $lnArray     = $cpCfg['cp.availableLanguages'];
        $vlArrayKeys = array_keys($vlArray);

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $rows .= "
            {$listObj->getListRowHeader($row, $this->rowCounter, '', array('hasRowNumber' => false, 'hasEditInList' => false))}
            <td width='10'>
                <a class='editFromList' dialogTitle=\"Edit - {$row['description']}\" href='javascript:void(0);' w='600' h=500 link='{$fn->getEditFromListUrl($row)}'>
                    <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_field.jpg' border='0'>
                </a>
            </td>
            {$listObj->getListDataCell($row['code'])}
            {$listObj->getListDataCell($row['value'])}
            {$listObj->getListDataCell($row['description'])}
            <td width='10'>
                <a class='delFromList' dialogTitle=\"Edit - {$row['description']}\" href='javascript:void(0);' w='600' h=500 recId='{$row['valuelist_id']}'>
                    <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/delete_s.png' border='0'>
                </a>
            </td>
            {$listObj->getListRowEnd($row['valuelist_id'])}
            ";
            $this->rowCounter++ ;
        }

        $textValue = '';
        foreach ($lnArray as $key => $value) {
            $fieldName = $ln->getFieldPrefix($key) . 'value';
            $textValue .= $listObj->getListHeaderCell("Text ({$value})", "v.{$fieldName}");
        }

        $text = "
        {$listObj->getListHeader(array('hasRowNumber' => false, 'hasEditInList' => false))}
        <th></th>
        {$listObj->getListHeaderCell('Code')}
        {$listObj->getListHeaderCell('Value')}
        {$listObj->getListHeaderCell('Description')}
        <th></th>
        {$listObj->getListHeaderEnd()}
        {$rows}
        </tbody>
        </table>
        </form>
        ";
                
        return $text;
    }


    /**
     *
     */
    function getNew(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');

        $key_text = $fn->getReqParam('key_text');
        $exp = array('useKey' => 1);

        $vlArray  = $cpCfg['m.pos.valuelist.recordTypeArr'];
        asort($vlArray);

        $fieldset = "
        {$formObj->getDDRowByArr('Value List Name', 'key_text', $vlArray, $key_text, $exp)}
        {$formObj->getTBRow('Value', 'value')}
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
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $formObj = Zend_Registry::get('formObj');
        $ln = Zend_Registry::get('ln');

        $exp = array('useKey' => 1);
        $vlArray  = $cpCfg['m.pos.valuelist.recordTypeArr'];
        asort($vlArray);

        $fieldset1  = "
        {$formObj->getDDRowByArr('Value List Name', 'key_text', $vlArray, $row['key_text'], $exp)}
        {$formObj->getTBRow('Code' , 'code', $row['code'])}
        {$formObj->getTARow('Value', 'value', $ln->gfv($row, 'value', '0'))}
        {$formObj->getTARow('Description' , 'description', $row['description'])}
        {$formObj->getTBRow('Sort Order' , 'sort_order', $row['sort_order'])}
		";

        $text = "
        {$formObj->getFieldSetWrapped('Valuelist Details', $fieldset1)}
        {$formObj->getCreationModificationText($row)}
        ";

        return $text;
    }

    /**
     *
     */
    function getShowValuesInModal() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        $key_text = $fn->getReqParam('key_text');

        $SQL = "
        SELECT value
        FROM valuelist
        WHERE key_text = '{$key_text}'
        ORDER BY value
                ,sort_order
        ";
        $result  = $db->sql_query($SQL);

		$rows = '';

        while ($row = $db->sql_fetchrow($result)) {
            $rows .= "
			<tr class='even'>
                <td class='value'>{$row['value']}</td>
                <td><input type='button' value='Set' class='set_{$key_text} w50'/></td>
			</tr>
			";
        }

        $text = "
        <div class='valuelist-selection'>
            <table class='list'>
                {$rows}
            </table>
        </div>
		";

        return $text;
    }

    /**
     *
     */
    function getEditFromList() {
        $formObj = Zend_Registry::get('formObj');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $id = $fn->getReqParam('id');
        $row = $fn->getRecordRowByID('valuelist', 'valuelist_id', $id);

        $formAction = "index.php?_spAction=saveFromList&module={$tv['module']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$this->getEdit($row)}
            <input type='hidden' name='valuelist_id' value='{$id}' />
        </form>
        ";

        return $text;
    }

}