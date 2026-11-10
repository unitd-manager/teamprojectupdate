<?
/**
 *
 */
class CP_Common_Lib_FormObj
{

    var $mode = 'edit';
    var $expNoEdit = array('isEditable' => 0);
    var $checkBoxYNDispArr = array('1' => 'Yes', '0' => 'No', '' => 'No');
    var $ynArr = array(1 => 'Yes', 0 => 'No');
    var $expNumType = array('htmlFldType' => 'number');
    var $expOneFld = array('sqlType' => 'OneField');
    var $expHideFirstOpt = array('hideFirstOption' => 1);
    var $expLabelLeft = array('isLabelOnLeft' => true);

    function __construct(){
    }

    /**
     *
     */
    function getTextBoxRow($displayTitle, $fieldName, $fieldValue = "", $extraParam = array()){
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $exp = &$extraParam;
        $rowCls        = isset($exp['rowCls'])        ? " {$exp['rowCls']}"                : "";
        $fieldLabelCls = isset($exp['fieldLabelCls']) ? " class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls      = isset($exp['fieldCls'])      ? " class='{$exp['fieldCls']}'"      : "";
        $notesDivCls   = isset($exp['notesDivCls'])   ? " class='{$exp['notesDivCls']}'"   : " class='formFieldNotes'";
        $notesRightCls = isset($exp['notesRightCls']) ? " class='{$exp['notesRightCls']}'" : " class='formFieldNotesRight'";
        $notes         = isset($exp['notes'])         ? $exp['notes']        : '';
        $notesRight    = isset($exp['notesRight'])    ? $exp['notesRight']   : '';
        $inputType     = isset($exp['password'])      ? "password"           : "text";
        $fldType       = isset($exp['fldType'])       ? $exp['fldType']      : "text";
        $htmlFldType   = isset($exp['htmlFldType'])   ? $exp['htmlFldType']  : "text";
        $extraHtml     = isset($exp['extraHtml'])     ? $exp['extraHtml']    : "";
        $autoFormat    = isset($exp['autoFormat'])    ? $exp['autoFormat']   : 0;
        $tooltip       = isset($exp['tooltip'])       ? $exp['tooltip']      : '';
        $required      = isset($exp['required'])      ? $exp['required']     : false;
        $placeholder   = isset($exp['placeholder'])   ? $exp['placeholder']  : '';
        $charsLimit    = isset($exp['charsLimit'])    ? $exp['charsLimit']   : '';
        $disableAutoComplete = isset($exp['disableAutoComplete'])? $exp['disableAutoComplete'] : false;
        $urliseContent = isset($exp['urliseContent']) ? $exp['urliseContent']   : false;
        $fldPrefix     = isset($exp['fldPrefix'])     ? $exp['fldPrefix']   : '';
        $useLabelFor   = isset($exp['useLabelFor'])   ? $exp['useLabelFor'] : true;
        $fldId         = isset($exp['fldId'])   ? $exp['fldId'] : "fld_{$fieldName}";
        $detailValue   = isset($exp['detailValue']) ? $exp['detailValue'] : '';
        $tabIndex      = isset($exp['tabindex']) ? "tabindex={$exp['tabindex']}" : '';

        $disabled      = isset($exp['disabled'])      ? $exp['disabled']                   : false;
        $disabled      = ($disabled == true)          ? " disabled='{$disabled}'"          : "";

        $autoSgstModule    = isset($exp['autoSgstModule'])   ? $exp['autoSgstModule']   : '';
        $autoSgstSrchFld   = isset($exp['autoSgstSrchFld'])  ? $exp['autoSgstSrchFld']  : '';
        $autoSgstCallBack  = isset($exp['autoSgstCallBack']) ? $exp['autoSgstCallBack'] : '';
        $autoSgstActualFld = isset($exp['autoSgstActualFld']) ? $exp['autoSgstActualFld'] : '';
        $autoSgstActualFldVal = isset($exp['autoSgstActualFldVal']) ? $exp['autoSgstActualFldVal'] : '';

        $htmlFldType = $inputType == 'password' ? 'password' : $htmlFldType;

        $fieldCls2 = '';
        if ($fldType == 'time'){
            $fieldCls2 = ($fieldCls != '') ? " fld_time" : " class='fld_time'";
        }

        if ($fldType == 'datetime'){
            $fieldCls2 = ($fieldCls != '') ? " fld_datetime" : " class='fld_datetime'";
        }

        $fieldKeypressEvent = '';
        if ($fldType == 'number'){
            $fieldKeypressEvent = "onkeypress='return Util.isNumberKey(event)'";
        }

        if ($fldType == 'decimal'){
            $fieldKeypressEvent = "onkeypress='return Util.isDecimalKey(this, event)'";
        }

        $charsLimitText = '';
        if ($charsLimit) { //charsLimit specified
            $charsLimitText = " maxlength='{$charsLimit}'";
        }

        if ($this->mode == 'detail'){
            $isEditable = 0;
        } else if (isset($exp['isEditable'])){
            $isEditable = $exp['isEditable'];
        } else {
            $isEditable = 1;
        }

        $editCls = $isEditable ? ' editable' : ' non-editable';

        $requiredText = '';
        $requiredFldText = '';
        if ($required && $isEditable == 1) {
            $requiredText = $this->getRequiredText();
            $requiredFldText = "required='require'  aria-required='true'";
        }

        if ($notes != ""){
           $notes = "<div{$notesDivCls}>{$notes}</div>";
        }

        if ($notesRight != ""){
           $notesRight = "<div{$notesRightCls}>{$notesRight}</div>";
        }

        if($tooltip != ''){
            $tooltip = " title='{$tooltip}'";
        }

        $autoCompleteText = '';
        if($disableAutoComplete){
            $autoCompleteText = " autocomplete='off'";
        }

        $notesSpan = '';
        if ($extraHtml != "" || $notes != '' || $notesRight != ''){
           $notesSpan = "<span>{$extraHtml}{$notes}{$notesRight}</span>";
        }
        if ($fldPrefix != ''){
           $fldPrefix = "<span class='fld-prefix'>{$fldPrefix}</span>";
        }

        if($placeholder != '') {
            $placeholder = "placeholder='{$placeholder}'";
        }

        $text = "";
        $urlImageHref = '';

        if($isEditable == 1){
            $fieldValue = $cpUtil->replaceForFormField($fieldValue);
            $fieldValueUrl = $fieldValue;
            if (strpos($fieldValue, 'http://') === false) {
                $fieldValueUrl = 'http://www.' . $fieldValue;
            }
            if ($urliseContent) {
                $image = "
                <img src='{$cpCfg['cp.commonImagesPathAlias']}icons/open_url_external.gif'
                title='Open URL externally'>
                ";
                $urlImageHref = "<a href='{$fieldValueUrl}' target='_blank'>{$image}</a>";
            }

            $autoSgstTxt = '';
            if ($autoSgstModule !=''){
                $autoSgstTxt = " cpAutoSuggest='1' autoSgstModule='{$autoSgstModule}' autoSgstActualFld='{$autoSgstActualFld}'
                autoSgstSrchFld='{$autoSgstSrchFld}' autoSgstCallBack='{$autoSgstCallBack}'";
            }

            if ($autoSgstActualFld != ''){
                $autoSgstActualFld = "<input type='hidden' name='{$autoSgstActualFld}' value='{$autoSgstActualFldVal}'>";
            }

            $fieldValueTemp =
            $fldPrefix .
            "<input{$fieldCls}{$fieldCls2} type='{$htmlFldType}' name='{$fieldName}' class='text' id='{$fldId}' " .
            "value=\"{$fieldValue}\"{$charsLimitText}{$tooltip}{$autoCompleteText}{$autoSgstTxt} {$requiredFldText} {$tabIndex} {$disabled} {$fieldKeypressEvent} {$placeholder}/>
            {$urlImageHref}
            {$notesSpan}
            {$autoSgstActualFld}
            ";
        } else {
            if ($autoFormat == 1){
                $fieldValue = $this->getFormattedValue($fieldValue);
            }
            if ($urliseContent) {
                $fieldValue = "<a href='{$fieldValue}' target='_blank'>{$fieldValue}</a>";
            }

            $fieldValue = ($detailValue != '') ? $detailValue : $fieldValue;
            $txt = ($fieldValue != '') ? $fieldValue : '&nbsp;';

            $fieldValueTemp = "
            <div class='txt' id='{$fldId}'>
                {$fldPrefix}
                <span class='value'>{$txt}</span>
                {$notesSpan}
            </div>
            ";
        }

        $clsName = "row_{$fieldName}";

        $fldPrefixClass = $fldPrefix != '' ? ' has-fld-prefix' : '';
        $labelFor = '';
        if ($useLabelFor) {
            $labelFor = " for='fld_{$fieldName}'";
        }
        $text = "
        <div class='type-text ym-fbox-text {$clsName}{$rowCls}{$editCls}{$fldPrefixClass}'>
            <label{$fieldLabelCls}{$labelFor}>{$displayTitle}{$requiredText}</label>
            {$fieldValueTemp}
        </div>
        ";

        return $text;
    }

    function getTBRow($displayTitle, $fieldName, $fieldValue = "", $extraParam = array()){
        return $this->getTextBoxRow($displayTitle, $fieldName, $fieldValue, $extraParam);
    }

