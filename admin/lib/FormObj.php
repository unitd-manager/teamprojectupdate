<?
/**
 *
 */
class CPL_Admin_Lib_FormObj extends CP_Common_Lib_FormObj
{
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
        $placeHolder   = isset($exp['placeHolder'])    ? $exp['placeHolder']     : '';
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

        if($placeHolder != ""){
            $placeHolder = "placeholder='{$placeHolder}'";
        }

        if($isEditable == 1){
            $fieldValue = $cpUtil->replaceForFormField($fieldValue);
            $fieldValueTemp = "
            <input{$fieldCls}  allowEdit='{$allowEdit}' type='text' name='{$fieldName}' fldId='{$fldId}'
                 class='fld_date MainDateField' yearStart='{$yearStart}' yearEnd='{$yearEnd}' id='fld_{$fieldName}' " .
                 "minDate='{$minDate}' maxDate='{$maxDate}'" .
                 "value=\"{$fieldValue}\" dateFormat='{$dateFormat}' {$requiredFldText}>{$extraHtml}
            <input type='text' class='hiddenDateDisplay' name='hidden_date_display' data-onload='{$fieldValue}' {$placeHolder}>
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
        <script>
            $(function() {
                // Call the function on each input
                $('.hiddenDateDisplay[data-onload]').each(function() {
                    var dateCheck = $(this).attr('data-onload');
                    
                    if(dateCheck != '') {
                        var date      = dateCheck.replace(/-/g, '/');
                        var newdate   = new Date(date);
                        var dd = ('0' + newdate.getDate()).slice(-2);
                        var mm = ('0' + (newdate.getMonth() + 1)).slice(-2)
                        var y  = newdate.getFullYear();
             
                        var endDate = dd + '-'+ mm + '-' + y;
                    }else {
                        var endDate = '';
                    }

                    $(this).val(endDate);
                });
            });
        </script>
        <div class='type-text ym-fbox-text {$clsName}{$rowCls}'>
            <label{$fieldLabelCls} for='fld_{$fieldName}'>{$displayTitle}{$requiredText}</label>
            {$notes}
            {$fieldValueTemp}
        </div>
        ";

        return $text;
    }
}
