<?
class CP_Www_Widgets_Event_EventItem_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget($exp = array()){
        $viewHelper = Zend_Registry::get('viewHelper');
        $c = &$this->controller;

        $rowsHTML = $this->getRowsHTML();
        $text = '';

        if ($rowsHTML != ""){
            $text = "
            <table class='thinlist'>
                <thead>
                    {$this->getTHeadRow()}
                </thead>
                <tbody>
                    {$rowsHTML}
                </tbody>
            </table>
            {$viewHelper->getWidgetPropertiesInHiddenVariable($c->name, $c)}
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
            $qtyRow = "<th class='qty txtCenter'>{$ln->gd('w.event.eventItem.lbl.quantity')}</th>";
        }

        $eventRec = $fn->getRecordRowByID('event', 'event_id', $c->eventId);
        $price = ($eventRec['free'] == 1) ? '' : "<th class='unit txtRight'>{$ln->gd('w.event.eventItem.lbl.unitPrice')} <span class='currency'>({$c->currencyDisplay})</span></th>";

        $text = "
        <th class='select'>&nbsp;</th>
        <th class='title'>{$ln->gd('w.event.eventItem.lbl.title')}</th>
        {$qtyRow}
        {$price}
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
//            if(is_array($c->defaultEventItemId)){//checkbox
//                $selected = in_array($row['event_item_id'], $c->defaultEventItemId) ? "checked='checked'" : '';
//            } else

            if(is_numeric($c->defaultEventItemId)){//radiobutton
                $selected = ($c->defaultEventItemId == $row['event_item_id']) ? "checked='checked'" : '';
            } else {
                $selected = ($rows == '') ? "checked='checked'" : '';
            }

            $qtyRow = $this->getQtyText($row);
            if($c->showQtyDropDown){
                $qtyRow = "<td class='qty txtCenter'>{$this->getQtyText($row)}</td>";
            }

            $inputType = ($c->selectMultipeEventItem) ? 'checkbox' : 'radio';
            $inputName = ($c->selectMultipeEventItem) ? 'event_item_id[]' : 'event_item_id';

            $eventRec = $fn->getRecordRowByID('event', 'event_id', $row['event_id']);
            $price = ($eventRec['free'] == 1) ? '' : "<td class='unit txtRight'>{$row[$c->unitPriceFld]}</td>";
            
            $rows .= "
            <tr>
                <td class='select'><input type='{$inputType}' name='{$inputName}' value='{$row['event_item_id']}' {$selected}/></td>
                <td class='title'>{$row['title']}</td>
                {$qtyRow}
                {$price}
            </tr>
            ";
        }

        return $rows;
    }

    /**
     *
     */
    function getButtons() {
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpCfg = Zend_Registry::get('cpCfg');

        $basketArray = $cpCfg['cp.basketArray'][$c->modName];
        $shopUrl = $cpUrl->getUrlBySecType($basketArray['sectionType']);
        $shipUrl = $cpUrl->getUrlByCatType('Shipping Details', $basketArray['basketSecType']);

        $text = "
        <div class='floatbox shopBtns' modName='{$c->modName}'>
            <div class='float_left button btnContinueShopping'>
                <a href='{$shopUrl}'>
                    {$ln->gd($c->continueShopping)}
                </a>
            </div>
            <div class='float_left button btnEmptyCart'>{$ln->gd($c->emptyCart)}</div>
            <div class='float_right button btnCheckout'>
                <a href='{$shipUrl}'>
                    {$ln->gd($c->checkout)}
                </a>
            </div>
        </div>
        ";

        return $text;
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
            for($i = 1; $i <= $stock; $i++){
                $arr[] = $i;
            }
            $text = "
            <select name='qty_{$row['event_item_id']}' event_item_id='{$row['event_item_id']}'>
                {$cpUtil->getDropDown1($arr, 1, false)}
            </select>
            ";
        } else {
            $text = "
            <input type='hidden' name='qty_{$row['event_item_id']}' event_item_id='{$row['event_item_id']}'>
            ";
        }

        return $text;
    }
}