    /**
     *
     * for containing other fields in case of a multi column row
     */
    function getFieldContainerRow($displayTitle, $fldTextArr, $extraParam = array()){
        $cpUtil = Zend_Registry::get('cpUtil');
        $exp = &$extraParam;
        $rowCls        = isset($exp['rowCls'])        ? " {$exp['rowCls']}"                : "";
        $fieldLabelCls = isset($exp['fieldLabelCls']) ? " class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls      = isset($exp['fieldCls'])      ? " class='{$exp['fieldCls']}'"      : "";
        $notesDivCls   = isset($exp['notesDivCls'])   ? " class='{$exp['notesDivCls']}'"   : " class='formFieldNotes'";
        $notesRightCls = isset($exp['notesRightCls']) ? " class='{$exp['notesRightCls']}'" : " class='formFieldNotesRight'";
        $notes         = isset($exp['notes'])         ? $exp['notes']        : '';
        $notesRight    = isset($exp['notesRight'])    ? $exp['notesRight']   : '';
        $inputType     = isset($exp['password'])      ? "password"           : "text";
        $fldType       = isset($exp['fldType'])       ? $exp['fldType']      : "text";
        $extraHtml     = isset($exp['extraHtml'])     ? $exp['extraHtml']    : "";
        $autoFormat    = isset($exp['autoFormat'])    ? $exp['autoFormat']   : 0;
        $tooltip       = isset($exp['tooltip'])       ? $exp['tooltip']      : '';
        $required      = isset($exp['required'])      ? $exp['required']     : false;
        $charsLimit    = isset($exp['charsLimit'])    ? $exp['charsLimit']   : '';
        $disableAutoComplete = isset($exp['disableAutoComplete'])? $exp['disableAutoComplete'] : false;

        $requiredText = '';
        $requiredFldText = '';
        if ($required && $isEditable == 1) {
            $requiredText = $this->getRequiredText();
            $requiredFldText = "required='require'  aria-required='true'";
        }

        $fldTexts = '';
        foreach ($fldTextArr as $fldText) {
            $fldTexts .= "
            <div class='float_left'>
            {$fldText}\n
            </div>
            ";
        }

        $fldsCountCls = 'flds-' . count($fldTextArr);

        $text = "
        <div class='type-text ym-fbox-text {$rowCls} multi-fld-row {$fldsCountCls}'>
            <label{$fieldLabelCls}>{$displayTitle}{$requiredText}</label>
            {$fldTexts}
        </div>
        ";

        return $text;
    }

    function getFCRow($displayTitle, $fldTextArr, $extraParam = array()){
        return $this->getFieldContainerRow($displayTitle, $fldTextArr, $extraParam);
    }


    /**
     *
     * @param <type> $displayTitle
     * @param <type> $fieldName
     * @param <type> $fieldValue
     * @param <type> $extraParam
     * @return <type>
     */
    function getSingleCheckBoxRow($displayTitle, $fieldName, $fieldValue = "", $extraParam = array()){
        $exp = &$extraParam;

        $rowCls         = isset($exp['rowCls'])         ? " {$exp['rowCls']}"        : "";
        $fieldLabelCls  = isset($exp['fieldLabelCls'])  ? " class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls       = isset($exp['fieldCls'])       ? " class='{$exp['fieldCls']}'"      : "";
        $checked        = $fieldValue  == 1             ? " checked='checked'"               : "";
        $disabled       = isset($exp['disabled'])       ? $exp['disabled']                   : false;
        $disabled       = ($disabled == true)           ? " disabled='{$disabled}'"          : "";
        $isLabelOnLeft  = isset($exp['isLabelOnLeft'])  ? $exp['isLabelOnLeft'] : false;
        $tabIndex       = isset($exp['tabindex']) ? "tabindex={$exp['tabindex']}" : '';

        $labelLeftClass = $isLabelOnLeft ? ' label-left' : '';
        $text  = "";
        $text .= "
        <div class='type-check ym-fbox-check yesNo{$rowCls}{$labelLeftClass}'>
            <!-- <span class='ym-label'>{$displayTitle}</span>-->
            <input type='checkbox' name='{$fieldName}' id=\"fld_{$fieldName}\"{$disabled} value='1' {$checked} {$tabIndex}/>
            <label{$fieldLabelCls} for='fld_{$fieldName}'>{$displayTitle}</label>
        </div>
        ";
        return $text;
    }

    /**
     *
     * @param <type> $displayTitle
     * @param <type> $fieldName1
     * @param <type> $fieldName2
     * @param <type> $fieldValue1
     * @param <type> $fieldValue2
     * @param <type> $extraParam
     * @return <type>
     */
    function getPhoneNoRow($displayTitle, $fieldName1, $fieldName2, $fieldValue1 = "", $fieldValue2 = "", $extraParam = array()){
        $exp = &$extraParam;
        $rowCls         = isset($exp['rowCls'])        ? " {$exp['rowCls']}"        : "";
        $fieldLabelCls  = isset($exp['fieldLabelCls']) ? " {$exp['fieldLabelCls']}" : "";
        $fieldCls1      = isset($exp['fieldCls1'])     ? " {$exp['fieldCls1']}"     : "";
        $fieldCls2      = isset($exp['fieldCls2'])     ? " {$exp['fieldCls2']}"     : "";
        $notesDivCls    = isset($exp['notesDivCls'])   ? " {$exp['notesDivCls']}"   : "";
        $notes          = isset($exp['notes'])         ? $exp['notes']              : "";
        $isEditable     = isset($exp['isEditable'])    ? $exp['isEditable']         : 1;

        if ($this->mode == 'detail'){
            $isEditable = 0;
        } else if (isset($exp['isEditable'])){
            $isEditable = $exp['isEditable'];
        } else {
            $isEditable = 1;
        }

        if ($notes != ""){
           $notes = "<div class='{$notesDivCls}'>{$notes}</div>";
        }

        $text = "";

        $text  .= "
        <div class='type-text ym-fbox-text phone-row'>
           <label for='fld_{$fieldName2}'>{$displayTitle}</label>
           {$notes}
        ";

        if($isEditable == 1){
           $text .= "
           <input class='code' id='fld_{$fieldName1}' type='text' name='{$fieldName1}' maxlength='3' value='{$fieldValue1}' />
           <input class='number' id='fld_{$fieldName2}' type='text' name='{$fieldName2}' value='{$fieldValue2}' />
           ";

        } else {
           $code  = ($fieldValue1 != "") ? $fieldValue1 . "-" : $fieldValue1;
           $text .= "<div class='txt'>{$code}{$fieldValue2}</div>";
        }

        $text .= "
        </div>
        ";

        return $text;
    }


    /**
     *
     * @param <type> $displayTitle
     * @param <type> $fieldName1
     * @param <type> $fieldName2
     * @param <type> $fieldValue1
     * @param <type> $fieldValue2
     * @param <type> $extraParam
     * @return <type>
     */
    function getPhoneNoRow2($displayTitle, $fieldName1, $fieldName2, $fieldName3,
                            $fieldValue1 = '', $fieldValue2 = '', $fieldValue3 = '',
                            $extraParam = array()){
        $exp = &$extraParam;
        $rowCls         = isset($exp['rowCls'])        ? " {$exp['rowCls']}"        : "";
        $fieldLabelCls  = isset($exp['fieldLabelCls']) ? " {$exp['fieldLabelCls']}" : "";
        $fieldCls1      = isset($exp['fieldCls1'])     ? " {$exp['fieldCls1']}"     : "";
        $fieldCls2      = isset($exp['fieldCls2'])     ? " {$exp['fieldCls2']}"     : "";
        $notesDivCls    = isset($exp['notesDivCls'])   ? " {$exp['notesDivCls']}"   : "";
        $notes          = isset($exp['notes'])         ? $exp['notes']              : "";
        $isEditable     = isset($exp['isEditable'])    ? $exp['isEditable']         : 1;

        if ($this->mode == 'detail'){
            $isEditable = 0;
        } else if (isset($exp['isEditable'])){
            $isEditable = $exp['isEditable'];
        } else {
            $isEditable = 1;
        }

        if ($notes != ""){
           $notes = "<div class='{$notesDivCls}'>{$notes}</div>";
        }

        $text = "";

        $text  .= "
        <div class='type-text ym-fbox-text phone-row'>
           <label for='fld_{$fieldName2}'>{$displayTitle}</label>
           {$notes}
        ";

        if($isEditable == 1){
           $text .= "
           <input class='country-code' id='fld_{$fieldName1}' type='text' name='{$fieldName1}' maxlength='3' value='{$fieldValue1}' />
           <input class='area-code' id='fld_{$fieldName2}' type='text' name='{$fieldName2}' value='{$fieldValue2}' />
           <input class='number2' id='fld_{$fieldName3}' type='text' name='{$fieldName3}' value='{$fieldValue3}' />
           ";

        } else {
            $arr = array();
            if ($fieldValue1) {
                $arr[] = $fieldValue1;
            }
            if ($fieldValue2) {
                $arr[] = $fieldValue2;
            }
            if ($fieldValue3) {
                $arr[] = $fieldValue3;
            }
            $fieldValue = join(' - ', $arr);

           $text .= "<div class='txt'>{$fieldValue}</div>";
        }

        $text .= "
        </div>
        ";

        return $text;
    }
    /**
     *
     * @param <type> $displayTitle
     * @param <type> $fieldName
     * @param <type> $fieldValue
     * @param <type> $extraParam
     * @return <type>
     */
    function getTextAreaRow($displayTitle, $fieldName, $fieldValue = "", $extraParam = array()){
        $exp = &$extraParam;
        $rowCls            = isset($exp['rowCls'])          ? " {$exp['rowCls']}"                  : "";
        $fieldLabelCls     = isset($exp['fieldLabelCls'])   ? " class='{$exp['fieldLabelCls']}'"   : "";
        $fieldCls          = isset($exp['fieldCls'])        ? " class='{$exp['fieldCls']}'"        : "";
        $notesDivCls       = isset($exp['notesDivCls'])     ? " class='{$exp['notesDivCls']}'"     : " class='formFieldNotes'";
        $notesRightCls     = isset($exp['notesRightCls'])   ? " class='{$exp['notesRightCls']}'"   : " class='formFieldNotesRight'";
        $notes             = isset($exp['notes'])           ? $exp['notes']                        : '';
        $placeholder       = isset($exp['placeholder'])     ? $exp['placeholder']                  : '';
        $notesRight        = isset($exp['notesRight'])      ? $exp['notesRight']                   : '';
        $changeNlToBr      = isset($exp['changeNlToBr'])    ? $exp['changeNlToBr']                 : true;
        $maxLen            = isset($exp['maxLen'])          ? "maxlength='{$exp['maxLen']}'"       : "";
        $rows              = isset($exp['rows'])            ? "rows='{$exp['rows']}'"              : "";
        $required          = isset($exp['required'])        ? $exp['required']                     : false;
        $tabIndex          = isset($exp['tabindex'])        ? "tabindex={$exp['tabindex']}"        : '';

        $requiredText = '';
        $requiredFldText = '';
        if ($required) {
            $requiredText = $this->getRequiredText();
            $requiredFldText = "required='require'  aria-required='true'";
        }

        if ($this->mode == 'detail'){
            $isEditable = 0;
        } else if (isset($exp['isEditable'])){
            $isEditable = $exp['isEditable'];
        } else {
            $isEditable = 1;
        }

        if ($notes != ""){
           $notes = "<div{$notesDivCls}>{$notes}</div>";
        }

        if ($notesRight != ""){
           $notesRight = "<div{$notesRightCls}>{$notesRight}</div>";
        }

        if($placeholder != '') {
            $placeholder = "placeholder='{$placeholder}'";
        }

        if($isEditable == 1){
            $fieldValueTemp = "<textarea {$maxLen} {$rows} {$fieldCls} name='{$fieldName}' id='fld_{$fieldName}' {$requiredFldText} {$tabIndex} {$placeholder}>{$fieldValue}</textarea>";

        } else {
            if ($changeNlToBr){
                $fieldValue = nl2br($fieldValue);
            }
            $txt = ($fieldValue != '') ? $fieldValue : '&nbsp;';
            $fieldValueTemp = "<div class='txt'>{$txt}</div>";
        }

        $clsName = "row_{$fieldName}";

        $text = "
        <div class='type-text ym-fbox-text {$clsName}{$rowCls}'>
            <label{$fieldLabelCls} for='fld_{$fieldName}'>{$displayTitle}{$requiredText}</label>
            {$fieldValueTemp}
            {$notes}{$notesRight}
        </div>
        ";

        return $text;
    }

