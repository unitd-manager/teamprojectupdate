<?
class CP_Admin_Modules_Pos_GlobalSettings_View extends CP_Common_Lib_ModuleViewAbstract
{
    var $rowCounter = 0;

    function getList($dataArray, $mode = 'Global'){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $rows = '';

        foreach ($cpCfg['m.pos.globalSettings.groups'][$mode] AS $groupKey => $arr){
            $SQL = "
            SELECT *
            FROM setting s
            WHERE group_name = '{$groupKey}'
              AND mode = '{$mode}'
            ORDER BY setting_id
            ";
            $result = $db->sql_query($SQL);
            $dataArray = $dbUtil->getResultsetAsArray($result);
            $flds = $arr['flds'];

            $rows .= "
            <div class='outer' group='{$groupKey}'>
                <div class='header' onClick=\"document.location='#{$groupKey}'\">
                    {$groupKey}
                </div>
                <div class='pane'>
                    {$this->getList2($dataArray, $flds)}
                </div>
            </div>
            ";
        }

        $currentId = getReqParam('currentId', '', true);
        if ($currentId != ''){
            $rec = $fn->getRecordRowByID('setting', 'setting_id', $currentId);
            $group = $rec['group_name'];
            CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
                var parent = $(\"#settings div.outer[group='{$group}']\");
                $('.pane', parent).show();
            "));
        }

        $text = "
        <div id='settings'>
            {$rows}
        </div>
        ";

        return $text;
    }

    function getList2($dataArray, $fldsArr){
        $listObj = Zend_Registry::get('listObj');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $session_user_group_id = isset($_SESSION['userGroupID']) ? $_SESSION['userGroupID']  : false;

        $rows  = "";
        $value = "";
        $user_group = $fn->getRecordRowByID('user_group', 'user_group_id', $session_user_group_id);

        //--------------------------------------------------------------------------//
        foreach ($dataArray as $row){
            $flds = '';
            $count = '';
            foreach ($fldsArr AS $fld){
                $fldKey = $cpCfg['m.pos.globalSettings.flds'][$fld]['fldKey'];

                $value = $row[$fldKey];

                if ($fldKey == 'value'){
                    if ($row['value_type'] == "Yes or No"){
                        $value = $fn->getYesNo($value);

                    } else  if ($row['value_type'] == "Enable or Disable"){
                        $arr = array(1 => 'Enable', 0 => 'Disable');
                        $value = $arr[$value];
                    }
                }

                if ($fldKey == 'add_shop_code' || $fldKey == 'add_separator' || $fldKey == 'reset_next_year'){
                    $value = $fn->getYesNo($value);
                }

                if ($fldKey == 'auto_generate_no'){
                    $arr = array(1 => 'Yes', 0 => 'No', 2 => 'When Blank');
                    $value = $arr[$value];
                }

                    $flds .= $listObj->getListDataCell($value);
            }
            $true_false = '';
            $pos = '';
            $pos1 = '';
            if ($user_group['item_discount'] == 0){
                $pos = strpos($flds, 'Item Discount');
                $true_false = false;
            }
            if ($user_group['overall_item_discount'] == 0){
                $pos1 = strpos($flds, 'Overall Invoice Discount');
                $true_false = false;
            }

            if($pos == $true_false){
                if($pos1 == $true_false){
                    $rows .= "
                    {$listObj->getListRowHeader($row, $this->rowCounter, '', array('hasRowNumber' => false, 'hasEditInList' => false))}
                    <td width='10'>
                        <a class='editFromList' dialogTitle=\"Edit - {$row['description']}\" href='javascript:void(0);' w='600' h=500 link='{$fn->getEditFromListUrl($row)}'>
                            <img src='{$cpCfg['cp.masterImagesPathAlias']}icons/edit_field.jpg' border='0'>
                        </a>
                    </td>
                    {$flds}
                    <td width='10'>
                        <a class='delFromList' dialogTitle=\"Edit - {$row['description']}\" href='javascript:void(0);' w='600' h=500 recId='{$row['setting_id']}'>
                            <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/delete_s.png' border='0'>
                        </a>
                    </td>
                    {$listObj->getListRowEnd($row['setting_id'])}
                    ";
                }
            }
            $this->rowCounter++;
        }

        $flds = '';
        foreach ($fldsArr AS $fld){
            $title = $cpCfg['m.pos.globalSettings.flds'][$fld]['title'];
            $flds .= $listObj->getListHeaderCell($title);
        }

        $text = "
        {$listObj->getListHeader(array('hasRowNumber' => false, 'hasEditInList' => false))}
        <th></th>
        {$flds}
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
        $formObj = Zend_Registry::get('formObj');

        $fieldset = "
        {$formObj->getTBRow('Key Text', 'key_text')}
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
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $formObj = Zend_Registry::get('formObj');

        $formObj->mode = $tv['action'];

        $arr = array();

        foreach($cpCfg['m.pos.globalSettings.groups'][$row['mode']] AS $key => $val){
            $arr[] = $key;
        }

        $masterDetails = '';

        if ($fn->isDeveloper()){
            $fieldset1 = "
            {$formObj->getTBRow('Key', 'key_text', $row['key_text'])}
            {$formObj->getTBRow('Description', 'description', $row['description'])}
            {$formObj->getDDRowByArr('Group', 'group_name', $arr, $row['group_name'])}
            {$formObj->getDDRowByArr('Value Type', 'value_type', $cpCfg['m.pos.globalSettings.valueTypeArr'], $row['value_type'])}
    		";

            $masterDetails = $formObj->getFieldSetWrapped('Master Details', $fieldset1);

        }

        $fldsArr = array();
        if ($row['group_name'] != ''){
            $fldsArr = $cpCfg['m.pos.globalSettings.groups'][$row['mode']][$row['group_name']]['flds'];
        }

        $value = '';
        $arr = array();
        foreach ($fldsArr AS $fld ){
            $fldLabel = $cpCfg['m.pos.globalSettings.flds'][$fld]['title'];
            $fldKey = $cpCfg['m.pos.globalSettings.flds'][$fld]['fldKey'];

            if ($fldKey == 'value'){
                if ($row['value_type'] == 'Yes or No'){
                    $value .= $formObj->getYesNoRRow($fldLabel, 'value', $row['value']);

                } else if ($row['value_type'] == 'Enable or Disable'){
                    $arr = array(1 => 'Enable', 0 => 'Disable');
                    $value .= $formObj->getRRow($fldLabel, $fldKey, $row[$fldKey], $arr, array('useKey' => 1, 'rowCls' => 'yesNo'));

                } else if ($row['value_type'] == 'Print or Not Print'){
                    $arr = array(1 => 'Print', 0 => 'Not Print');
                    $value .= $formObj->getRRow($fldLabel, $fldKey, $row[$fldKey], $arr, array('useKey' => 1, 'rowCls' => 'yesNo'));

                } else if ($row['value_type'] == 'Text Area'){
                    $value .= $formObj->getTARow($fldLabel, 'value', $row['value']);

                } else if ($row['value_type'] == 'Date Format'){
                    $arr = array(
                         'YYYY-MM-DD'
                        ,'YYYY-MMM-DD'
                        ,'YYYY.MM.DD'
                        ,'YYYY.MMM.DD'
                    );
                    $value .= $formObj->getDDRowByArr($fldLabel, 'value', $arr, $row['value']);

                } else if ($row['value_type'] == 'Time Format'){
                    $arr = array(
                         '24 hrs'
                        ,'AM/PM'
                    );
                    $value .= $formObj->getDDRowByArr($fldLabel, 'value', $arr, $row['value']);

                } else if ($row['value_type'] == 'Currency'){
                    $SQL = "
                    SELECT code
                    FROM currency
                    ORDER BY code
                    ";

                    $value .= $formObj->getDDRowBySQL($fldLabel, 'value', $SQL, $row['value'], array('sqlType' => 'OneField'));

                } else if ($row['value_type'] == 'Cost Method'){
                    $arr = array(
                         'fifo' => 'FIFO (First In First Out)'
                        ,'avg' => 'Average Cost'
                        ,'filo' => 'FILO (First In Last Out)'
                    );
                    $value .= $formObj->getDDRowByArr($fldLabel, 'value', $arr, $row['value'], array('useKey' => true));

                } else if ($row['value_type'] == 'Operator'){
                    $arr = array(
                         '+'
                        ,'-'
                        ,'*'
                        ,'/'
                    );
                    $value .= $formObj->getDDRowByArr($fldLabel, 'value', $arr, $row['value']);

                } else if ($row['value_type'] == 'Rounding'){
                    $arr = array(
                         'None'
                        ,'Round Up'
                        ,'Round Down'
                        ,'Round Off'
                    );
                    $value .= $formObj->getDDRowByArr($fldLabel, 'value', $arr, $row['value']);

                } else if ($row['value_type'] == 'Multi Payments'){
                    $arr = array(
                         '1'
                        ,'2'
                        ,'3'
                        ,'4'
                    );
                    $value .= $formObj->getDDRowByArr($fldLabel, 'value', $arr, $row['value']);

                } else if ($row['value_type'] == 'Days'){
                    for ($i = 1; $i <= 31; $i++){
                        $arr[] = $i;
                    }

                    $value .= $formObj->getDDRowByArr($fldLabel, 'value', $arr, $row['value']);

                } else if ($row['value_type'] == 'Hours'){
                    for ($i = 1; $i <= 24; $i++){
                        $arr[] = $i;
                    }

                    $value .= $formObj->getDDRowByArr($fldLabel, 'value', $arr, $row['value']);

                } else if ($row['value_type'] == 'Minutes'){
                    for ($i = 0; $i <= 59; $i++){
                        $arr[] = $i;
                    }

                    $value .= $formObj->getDDRowByArr($fldLabel, 'value', $arr, $row['value']);

                } else{
                    $value .= $formObj->getTBRow($fldLabel, 'value', $row['value']);
                }

            } else if ($fldKey == 'add_shop_code' || $fldKey == 'add_separator' || $fldKey == 'reset_next_year'){
                $value .= $formObj->getYesNoRRow($fldLabel, $fldKey, $row[$fldKey]);

            } else if ($fldKey == 'auto_generate_no'){
                $arr = array(1 => 'Yes', 0 => 'No', 2 => 'When Blank');
                $value .= $formObj->getRRow($fldLabel, $fldKey, $row[$fldKey], $arr, array('useKey' => 1, 'rowCls' => 'yesNo'));

            } else if ($fldKey == 'description'){
                // do nothing
            } else {
                $value .= $formObj->getTBRow($fldLabel, $fldKey, $row[$fldKey]);
            }
        }

        $fieldset2 = "
        {$value}
		";

        $text = "
        {$masterDetails}
        {$formObj->getFieldSetWrapped('Values', $fieldset2)}
        {$formObj->getCreationModificationText($row)}
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
        $row = $fn->getRecordRowByID('setting', 'setting_id', $id);

        $sqlStatus  = $fn->getValueListSQL('taskStatus');

        $formAction = "index.php?_spAction=saveFromList&module={$tv['module']}&showHTML=0";
        $exp = array('sqlType' => 'OneField');

        $text = "
        <form id='portalForm' class='yform columnar' method='post' action='{$formAction}'>
            {$this->getEdit($row)}
            <input type='hidden' name='setting_id' value='{$id}' />
        </form>
        ";

        return $text;
    }

    /**
     *
     */
    function getRightPanel($row){
    }
}