<?
class CP_Www_Widgets_Ecommerce_ProductList_View extends CP_Common_Lib_WidgetViewAbstract
{
    var $jssKeys = array('jqCalculation-0.4.08');
    /**
     *
     */
    function getWidget($exp = array()){
        $viewHelper = Zend_Registry::get('viewHelper');
        $ln = Zend_Registry::get('ln');
        $cpCfg = Zend_Registry::get('cpCfg');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        $heading = ($c->showHeading) ? "<h1>{$ln->gd($c->heading)}</h1>" : '';

        $infoText = $ln->gd2($c->infoText);

        if ($infoText != ''){
            $infoText = "<div class='infoText'>{$infoText}</div>";
        }

        if ($rowsHTML != ""){
            $text = "
            {$heading}
            {$infoText}
            <table class='thinlist'>
                <thead>
                    {$this->getTHeadRow()}
                </thead>
                <tbody>
                    {$rowsHTML}
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class='txtRight'>{$ln->gd($c->grossTotalLbl)}</td>
                        <td id='grossTotal' class='txtRight'></td>
                    </tr>
                </tbody>
            </table>
            {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
            <input id='addToCartUrl' type='hidden' value='/index.php?widget=ecommerce_addToCart&_spAction=add&showHTML=0&&modName={$c->modName}'>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getTHeadRow() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;

        $qtyRow = '';
        if($c->showQtyDropDown){
            $qtyRow = "
            <th class='qty txtCenter'>
                {$ln->gd($c->qtyLbl)}
            </th>";
        }

        $text = "
        <th class='select txtCenter'>&nbsp;</th>
        <th class='title'>{$ln->gd($c->titleLbl)}</th>
        <th class='unit txtRight'>
            {$ln->gd($c->priceLbl)}
            <span class='currency'>({$c->currencyDisplay})</span>
        </th>
        {$qtyRow}
        <th class='total txtRight'>
            {$ln->gd($c->totalLbl)}
            <span class='currency'>({$c->currencyDisplay})</span>
        </th>
        ";

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $media = Zend_Registry::get('media');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        $rows = '';

        foreach ($this->model->dataArray as $key => $row) {
            $qtyRow = $this->getQtyText($row);
            if($c->showQtyDropDown){
                $qtyRow = "<td class='qty txtCenter'>{$this->getQtyText($row)}</td>";
            }
            
            $inputType = ($c->selection) ? 'checkbox' : 'radio';
            $inputName = ($c->selection == 'checkbox') ? $c->keyFldName . '[]' : $c->keyFldName;
            $price = number_format($row[$c->unitPriceFld], $c->decimals);
            
            $notes = '';
            if ($c->showNotesPerItem){
                $notesLbl = $ln->gd2($c->notesLbl);
        
                if ($notesLbl != ''){
                    $notesLbl = "<div class='infoText'>{$notesLbl}</div>";
                }

                $preText = $ln->gd2($c->notesPreText);
        
                if ($preText != ''){
                    $preText = " rel='pptxt: {$preText}'";
                }

                $notes = "
                <div class='notesWrap'>
                    {$notesLbl}
                    <textarea class='notes' name='notes_{$row[$c->keyFldName]}' {$preText}>{$row['notes']}</textarea>
                </div>
                ";
            }
            
            $checked = ($row['qty'] > 0) ? "checked='checked'" : '';
            
            $rows .= "
            <tr unitPrice='{$row[$c->unitPriceFld]}'>
                <td class='select txtCenter'>
                    <input type='{$c->selection}' name='{$inputName}' value='{$row[$c->keyFldName]}'{$checked}>
                </td>
                <td class='title'>{$row['title']}{$notes}</td>
                <td class='unitPrice txtRight'>{$price}</td>
                {$qtyRow}
                <td class='total txtRight'></td>
            </tr>
            ";
        }

        return $rows;
    }

    /**
     *
     */
    function getQtyText($row) {
        $cpUtil = Zend_Registry::get('cpUtil');
        $c = &$this->controller;

        if($c->showQtyDropDown){
            $stock = $c->maxQuantity;

            $arr = array();
            for($i = 0; $i <= $stock; $i++){
                $arr[] = $i;
            }
            $text = "
            <select class='input' name='qty_{$row[$c->keyFldName]}' {$c->keyFldName}='{$row[$c->keyFldName]}'>
                {$cpUtil->getDropDown1($arr, $row['qty'])}
            </select>
            ";
        } else {
            $text = "
            <input class='input' type='hidden' name='qty_{$row[$c->keyFldName]}' {$c->keyFldName}='{$row[$c->keyFldName]}'>
            ";
        }

        return $text;
    }
}