    function getTARow($displayTitle, $fieldName, $fieldValue = "", $extraParam = array()){
        return $this->getTextAreaRow($displayTitle, $fieldName, $fieldValue, $extraParam);
    }

    /**
     *
     * @param <type> $displayTitle
     * @param <type> $fieldName
     * @param <type> $fieldValue
     * @param <type> $valueArray
     * @param <type> $extraParam
     * @return <type>
     */
    function getRadioArrRow($displayTitle, $fieldName, $fieldValue = "",
                            $valueArray = array(), $extraParam = array()){
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');

        $exp    = &$extraParam;
        $rowCls = isset($exp['rowCls']) ? " {$exp['rowCls']}" : "";
        $rows   = '';
        $text = '';

        $clsName = "row_{$fieldName}";

        if ($this->mode == 'detail'){
            $useKey = $fn->getIssetParam($exp, 'useKey', 0);

            if ($fieldValue != ''){
                if ($useKey == 1){
                    $rows = $valueArray[$fieldValue];
                } else {
                    $rows = $fieldValue;
                }
            }

            return "
            <div class='type-text ym-fbox-text {$clsName}{$rowCls}'>
                <label for='fld_{$fieldName}'>{$displayTitle}</label>
                <div class='txt'>{$rows}</div>
            </div>
            ";

        } else {
            $rows = $cpUtil->getRadioFromArray($valueArray, $fieldName, $fieldValue, $extraParam);
        }


        if ($displayTitle != ""){
            $clsName = "row_{$fieldName}";
            $text = "
            <div class='type-radio form-row-wrapper{$rowCls} {$clsName}'>
                <div class='leftCol'>
                    <label for='fld_{$fieldName}'>{$displayTitle}</label>
                </div>
                <div class='rightCol'>
                    {$rows}
                </div>
            </div>
            ";
        }

        return $text;
    }

    function getRRow($displayTitle, $fieldName, $fieldValue = "", $valueArray = array(), $extraParam = array()) {
        return $this->getRadioArrRow($displayTitle, $fieldName, $fieldValue, $valueArray, $extraParam);
    }

    function getRRowBySQL($displayTitle, $fieldName, $SQL, $fieldValue = "", $extraParam = array()) {
        $dbUtil = Zend_Registry::get('dbUtil');

        $valueArray = $dbUtil->getArrayFromSQLForVL($SQL);
        return $this->getRadioArrRow($displayTitle, $fieldName, $fieldValue, $valueArray, $extraParam);
    }

    function getYesNoRRow($displayTitle, $fieldName, $fieldValue = '', $exp = array()) {
        $yesText = isset($exp['yesText']) ? $exp['yesText'] : 'Yes';
        $noText = isset($exp['noText']) ? $exp['noText'] : 'No';
        $arr = array(1 => $yesText, 0 => $noText);

        $rowCls = isset($exp['rowCls']) ? $exp['rowCls'] : '';
        $rowCls .= ' yesNo';

        $exp2 = array('useKey' => 1, 'rowCls' => $rowCls);
        return $this->getRadioArrRow($displayTitle, $fieldName, $fieldValue, $arr, $exp2);
    }

    function getYesNoRRow2($displayTitle, $fieldName, $fieldValue = '', $exp = array()) {
        $yesText = isset($exp['yesText']) ? $exp['yesText'] : 'Yes';
        $noText = isset($exp['noText']) ? $exp['noText'] : 'No';
        $arr = array('Yes' => $yesText, 'No' => $noText);

        $rowCls = isset($exp['rowCls']) ? $exp['rowCls'] : '';
        $rowCls .= ' yesNo';

        $exp2 = array('useKey' => 1, 'rowCls' => $rowCls);
        return $this->getRadioArrRow($displayTitle, $fieldName, $fieldValue, $arr, $exp2);
    }

    function getYesNoDropDownRow($displayTitle, $fieldName, $fieldValue = '', $extraParam = array()){
        $yesText = isset($exp['yesText']) ? $exp['yesText'] : 'Yes';
        $noText = isset($exp['noText']) ? $exp['noText'] : 'No';
        $arr = array(1 => $yesText, 0 => $noText);

        $exp2 = array('useKey' => 1);
        return $this->getDropDownRowByArray($displayTitle, $fieldName, $arr, $fieldValue, $exp2);
    }

    /**
     *
     * @param <type> $displayTitle
     * @param <type> $fieldName
     * @param <type> $SQL
     * @param <type> $selectedValuesArr
     * @param <type> $extraParam
     * @return <type>
     */
    function getCheckBoxArrRowBySQL($displayTitle, $fieldName, $SQL, $selectedValuesArr = array(), $extraParam = array()){
        $cpUtil = Zend_Registry::get('cpUtil');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');

        $exp = &$extraParam;

        $hasOtherOption    = isset($exp['hasOtherOption']) ? $exp['hasOtherOption']  : 0;
        $commaToArray      = isset($exp['commaToArray'])   ? $exp['commaToArray']    : false;
        $disabled          = isset($exp['disabled'])       ? $exp['disabled']        : false;

        if ($commaToArray){
            $selectedValuesArr = explode(',', $selectedValuesArr);
        }

        if ($this->mode == 'detail' && !isset($exp['disabled'])){
            $exp['disabled'] = true;
        }

        $text = $dbUtil->getCheckBoxFromSQLCols($db, $SQL, $fieldName, $selectedValuesArr, $extraParam);

        if ($hasOtherOption == 1){
            $checked = (in_array($cpCfg['otherVLID'], $selectedValuesArr ) == true) ? " checked=\"checked\"" : "";
            $id = "{$fieldName}_{$cpCfg['otherVLTitle']}";
            $text .= "
            <div class='type-check ym-fbox-check'>
                <!-- <span class='ym-label'>{$displayTitle}</span>-->
                <input type=\"checkbox\" name=\"{$fieldName}\" value=\"{$cpCfg['otherVLID']}\" id=\"{$id}\" {$checked}{$disabled}{$jsText} />
                <label for=\"{$id}\">{$cpCfg['otherVLTitle']}</label>
            </div>
            ";
        }


        if ($displayTitle != ""){
            $text = "
            <div class='form-row-wrapper'>
                <div class='leftCol'>
                    <label for='fld_{$fieldName}'>{$displayTitle}</label>
                </div>

                <div class='rightCol'>
                    {$text}
                </div>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     * @param <type> $displayTitle
     * @param <type> $fieldName
     * @param <type> $SQL
     * @param <type> $selectedValuesArr
     * @param <type> $extraParam
     * @return <type>
     */
    function getCheckBoxArrRowByArr($displayTitle, $fieldName, $valArray,
                                    $selectedValuesArr = array(), $extraParam = array()){
        $cpUtil = Zend_Registry::get('cpUtil');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');

        $exp = &$extraParam;

        $hasOtherOption    = $fn->getIssetParam($exp, 'hasOtherOption', 0);
        $disabled          = $fn->getIssetParam($exp, 'disabled', false);
        $jsFunction        = $fn->getIssetParam($exp, 'jsFunction', '');
        $useKey            = $fn->getIssetParam($exp, 'useKey', 0);
        $singleValue       = $fn->getIssetParam($exp, 'singleValue', 0);
        $commaToArray      = $fn->getIssetParam($exp, 'commaToArray', false);

        if ($commaToArray){
            $selectedValuesArr = explode(',', $selectedValuesArr);
        }

        if ($singleValue == 1 && !is_array($selectedValuesArr)){
            $selectedValuesArr = array($selectedValuesArr);
        }

        if ($this->mode == 'detail' && !isset($exp['disabled'])){
            $exp['disabled'] = true;
        }
        $text = $cpUtil->getCheckboxFromArray($valArray, $fieldName, $selectedValuesArr, $extraParam);

        if ($displayTitle != ""){
            $text = "
            <div class='form-row-wrapper'>
                <div class='leftCol'>
                    <label for='fld_{$fieldName}'>{$displayTitle}</label>
                </div>

                <div class='rightCol'>
                    {$text}
                </div>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     * @param <type> $prefix
     * @return <type>
     */
    function getFullDateValueFromDD($prefix = "pref_"){
        $field1 = $prefix . "dd_day";
        $field2 = $prefix . "dd_month";
        $field3 = $prefix . "dd_year";

        //---------------------------------------------------------//
        $dd_day         = isset($_POST[$field1])  ? $_POST[$field1] : "";
        $dd_month       = isset($_POST[$field2])  ? $_POST[$field2] : "";
        $dd_year        = isset($_POST[$field3])  ? $_POST[$field3] : "";

        if ($dd_day != "" && $dd_month != "" && $dd_year != ""){
            $dateValue = $dd_year . "-" . $dd_month . "-" . $dd_day ;
        } else {
            $dateValue = "" ;
        }

        return $dateValue;
    }

    /**
     *
     */
    function getSubmitButtonRow($btnTitle, $exp = array()){
        $text = "";

        $text .= "
        <div class='type-button ym-fbox-button' style='display: block;'>
            <input type='submit' value='{$btnTitle}'>
        </div>
        ";

        return $text;
    }
    /**
     *
     */
    function getSubmitButtonImageRow($btnTitle, $jsFunction="", $link ="", $extraParam = array()){
        $exp = &$extraParam;
        $rowCls         = isset($exp['rowCls'])        ? "class='{$exp['rowCls']}'"        : "";
        $fieldCls       = isset($exp['fieldCls'])      ? "class='{$exp['fieldCls']}'"      : "";
        $linkCls        = isset($exp['linkCls'])       ? "class='{$exp['linkCls']}'"       : "";
        $linkUrl        = ($link != "") ? $link : "javascript:void(0)";
        $jsFunction     = ($jsFunction != "") ? "{$jsFunction}" : "";

        $text = "";

        $text  .= "
        <div {$rowCls}>
            <a {$linkCls} href='{$linkUrl}' onclick=\"{$jsFunction}\"><span>{$btnTitle}</span></a>
            <input type='submit' name='x_submit' class='submithidden' />
        </div>
        ";

        return $text;
    }

    /**
     *
     * @param <type> $btnTitle
     * @param <type> $jsFunction
     * @param <type> $link
     * @param <type> $extraParam
     * @return <type>
     */
    function getImageButtonRow($imgPath, $jsFunction, $link ="", $extraParam = array()){
        $exp = &$extraParam;
        $rowCls         = isset($exp['rowCls'])        ? "class='{$exp['rowCls']}'"        : "";
        $fieldLabelCls  = isset($exp['fieldLabelCls']) ? "class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls       = isset($exp['fieldCls'])      ? "class='{$exp['fieldCls']}'"      : "";
        $linkUrl        = ($link != "") ? $link : "javascript:void(0)";
        $jsFunction     = ($jsFunction != "") ? "{$jsFunction}" : "";

        $text = "";

        $text  .= "
        <div {$rowCls}>
            <label class='{$fieldLabelCls}'>&nbsp;</label>
            <a href='{$linkUrl}' onclick=\"{$jsFunction}\"><img src='{$imgPath}' /></a>
            <input type='submit' name='x_submit' class='submithidden' />
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getButtonImageRow($btnTitle, $jsFunction="", $link ="#", $extraParam = array()){
        $exp = &$extraParam;
        $rowCls         = isset($exp['rowCls'])        ? "class='{$exp['rowCls']}'"        : "";
        $fieldLabelCls  = isset($exp['fieldLabelCls']) ? "class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls       = isset($exp['fieldCls'])      ? "class='{$exp['fieldCls']}'"      : "";
        $linkUrl        = ($link != "") ? $link : "javascript:void(0)";
        $jsFunction     = ($jsFunction != "") ? "{$jsFunction}" : "";

        $text = "";

        $text  .= "
        <div {$rowCls}>
            <label class='{$fieldLabelCls}'>&nbsp;</label>
            <a class='squarebutton' href='{$linkUrl}' onclick=\"{$jsFunction}\"><span>{$btnTitle}</span></a>
        </div>
        ";

        return $text;
    }

    /**
     *
     * @param <type> $displayTitle
     * @param <type> $fieldName
     * @param <type> $SQL
     * @param <type> $fieldValue
     * @param <type> $extraParam
     * @return <type>
     */
    function getDropDownRowBySQL($displayTitle, $fieldName, $SQL = "", $fieldValue = "", $extraParam = array()){
        $cpUtil = Zend_Registry::get('cpUtil');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $exp = &$extraParam;
        $text = "";

        $rowCls            = isset($exp['rowCls'])           ? " {$exp['rowCls']}"                : "";
        $fieldLabelCls     = isset($exp['fieldLabelCls'])    ? " class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls          = isset($exp['fieldCls'])         ? " class='{$exp['fieldCls']}'"      : "";
        $jsFunction        = isset($exp['jsFunction'])       ? $exp['jsFunction']                 : "";
        $useKey            = isset($exp['useKey'])           ? $exp['useKey']                     : 0;
        $disabled          = isset($exp['disabled'])         ? $exp['disabled']                   : false;
        $sqlType           = isset($exp['sqlType'])          ? $exp['sqlType']                    : "TwoFields";
        $hideFirstOption   = isset($exp['hideFirstOption'])  ? 1                                  : 0;
        $selectObjID       = isset($exp['selectObjID']    )  ? "id='{$exp['selectObjID']}'"       : "";
        $hasOtherOption    = isset($exp['hasOtherOption'])   ? $exp['hasOtherOption']             : 0;
        $firstOptionLabel  = isset($exp['firstOptionLabel']) ? $exp['firstOptionLabel']           : $ln->getData("cp.form.lbl.pleaseSelect");
        $dummyFirstRowText = isset($exp['dummyFirstRowText'])? $exp['dummyFirstRowText']          : "";
        $hasLabel          = isset($exp['hasLabel'])         ? $exp['hasLabel']                   : true;
        $notesDivCls       = isset($exp['notesDivCls'])      ? " class='{$exp['notesDivCls']}'"   : " class='formFieldNotes'";
        $notesRightCls     = isset($exp['notesRightCls'])    ? " class='{$exp['notesRightCls']}'" : " class='formFieldNotesRight'";
        $notes             = isset($exp['notes'])            ? $exp['notes']                      : "";
        $notesRight        = isset($exp['notesRight'])       ? $exp['notesRight']                 : '';
        $detailValue       = isset($exp['detailValue'])      ? $exp['detailValue']                : '';
        $required          = isset($exp['required'])         ? $exp['required']                   : false;
        $extraHtml         = isset($exp['extraHtml'])        ? $exp['extraHtml']                  : "";
        $tabIndex          = isset($exp['tabindex']) ? "tabindex={$exp['tabindex']}" : '';

        $jsText            = ($jsFunction != "") ? " onchange=\"{$jsFunction}\"" : "";
        $disabled          = ($disabled == true) ? " disabled"      : "";

        if ($this->mode == 'detail'){
            $isEditable = 0;
        } else if (isset($exp['isEditable'])){
            $isEditable = $exp['isEditable'];
        } else {
            $isEditable = 1;
        }

        $requiredText = '';
        $requiredFldText = '';
        if ($required && $isEditable == 1) {
            $requiredText = $this->getRequiredText();
            $requiredFldText = "required='require'  aria-required='true'";
        }

        if ($notes != ""){
           $notes = "<div{$notesDivCls}>{$notes}</div>";
        }

        if ($notesRight != ""){
           $notesRight = "<div{$notesRightCls}>{$notesRight}</div>";
        }

        $labelText = '';
        if ($hasLabel) {
            $labelText = "
            <label for='fld_{$fieldName}'{$fieldLabelCls}>{$displayTitle}{$requiredText}</label>
            ";
        }

        $notesSpan = '';
        if ($extraHtml != "" || $notes != '' || $notesRight != ''){
            $notesSpan = "<span class='ml10'>{$extraHtml}{$notes}{$notesRight}</span>";
        }

        if($isEditable == 1){
            $ddText = '';

            if ($hideFirstOption == 0){
                $selected = ($fieldValue == "") ? " selected='selected'" : "";
                $ddText = "<option value=''{$selected}>{$firstOptionLabel}</option>";
            }

            if ($dummyFirstRowText != ""){
                $ddText .= "<option value=''>{$dummyFirstRowText}</option>";
            }

            if ($SQL != ""){
                if ($sqlType == "TwoFields"){
                    $ddText .= $dbUtil->getDropDownFromSQLCols2($db, $SQL, $fieldValue);

                } else if ($sqlType == "OneField"){
                    $ddText .= $dbUtil->getDropDownFromSQLCols1($db, $SQL, $fieldValue);

                } else if ($sqlType == "hasSeperator"){
                    $ddText .= $dbUtil->getDropDownWithSeperator($db, $SQL, $fieldValue);
                }
            }

            if ($hasOtherOption == 1){
                $selected = ($fieldValue == $cpCfg['otherVLID']) ? "selected='selected'" : "";
                $ddText .= "<option {$selected} value='{$cpCfg['otherVLID']}'>{$cpCfg['otherVLTitle']}</option>";
            }

            $fieldValueTemp = "
            <select name='{$fieldName}' id='fld_{$fieldName}' {$fieldCls}{$jsText}{$disabled} {$requiredFldText} {$tabIndex}>
                {$ddText}
            </select>
            {$notesSpan}
            ";
        } else {
            $fieldValue = ($detailValue != '') ? $detailValue : $fieldValue;
            $txt = ($fieldValue != '') ? $fieldValue : '&nbsp;';
            $fieldValueTemp = "<div class='txt'>{$txt}{$notesSpan}</div>";
        }

        $clsName = "row_{$fieldName}";

        $text = "
        <div class='type-select ym-fbox-select {$clsName}{$rowCls}'>
            {$labelText}
            {$fieldValueTemp}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDDRowBySQL($displayTitle, $fieldName, $SQL = "", $fieldValue = "", $extraParam = array()){
        return $this->getDropDownRowBySQL($displayTitle, $fieldName, $SQL, $fieldValue, $extraParam);
    }

    /**
     *
     */
    function getDDRowByVL($displayTitle, $fieldName, $vlName, $fieldValue = "", $exp = array()){
        $fn = Zend_Registry::get('fn');
        $orderBy = $fn->getIssetParam($exp, 'orderBy', 'value');
        $globalForAllSites = $fn->getIssetParam($exp, 'globalForAllSites', false);
        $condn = "WHERE key_text = '{$vlName}'";

        if (!$globalForAllSites){
            $condn = $fn->appendSiteIdToCondn($condn);
        }

        $SQL = "
        SELECT value
        FROM valuelist
        {$condn}
        ORDER BY {$orderBy}
        ";
        $exp['sqlType'] = 'OneField';

        return $this->getDropDownRowBySQL($displayTitle, $fieldName, $SQL, $fieldValue, $exp);
    }

    /**
     *
     */
    function getDropdownByVL($displayTitle, $fieldName, $vlName, $fieldValue = "", $exp = array()){
        $fn = Zend_Registry::get('fn');
        $orderBy = $fn->getIssetParam($exp, 'orderBy', 'value');
        $globalForAllSites = $fn->getIssetParam($exp, 'globalForAllSites', false);
        $condn = "WHERE key_text = '{$vlName}'";

        if (!$globalForAllSites){
            $condn = $fn->appendSiteIdToCondn($condn);
        }

        $SQL = "
        SELECT value
        FROM valuelist
        {$condn}
        ORDER BY {$orderBy}
        ";
        $exp['sqlType'] = 'OneField';

        return $this->getDropDownBySQL($displayTitle, $fieldName, $SQL, $fieldValue, $exp);
    }


    /**
     *
     */
    function getDropDownRowByArray($displayTitle, $fieldName, $arr, $fieldValue, $extraParam = array()){
        $cpUtil = Zend_Registry::get('cpUtil');
        $ln = Zend_Registry::get('ln');
        $exp = &$extraParam;
        $text = "";

        $rowCls            = isset($exp['rowCls'])           ? " {$exp['rowCls']}"                  : "";
        $fieldLabelCls     = isset($exp['fieldLabelCls'])    ? " class='{$exp['fieldLabelCls']}'"   : "";
        $fieldCls          = isset($exp['fieldCls'])         ? " class='{$exp['fieldCls']}'"        : "";
        $jsFunction        = isset($exp['jsFunction'])       ? $exp['jsFunction']                   : "";
        $useKey            = isset($exp['useKey'])           ? $exp['useKey']                       : 0;
        $disabled          = isset($exp['disabled'])         ? $exp['disabled']                     : false;
        $hideFirstOption   = isset($exp['hideFirstOption'])  ? $exp['hideFirstOption']              : 0;
        $firstOptionLabel  = isset($exp['firstOptionLabel']) ? $exp['firstOptionLabel']             : $ln->getData("cp.form.lbl.pleaseSelect");
        $jsText            = ($jsFunction != "")             ? " onchange='{$jsFunction}'"          : "";
        $disabled          = ($disabled == true)             ? " disabled='{$disabled}'"            : "";
        $required          = isset($exp['required'])         ? $exp['required']                     : false;
        $tabIndex          = isset($exp['tabindex']) ? "tabindex={$exp['tabindex']}" : '';

        $notesDivCls   = isset($exp['notesDivCls'])   ? " class='{$exp['notesDivCls']}'"   : " class='formFieldNotes'";
        $notesRightCls = isset($exp['notesRightCls']) ? " class='{$exp['notesRightCls']}'" : " class='formFieldNotesRight'";
        $notes         = isset($exp['notes'])         ? $exp['notes']        : '';
        $notesRight    = isset($exp['notesRight'])    ? $exp['notesRight']   : '';

        $requiredText = '';
        $requiredFldText = '';
        if ($required) {
            $requiredText = $this->getRequiredText();
            $requiredFldText = "required='require'  aria-required='true'";
        }

        if (isset($exp['isEditable'])){
            $isEditable = $exp['isEditable'];
        } else if ($this->mode == 'detail'){
            $isEditable = 0;
        } else {
            $isEditable = 1;
        }


        if ($notes != ""){
           $notes = "<div{$notesDivCls}>{$notes}</div>";
        }

        if ($notesRight != ""){
           $notesRight = "<div{$notesRightCls}>{$notesRight}</div>";
        }

        $notesSpan = '';
        if ($notes != '' || $notesRight != ''){
           $notesSpan = "<span>{$notes}{$notesRight}</span>";
        }

        if($isEditable == 1){
            $ddText = '';

            if ($hideFirstOption == 0){
                $selected = ($fieldValue == "") ? " selected='selected'" : "";
                $ddText = "<option value=''{$selected}>{$firstOptionLabel}</option>";
            }

            $ddText .= $cpUtil->getDropDown1($arr, $fieldValue, $useKey);

            $fieldValueTemp = "
            <select name='{$fieldName}' id='fld_{$fieldName}' {$fieldCls}{$jsText}{$disabled} {$requiredFldText} {$tabIndex}>
                {$ddText}
            </select>
            ";
        } else {
            $fieldValueTemp = ($useKey == 1 && $fieldValue != '') ? $arr[$fieldValue] : $fieldValue;
            $txt = ($fieldValueTemp != '') ? $fieldValueTemp : '&nbsp;';
            $fieldValueTemp = "<div class='txt'>{$txt}</div>";
        }

        $clsName = "row_{$fieldName}";

        $text = "
        <div class='type-select ym-fbox-select {$rowCls} {$clsName}'>
            <label{$fieldLabelCls} for='fld_{$fieldName}'>{$displayTitle}{$requiredText}</label>
            {$fieldValueTemp}
            {$notesSpan}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDropDownByArray($firstOptionLabel, $fieldName, $arr, $fieldValue = '', $extraParam = array()){
        $cpUtil = Zend_Registry::get('cpUtil');
        $ln = Zend_Registry::get('ln');
        $exp = &$extraParam;

        $fieldCls          = isset($exp['fieldCls'])         ? " class='{$exp['fieldCls']}'"        : "";
        $useKey            = isset($exp['useKey'])           ? $exp['useKey']                       : 0;
        $disabled          = isset($exp['disabled'])         ? $exp['disabled']                     : false;
        $disabled          = ($disabled == true)             ? " disabled='{$disabled}'"            : "";
        $hideFirstOption   = isset($exp['hideFirstOption'])  ? $exp['hideFirstOption']              : 0;
        $tabIndex          = isset($exp['tabindex']) ? "tabindex={$exp['tabindex']}" : '';

        $ddText = '';
        if ($hideFirstOption == 0){
            $selected = ($fieldValue == "") ? " selected='selected'" : "";
            $ddText = "<option value=''{$selected}>{$firstOptionLabel}</option>";
        }

        $ddText .= $cpUtil->getDropDown1($arr, $fieldValue, $useKey);

        $text = "
        <select name='{$fieldName}' id='fld_{$fieldName}' {$fieldCls}{$disabled} {$tabIndex}>
            {$ddText}
        </select>
        ";

        return $text;
    }


    function getDDRowByArr($displayTitle, $fieldName, $arr, $fieldValue='', $extraParam = array()){
        return $this->getDropDownRowByArray($displayTitle, $fieldName, $arr, $fieldValue, $extraParam);
    }

    /**
     *
     * @param <type> $field1
     * @param <type> $field2
     * @param <type> $field3
     * @return <type>
     */
    function getMultiFldsInOneRow($fldArray){
        $text     = "";
        $fldsText = "";

        $count = count($fldArray);

        if ($count == 3){
            $col = "c33l";
        } else if ($count == 2){
            $col = "c50l";
        }

        foreach($fldArray as $key => $field){
            $fldsText .= "
            <div class='{$col}'>
                {$field}
            </div>
            ";
        }

        $text .= "
        <div class=\"subcolumns multicols\">
            {$fldsText}
        </div>
        ";

        return $text;
    }
    /**
     *
     * @param <type> $displayTitle
     * @param <type> $fieldName
     * @param <type> $antiSpamExplanation
     * @return <type>
     */
    function getCaptchaImage($displayTitle, $fieldName, $extraParam = array()){
        $ln = Zend_Registry::get('ln');
        $exp = &$extraParam;
        $rowCls           = isset($exp['rowCls'])        ? " {$exp['rowCls']}"                : "";
        $fieldLabelCls    = isset($exp['fieldLabelCls']) ? " class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls         = isset($exp['fieldCls'])      ? " class='{$exp['fieldCls']}'"      : "";
        $inputType        = isset($exp['password'])      ? "password"           : "text";
        $reloadBtnCls     = isset($exp['reloadBtnCls'])  ? $exp['reloadBtnCls'] : "";
        $required         = isset($exp['required'])      ? $exp['required']     : true;
        $tabIndex         = isset($exp['tabindex']) ? "tabindex={$exp['tabindex']}" : '';

        $requiredText = '';
        $requiredFldText = '';
        if ($required) {
            $requiredText = $this->getRequiredText();
            $requiredFldText = "required='require'  aria-required='true'";
        }

        $cp_lib_path_alias = CP_LIBRARY_PATH_ALIAS;

        $clsName = "row_{$fieldName}";

        $reloadUrl = "document.getElementById('captcha').src =" .
                     "'{$cp_lib_path_alias}lib_php/securimage/securimage_show.php?'" .
                     "+ Math.random(); return false;";

        $text = "
        <div class='type-text ym-fbox-text {$clsName}{$rowCls}'>
            <label{$fieldLabelCls} for='fld_{$fieldName}'>{$displayTitle}{$requiredText}</label>
            <input{$fieldCls} type='{$inputType}' name='{$fieldName}' id='fld_{$fieldName}' value='' {$tabIndex}/>
            <div class='captcha'>
                <img id='captcha' src='{$cp_lib_path_alias}lib_php/securimage/securimage_show.php' alt='CAPTCHA Image' />
                <div class='reload'>
                    <div class='info'>{$ln->gd2('cp.form.lbl.captchaReloadInfo')}</div>
                    <a href='#' id='reloadCaptcha' class='{$reloadBtnCls} mt5'
                       onclick=\"{$reloadUrl}\"><span>{$ln->gd("reload")}</span></a>
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getHTMLEditor($displayTitle, $fieldName, $fieldValue = '', $exp = array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');

        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('ckEditor'));

        $includeStylesheet = isset($exp['includeStylesheet']) ? "{$exp['includeStylesheet']}" : true;
        $stylesheetPath    = isset($exp['stylesheetPath']   ) ? "{$exp['stylesheetPath']}"    : '/css/central.css';
        if ($this->mode == 'detail'){
            $text = "
            <div class='txt'>
                {$fieldValue}
            </div>
            ";
        } else {
            $text = "
            <div class='type-text ym-fbox-text'>
                <textarea
                class='cp_ckeditor'
                name='{$fieldName}'
                id='fld_{$fieldName}'
                includeStylesheet='{$includeStylesheet}'
                stylesheetPath='{$stylesheetPath}'
                >{$fieldValue}</textarea>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getDateRow($displayTitle, $fieldName, $fieldValue = "", $exp = array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $fn = Zend_Registry::get('fn');

        $rowCls        = isset($exp['rowCls'])        ? " {$exp['rowCls']}"                : "";
        $fieldLabelCls = isset($exp['fieldLabelCls']) ? " class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls      = isset($exp['fieldCls'])      ? " class='{$exp['fieldCls']}'"      : "";
        $notesDivCls   = isset($exp['notesDivCls'])   ? " class='{$exp['notesDivCls']}'"   : "class='formFieldNotes'";
        $notes         = isset($exp['notes'])         ? $exp['notes']                      : "";
        $inputType     = isset($exp['password'])      ? "password"                         : "text";
        $extraHtml     = isset($exp['extraHtml'])     ? $exp['extraHtml']                  : "";
        $allowEdit     = isset($exp['allowEdit'])     ? $exp['allowEdit']                  : 0;
        $yearStart     = isset($exp['yearStart'])     ? $exp['yearStart']                  : date('Y') - 10;
        $yearEnd       = isset($exp['yearEnd'])       ? $exp['yearEnd']                    : date('Y') + 10;
        $minDate       = isset($exp['minDate'])       ? $exp['minDate']                    : '';
        $maxDate       = isset($exp['maxDate'])       ? $exp['maxDate']                    : '';
        $required      = isset($exp['required'])      ? $exp['required']                   : false;
        $fldId         = isset($exp['fldId'])         ? $exp['fldId']                      : "fld_{$fieldName}";
        $dateFormat = $fn->getIssetParam($exp, 'dateFormat', 'yy-mm-dd');

        if ($notes != ""){
           $notes = "<div class=\"{$notesDivCls}\">{$notes}</div>";
        }

        $isEditable    = isset($exp['isEditable'])    ? $exp['isEditable']      : 1;

        if (isset($exp['isEditable'])){
            $isEditable = $exp['isEditable'];
        } else if ($this->mode == 'detail'){
            $isEditable = 0;
        } else {
            $isEditable = 1;
        }

        $requiredText = '';
        $requiredFldText = '';
        if ($required) {
            $requiredText = $this->getRequiredText();
            $requiredFldText = "required='require'  aria-required='true'";
        }

        if($isEditable == 1){
            $fieldValue = $cpUtil->replaceForFormField($fieldValue);
            $fieldValueTemp = "
            <input{$fieldCls}  allowEdit='{$allowEdit}' type='text' name='{$fieldName}' fldId='{$fldId}'
                 class='fld_date' yearStart='{$yearStart}' yearEnd='{$yearEnd}' id='fld_{$fieldName}' " .
                 "minDate='{$minDate}' maxDate='{$maxDate}'" .
                 "value=\"{$fieldValue}\" dateFormat='{$dateFormat}' {$requiredFldText}>{$extraHtml}
            ";
        } else {
            if ($cpUtil->is_date($fieldValue)){
                $ts = strtotime($fieldValue);
                $fieldValue = date('d-m-Y', $ts);
            }
            $txt = ($fieldValue != '') ? $fieldValue : '&nbsp;';
            $fieldValueTemp = "<div class='txt'>{$txt}{$extraHtml}</div>";
        }

        $clsName = "row_{$fieldName}";


        $text = "
        <div class='type-text ym-fbox-text {$clsName}{$rowCls}'>
            <label{$fieldLabelCls} for='fld_{$fieldName}'>{$displayTitle}{$requiredText}</label>
            {$notes}
            {$fieldValueTemp}
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getDayMonthDdRow($displayTitle, $fieldName, $val1 = '', $val2 = '', $exp = array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUtil = Zend_Registry::get('cpUtil');
        $ln = Zend_Registry::get('ln');

        $rowCls        = isset($exp['rowCls'])        ? " {$exp['rowCls']}"                : "";
        $fieldLabelCls = isset($exp['fieldLabelCls']) ? " class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls      = isset($exp['fieldCls'])      ? " class='{$exp['fieldCls']}'"      : "";
        $notesDivCls   = isset($exp['notesDivCls'])   ? " class='{$exp['notesDivCls']}'"   : "class='formFieldNotes'";
        $notes         = isset($exp['notes'])         ? $exp['notes']                      : "";
        $extraHtml     = isset($exp['extraHtml'])     ? $exp['extraHtml']                  : "";
        $allowEdit     = isset($exp['allowEdit'])     ? $exp['allowEdit']                  : 0;
        $disabled      = isset($exp['disabled'])      ? $exp['disabled']                   : false;

        if ($notes != ""){
           $notes = "<div class=\"{$notesDivCls}\">{$notes}</div>";
        }

        $isEditable    = isset($exp['isEditable']) ? $exp['isEditable'] : 1;

        if (isset($exp['isEditable'])){
            $isEditable = $exp['isEditable'];
        } else if ($this->mode == 'detail'){
            $isEditable = 0;
        } else {
            $isEditable = 1;
        }

        if($isEditable == 1){
            $fieldValueTemp = "
            <select name='{$fieldName}_day' id='fld_{$fieldName}_day' {$fieldCls}{$disabled}>
                <option value=''>{$ln->gd('cp.form.fld.day.lbl')}</option>
                {$cpUtil->getDropDown1($cpUtil->getDayArray(), $val1)}
            </select>
            <select name='{$fieldName}_month' id='fld_{$fieldName}_month' {$fieldCls}{$disabled}>
                <option value=''>{$ln->gd('cp.form.fld.month.lbl')}</option>
                 {$cpUtil->getDropDown1($cpUtil->getMonthArray(), $val2, 1)}
            </select>
            ";
        } else {
            $monthArr = $cpUtil->getMonthArray();
            $val2 = ($val2 != '') ? $monthArr[$val2] : '';
            $fieldValueTemp = "<div class='txt'>{$val1}-{$val2}{$extraHtml}</div>";
        }

        $clsName = "row_{$fieldName}";

        $text = "
        <div class='type-text ym-fbox-text {$clsName}{$rowCls}'>
            <label{$fieldLabelCls} for='fld_{$fieldName}'>{$displayTitle}</label>
            {$notes}
            {$fieldValueTemp}
        </div>
        ";

        return $text;
    }

    //==================================================================//
    function getDateRangeRow($displayTitle, $fieldName, $val1 = '', $val2 = '', $format = "DD MMM YYYY", $allowEdit = 1) {

        $text = "
        <div class='type-text ym-fbox-text dateRange'>
            <label for='fld_{$fieldName}'>{$displayTitle}</label>
            <input type='text' allowEdit='{$allowEdit}' name='{$fieldName}_1' class='fld_date' id='fld_{$fieldName}_2' value='{$val1}' />
            <input type='text' allowEdit='{$allowEdit}' name='{$fieldName}_2' class='fld_date' id='fld_{$fieldName}_1' value='{$val2}' />
        </div>
        ";

        return $text;
    }

    /**
     *
     * @return <type>
     */
    function getTimeRow($displayTitle, $fieldName, $fieldValue = "", $exp = array()){
    	$exp['fldType'] = 'time';
        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqUITimePickerAddon-0.9.3'));
        return $this->getTextBoxRow($displayTitle, $fieldName, $fieldValue, $exp);
    }


    /**
     *
     * @return <type>
     */
    function getDateTimeRow($displayTitle, $fieldName, $fieldValue = "", $exp = array()){
    	$exp['fldType'] = 'datetime';
        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('jqUITimePickerAddon-0.9.3'));
        return $this->getTextBoxRow($displayTitle, $fieldName, $fieldValue, $exp);
    }

    /**
     *
     * @return <type>
     */
    function getCreationModificationText($row) {
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');
        $dateUtil = Zend_Registry::get('dateUtil');

        $creation_date       = $dateUtil->formatDate($row['creation_date']    , "DD MMM YYYY");
        $modification_date   = $dateUtil->formatDate($row['modification_date'], "DD MMM YYYY");

        $exp = array('isEditable' => 0);

        if (array_key_exists('created_by', $row)  || array_key_exists('modified_by', $row)) {
            $text1 = ($row['created_by'] != "")  ? $row['created_by']  . " on " : "";
            $text2 = ($row['modified_by'] != "") ? $row['modified_by'] . " on " : "";

            $fieldset = "
            {$this->getTextBoxRow($ln->gd('cp.lbl.creation', 'Created By'), 'created_by', $text1 . $row['creation_date'], $exp)}
            {$this->getTextBoxRow($ln->gd('cp.lbl.modified', 'Modified By'), 'modified_by', $text2 . $row['modification_date'], $exp)}
            ";
        } else {
            $fieldset = "
            {$this->getTextBoxRow($ln->gd('cp.lbl.creation', 'Creation'), 'creation', $row['creation_date'], $exp)}
            {$this->getTextBoxRow($ln->gd('cp.lbl.modification', 'Modification'), 'modification', $row['modification_date'], $exp)}
            ";
        }

        return $this->getFieldSetWrapped($ln->gd('cp.lbl.creationAndModification', 'Creation & Modification'), $fieldset);
    }

    //==================================================================//
    function getCreationModificationText2($row) {
        $tv = Zend_Registry::get('tv');
        $dateUtil = Zend_Registry::get('dateUtil');
        $ln = Zend_Registry::get('ln');

        $creation_date       = $dateUtil->formatDate($row['creation_date']    , "DD MMM YYYY");
        $modification_date   = $dateUtil->formatDate($row['modification_date'], "DD MMM YYYY");

        $exp = array('isEditable' => 0);
        $fielset = "
        {$this->getTextBoxRow($ln->gd('cp.lbl.creation', 'Creation'), 'created_by', $row['creation_date'], $exp)}
        {$this->getTextBoxRow($ln->gd('cp.lbl.modified', 'Modification'), 'modified_by', $row['modification_date'], $exp)}
        ";

        return $this->getFieldSetWrapped($ln->gd('cp.lbl.creationAndModification', 'Creation & Modification'), $fielset);
    }

    //==================================================================//
    function getFieldSetWrapped($title, $form, $exp=array()){
        $fn = Zend_Registry::get('fn');
        $class = $fn->getIssetParam($exp, 'class');

        $legend = ($title != '') ? "<legend class='{$class}'>{$title}</legend>" : '';
        return "
        <fieldset>
            {$legend}
            {$form}
        </fieldset>
        ";
    }

    //==================================================================//
    function getFormattedValue($value){
        $cpUtil = Zend_Registry::get('cpUtil');

        if (is_numeric($value)) {
            $value = number_format($value, 2);
        }

        return $value;
    }

    //==================================================================//
    function getMetaData($row = "", $exp = array()) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $dbUtil = Zend_Registry::get('dbUtil');
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');

        $lblTitle       = $fn->getIssetParam($exp, 'lblTitle', $ln->gd('cp.lbl.pageTitle', 'Page Title'));
        $lblDescription = $fn->getIssetParam($exp, 'lblDescription', $ln->gd('cp.lbl.pageDescription', 'Page Description'));
        $lblKeywords    = $fn->getIssetParam($exp, 'lblKeywords', $ln->gd('cp.lbl.pageKeywords', 'Page Keywords'));
        $lblFieldset    = $fn->getIssetParam($exp, 'lblFieldset', $ln->gd('cp.lbl.pageMetaData', 'Page Meta Data'));

        if ($cpCfg['cp.hasMultiLangForMetaData']){
            $fieldset = "
            {$this->getTBRow($lblTitle, 'meta_title', $ln->gfv($row, 'meta_title', '0'))}
            {$this->getTARow($lblDescription, 'meta_description', $ln->gfv($row, 'meta_description', '0'))}
            {$this->getTARow($lblKeywords, 'meta_keyword', $ln->gfv($row, 'meta_keyword', '0'))}
            ";
        } else {
            $fieldset = "
            {$this->getTBRow($lblTitle, 'meta_title', $row['meta_title'])}
            {$this->getTARow($lblDescription, 'meta_description', $row['meta_description'])}
            {$this->getTARow($lblKeywords, 'meta_keyword', $row['meta_keyword'])}
            ";
        }

        $text = $this->getFieldSetWrapped($lblFieldset, $fieldset);

        return $text;
    }

    /**
     *
     */
    function getUploadifyObj($module, $recordType, $id, $exp = array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $media = Zend_Registry::get('media');
        $fn = Zend_Registry::get('fn');
        $cpUtil = Zend_Registry::get('cpUtil');

        $media->model->setMediaArray($module, $recordType);
        $mediaArray = Zend_Registry::get('mediaArray');

        $lang = $fn->getIssetParam($exp, 'lang');
        $hideUploadBtn  = $fn->getIssetParam($exp, 'hideUploadBtn', false);
        $formSuccessMsg = $fn->getIssetParam($exp, 'formSuccessMsg', false);
        $browseButtonImg = $fn->getIssetParam($exp, 'browseButtonImg', $cpCfg['cp.masterImagesPathAlias'].'icons/btn_browse.png');
        $uploadeButtonImg = $fn->getIssetParam($exp, 'uploadButtonImg', $cpCfg['cp.masterImagesPathAlias'].'icons/btn_upload_files.png');
        $btnWidth = $fn->getIssetParam($exp, 'btnWidth', 90);
        $btnHeight = $fn->getIssetParam($exp, 'btnHeight', 20);
        $fileSizeLimit = $fn->getIssetParam($exp, 'fileSizeLimit', $cpCfg['cp.maxUploadLimit']);
        $queueSizeLimit = $fn->getIssetParam($exp, 'queueSizeLimit', 150);
        $fileTypeExts = $mediaArray[$module][$recordType]['fileTypeExts'];

        $sessionID = session_id();

        $uploadBtn = '';

        if (!$hideUploadBtn){
            $uploadBtn = "
            <div class='float_left'>
                <a class='uploadQueue' href=''><img src='{$uploadeButtonImg}' /></a>
            </div>
            ";
        }

        if (CP_SCOPE == 'admin' && $cpUtil->isIEBrowser()){
            $text = "
            <form id='uploadWrapHtml_{$recordType}' class='uploadWrapHtml' method='post'
                action='/index.php?plugin=common_media&_spAction=addMedia&showHTML=0'
                enctype='multipart/form-data' name='mediaUploadHtml'>
                <table width='100%' cellpadding='0' cellspacing='0'>
                    <tr>
                        <td>
                            <input name='fileName' type='file' />
                        </td>
                    </tr>

                    <tr>
                        <td height='40'>
                            <input class='button' type='submit' value='Upload' name='fileUpload' />
                        </td>
                    </tr>
                </table>
                <input type='hidden' name='room'       value='{$module}' />
                <input type='hidden' name='recordType' value='{$recordType}' />
                <input type='hidden' name='id'         value='{$id}' />
                <input type='hidden' name='sessionIDCP' value='{$sessionID}' />
                <input type='hidden' name='successText' value='successText' />
                <input type='hidden' name='lang' value='{$lang}' />
            </form>
            ";
        } else {

            if ($cpCfg['cp.uploadTechnology'] == 'html'){
                $text = "
                <div id='uploadWrap_{$recordType}' class='uploadWrap'>
                    <div id='fileQueueMedia_{$recordType}' class='fileQueueMedia'></div>
                    <div class='floatbox'>
                        <div class='float_left mr10'>
                            <input class='uploadifive'  type='file' multiple='true' name='uploadifiveMedia_{$recordType}' id='uploadifiveMedia_{$recordType}' />
                        </div>
                        <div class='float_right uploadFiles'>
                            <a class='uploadifiveQueue' href=''>Upload Files</a>
                        </div>
                    </div>
                </div>

                <script>
                    $(function() {
                        var exp = {
                              formSuccessMsg: '{$formSuccessMsg}'
                            , browseButtonImg: '{$browseButtonImg}'
                            , btnWidth: '{$btnWidth}'
                            , btnHeight: '{$btnHeight}'
                            , fileSizeLimit: '{$fileSizeLimit}'
                            , queueSizeLimit: '{$queueSizeLimit}'
                            , fileTypeExts: '{$fileTypeExts}'
                            , lang: '{$lang}'
                        }
                        cpp.common.media.setUploadifive('{$module}', '{$recordType}', '{$id}', '{$sessionID}', exp);
                    });
                </script>
                ";
            } else {
                $text = "
                <div id='uploadWrap_{$recordType}' class='uploadWrap'>
                    <div id='fileQueueMedia_{$recordType}' class='fileQueueMedia'></div>
                    <div class='floatbox'>
                        <div class='float_left mr10'>
                            <input type='file' name='uploadifyMedia_{$recordType}' id='uploadifyMedia_{$recordType}' />
                        </div>
                        {$uploadBtn}
                    </div>
                </div>
                <script>
                    $(function() {
                        var exp = {
                              formSuccessMsg: '{$formSuccessMsg}'
                            , browseButtonImg: '{$browseButtonImg}'
                            , btnWidth: '{$btnWidth}'
                            , btnHeight: '{$btnHeight}'
                            , fileSizeLimit: '{$fileSizeLimit}'
                            , queueSizeLimit: '{$queueSizeLimit}'
                            , fileTypeExts: '{$fileTypeExts}'
                            , lang: '{$lang}'
                        }
                        cpp.common.media.setUploadify('{$module}', '{$recordType}', '{$id}', '{$sessionID}', exp);
                    });
                </script>
                ";
            }
        }

        return $text;
    }

    /**
     *
     */
    function getDaysOfWeeksRow($displayTitle, $fieldName, $fieldValue = "", $extraParam = array()){

        $valArray = array(
             'Sun'
            ,'Mon'
            ,'Tue'
            ,'Wed'
            ,'Thu'
            ,'Fri'
            ,'Sat'
        );

        $selectedValuesArr = explode(', ', $fieldValue);

        return $this->getCheckBoxArrRowByArr($displayTitle, $fieldName, $valArray, $selectedValuesArr);
    }

    /**
     *
     */
    function getDropDownBySQL($displayTitle, $fieldName, $SQL = "", $fieldValue = "", $extraParam = array()){
        $dbUtil = Zend_Registry::get('dbUtil');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $exp = &$extraParam;

        $sqlType = isset($exp['sqlType']) ? $exp['sqlType'] : "TwoFields";
        $selected = ($fieldValue == '') ? " selected='selected'" : "";
        $firstVal = ($displayTitle != '') ? "<option value=''{$selected}>{$displayTitle}</option>" : '';
        $fieldCls = isset($exp['fieldCls']) ? " class='{$exp['fieldCls']}'"      : "";

        $ddText = '';
        if ($SQL != ""){
            if ($sqlType == "TwoFields"){
                $ddText = $dbUtil->getDropDownFromSQLCols2($db, $SQL, $fieldValue);
            } else if ($sqlType == "OneField"){
                $ddText = $dbUtil->getDropDownFromSQLCols1($db, $SQL, $fieldValue);
            } else if ($sqlType == "hasSeperator"){
                $ddText = $dbUtil->getDropDownWithSeperator($db, $SQL, $fieldValue);
            }
        }

        $text = "
        <select name='{$fieldName}' id='fld_{$fieldName}'{$fieldCls}>
            {$firstVal}
            {$ddText}
        </select>
        ";

        return $text;
    }

    function getStarRatingRow($displayTitle, $fieldName, $fieldValue = '', $disabled = false, $exp = array()) {
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $rows = $this->getStarRatingFld($fieldName, $fieldValue, $disabled, $exp);

        $showHoverTips = $fn->getIssetParam($exp, 'showHoverTips', true);
        $hoverTipDefault = $fn->getIssetParam($exp, 'hoverTipDefault', $ln->gd('p.common.comment.lbl.hoverTipDefault'));
        $clsName = " row_rating";

        $hoverTxt = '';
        if($showHoverTips){
            $hoverTxt = "
            <div class='rating-hover-tips'><span>{$hoverTipDefault}</span></div>
            ";
        }

        $text = "
        <div class='type-text ym-fbox-text {$clsName}'>
            <label>{$displayTitle}</label>
            {$rows}
            {$hoverTxt}
        </div>
        ";

        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('starrating-3.14'));

        return $text;
    }

    function getStarRatingFld($fieldName, $fieldValue = '', $disabled = false, $exp = array()) {
        $fn = Zend_Registry::get('fn');

        $optionArr = $fn->getIssetParam($exp, 'optionArr');

        if (!is_array($optionArr)){
            $optionArr = array(
                 1 => 'Terrible'
                ,2 => 'Poor'
                ,3 => 'Average'
                ,4 => 'Very Good'
                ,5 => 'Excellent'
            );
        }

        $start     = $fn->getIssetParam($exp, 'start', 1);
        $end       = $fn->getIssetParam($exp, 'end', 5);
        $increment = $fn->getIssetParam($exp, 'increment', $start);
        $split     = $fn->getIssetParam($exp, 'split', 1);
        $optionArr  = $fn->getIssetParam($exp, 'optionArr', $optionArr);
        $starCls   = $fn->getIssetParam($exp, 'starCls', 'cpStarRating');

        $rows = '';
        for ($i = $start; $i<= $end; $i=$i+$increment){
            $checked = ($fieldValue == $i) ? " checked='checked'" : '';
            $disabled = ($disabled) ? " disabled='disabled'" : '';
            $title = isset($optionArr[$i]) ? " title='{$optionArr[$i]}'" : '';
            $rows .= "<input name='{$fieldName}' type='radio' value='{$i}' class='{$starCls} {split:{$split}}'{$checked}{$disabled}{$title}/>\n";
        }

        $text = "
        {$rows}
        ";

        CP_Common_Lib_Registry::arrayMerge('jssKeys', array('starrating-3.14'));

        return $text;
    }

    function getHiddenFldObj($name, $value = '', $id = ''){
        if ($id == '') {
            $id = 'fld_' . $name;
        }

        $text = "<input type='hidden' name='{$name}' id='{$id}' value='{$value}'>";

        return $text;
    }

    function getTextFldObj($name, $value = '', $id = '', $exp = array()){
        $fn = Zend_Registry::get('fn');

        if ($id == '') {
            $id = 'fld_' . $name;
        }
        $class = $fn->getIssetParam($exp, 'class');
        $class .= ' text';
        $isEditable = $fn->getIssetParam($exp, 'isEditable');

        if ($this->mode == 'detail'){
            $isEditable = 0;
        } else if (isset($exp['isEditable'])){
            $isEditable = $exp['isEditable'];
        } else {
            $isEditable = 1;
        }

        if ($isEditable == 1){
            $text = "<input type='text' name='{$name}' id='{$id}' value='{$value}' class='{$class}'>";
        } else {
            $text = $value;
        }

        return $text;
    }

    function getTextAreaObj($name, $value = '', $id = '', $exp = array()){
        $fn = Zend_Registry::get('fn');

        if ($id == '') {
            $id = 'fld_' . $name;
        }
        $class = $fn->getIssetParam($exp, 'class');
        $isEditable = $fn->getIssetParam($exp, 'isEditable');
        $pptxt = $fn->getIssetParam($exp, 'pptxt');

        $prePopText = '';
        if ($pptxt) {
            $prePopText = "rel='pptxt: {$pptxt}'";
        }

        if ($this->mode == 'detail'){
            $isEditable = 0;
        } else if (isset($exp['isEditable'])){
            $isEditable = $exp['isEditable'];
        } else {
            $isEditable = 1;
        }

        if ($isEditable == 1){
            $text = "<textarea name='{$name}' id='{$id}' class='{$class}' {$prePopText}>" .
                    "{$value}" .
                    "</textarea>";
        } else {
            $text = $value;
        }

        return $text;
    }

    function getCheckboxFldObj($name, $selectedValue = '', $id = '', $exp = array()){
        $fn = Zend_Registry::get('fn');

        if ($id == '') {
            $id = 'fld_' . $name;
        }

        $class       = null;
        $isEditable  = null;
        $displayArr  = null;
        $checkboxVal = null;

        $defOptsArr = array(
            'class' => ''
           ,'isEditable' => $this->mode == 'detail' ? 0 : 1
           ,'displayArr' => $this->checkBoxYNDispArr
           ,'checkboxVal' => 1
        );
        $exp = array_merge($defOptsArr, $exp);
        extract($exp);

        if ($isEditable == 0){
        }

        if ($isEditable == 1){
            $checked = $selectedValue ? 'checked' : '';
            $text = "<input type='checkbox' name='{$name}' id='{$id}' " .
                    "value='{$checkboxVal}' {$checked} class='{$class}'>";
        } else {
            if (isset($displayArr[$selectedValue])) {
                $text = $displayArr[$selectedValue];
            } else {
                $text = $selectedValue;
            }
        }

        return $text;
    }

    function getSelectFldObj($name, $value = '', $id = '', $exp = array()){
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');

        if ($id == '') {
            $id = 'fld_' . $name;
        }
        $class = null;
        $ddSQL = null;
        $valueArr = null;
        $isEditable = null;
        $sqlType = null;
        $dataType = null;
        $detailText = null;

        $defOptsArr = array(
            'class' => ''
           ,'ddSQL' => ''
           ,'valueArr' => array()
           ,'isEditable' => 1
           ,'sqlType' => 'OneField'
           ,'dataType' => 'sql' //sql or array
           ,'detailText' => ''
        );
        $exp = array_merge($defOptsArr, $exp);
        extract($exp);

        if ($this->mode == 'detail'){
            $isEditable = 0;
        }

        if ($isEditable == 1){
            $ddText = '';
            if ($dataType == 'sql') {
                if ($sqlType == 'TwoFields'){
                    $ddText .= $dbUtil->getDropDownFromSQLCols2($db, $ddSQL, $value);

                } else if ($sqlType == 'OneField'){
                    $ddText .= $dbUtil->getDropDownFromSQLCols1($db, $ddSQL, $value);
                }
            } else {

            }
            if ($sqlType == 'TwoFields'){
                $useKey = true;
                $ddText .= $cpUtil->getDropDown1($valueArr, $value, $useKey);

            } else if ($sqlType == 'OneField'){
                $useKey = false;
                $ddText .= $cpUtil->getDropDown1($valueArr, $value, $useKey);
            }
            $text = "
            <select id='{$id}' class='{$class}' name='{$name}'>
                <option value=''></option>
                {$ddText}
            </select>
            ";
        } else {
            if ($detailText != '') {
                $text = $detailText;
            } else {
                $text = $value;
            }
        }
        return $text;
    }

    function getDisplayFldObj($name, $value = '', $id = '', $exp = array()){
        $fn = Zend_Registry::get('fn');

        $idHiddenFld = '';
        if ($id == '') {
            $id = 'txt_' . $name;
            $idHiddenFld = 'fld_' . $name;
        }
        $class = $fn->getIssetParam($exp, 'class');

        $text = "
        <div id='{$id}' value='{$value}' class='{$class}'>{$value}</div>
        <input type='hidden' name='{$name}' id='{$idHiddenFld}' value='{$value}'>
        ";

        return $text;
    }

    function getYesNoRadioFldObj($name, $selectedValue = '', $exp = array()){
        $valuesArr = array('1' => 'Yes', '0' => 'No');
        return $this->getRadioFldObj($name, $selectedValue, $valuesArr, $exp);
    }

    function getRadioFldObj($name, $selectedValue = '', $valuesArr = array(), $exp = array()){
        $useKey = null;
        $isEditable = null;

        $defOptsArr = array(
           'useKey' => true
          ,'isEditable' => $this->mode == 'detail' ? 0 : 1
        );
        $exp = array_merge($defOptsArr, $exp);
        extract($exp);

        if ($isEditable == 0){
            if ($useKey) {
                return $valuesArr[$selectedValue];
            } else {
                return $selectedValue;
            }
        }

        $text = '';
        $counter = 1;
        foreach ($valuesArr as $key => $value) {
            $key     = ($useKey == 1) ? $key : $value;
            $checked = ($key == $selectedValue) ? 'checked' : '';

            $id = "{$name}_{$counter}";
            $text .= "
                <input type='radio' name='{$name}' value='{$key}' id='{$id}'
                       {$checked}>
                <label for='{$id}'>{$value}</label>
            ";

            $counter++;
        }

        return $text;
    }

    function getDivObj($content = '', $attrs = array()){
        $attrText = $this->getAttributes($attrs);
        $text = "<div{$attrText}>{$content}</div>";

        return $text;
    }

    function getAttributes($attrs){
        $text = '';
        foreach($attrs as $key => $value) {
            $text .= " " . $key . "=\"" . $value . "\"";
        }
        return $text;
    }

    /**
     *
     */
    function getUploadRow($displayTitle, $fieldName, $extraParam = array()){
        $cpUtil = Zend_Registry::get('cpUtil');
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $exp = &$extraParam;
        $multiple = $fn->getIssetParam($exp, 'multiple', true);

        $multipleTxt = ($multiple == true) ? " multiple='multiple'" : '';
        $fldId   = isset($exp['fldId']) ? $exp['fldId'] : "fld_{$fieldName}";
        $clsName = "row_{$fieldName}";

        $text = "
        <div class='type-text ym-fbox-text {$clsName}'>
            <label for='fld_{$fieldName}'>{$displayTitle}</label>
            <input name='{$fieldName}' type='file' id='{$fldId}'{$multipleTxt}>
        </div>
        ";

        return $text;
    }

    /**
     *
     * @return string
     */
    function getRequiredText(){
        $text = " <span class='required ym-required'>*</span>";
        return $text;
    }

    /**
     *
     * @param <type> $displayTitle
     * @param <type> $fieldName
     * @param <type> $antiSpamExplanation
     * @return <type>
     */
    function getCaptchaRobot($fieldName, $extraParam = array()){
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');

        $exp = &$extraParam;
        $rowCls           = isset($exp['rowCls'])        ? " {$exp['rowCls']}"                : "";
        $fieldLabelCls    = isset($exp['fieldLabelCls']) ? " class='{$exp['fieldLabelCls']}'" : "";
        $fieldCls         = isset($exp['fieldCls'])      ? " class='{$exp['fieldCls']}'"      : "";
        $inputType        = isset($exp['password'])      ? "password"           : "text";
        $reloadBtnCls     = isset($exp['reloadBtnCls'])  ? $exp['reloadBtnCls'] : "";
        $required         = isset($exp['required'])      ? $exp['required']     : true;
        $tabIndex         = isset($exp['tabindex']) ? "tabindex={$exp['tabindex']}" : '';

        $requiredText = '';
        $requiredFldText = '';
        if ($required) {
            $requiredText = $this->getRequiredText();
            $requiredFldText = "required='require'  aria-required='true'";
        }

        $cp_lib_path_alias = CP_LIBRARY_PATH_ALIAS;
        $clsName = "row_{$fieldName}";

        $text = "
        <div class='type-text ym-fbox-text {$clsName}{$rowCls}'>
            <label for='fld_{$fieldName}'></label>
            <div class='txt' id='fld_{$fieldName}'>
                <span class='value'>&nbsp;</span>
            </div>
            <a class='mb5 reloadRobotCaptcha' onclick='Util.reloadRobotCaptcha()'><span class='glyphicon glyphicon-repeat'></span> Reload Captcha</a>
            <script src='https://www.google.com/recaptcha/api.js'></script>
            <div class='g-recaptcha' data-sitekey='{$cpCfg['cp.dataRobotSiteKey']}'></div>
        </div>
        ";

        return $text;
    }

}
