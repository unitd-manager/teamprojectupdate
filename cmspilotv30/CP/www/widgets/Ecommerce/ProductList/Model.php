<?
class CP_Www_Widgets_Ecommerce_ProductList_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT p.*
              ,p.title
        FROM product p
        ";
        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;

        $searchVar->sqlSearchVar[] = "p.published = 1";
        $searchVar->sortOrder = $c->orderBy;
    }

    /**
     *
     */
    function getDataArray(){
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;
        $basket = $cpCfg['cp.basketArray'][$c->modName];
        
        $c->currency         = ($c->currency         != '') ? $c->currency         : $basket['currency'];
        $c->decimals         = ($c->decimals         != '') ? $c->decimals         : $basket['decimals'];
        $c->keyFldName       = ($c->keyFldName       != '') ? $c->keyFldName       : $basket['keyField'];
        $c->currencyDisplay  = ($c->currencyDisplay  != '') ? $c->currencyDisplay  : $basket['currencyDisplay'];
        $c->showNotesPerItem = ($c->showNotesPerItem != '') ? $c->showNotesPerItem : $basket['showNotesPerItem'];

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'ecommerce_productList');

        $wBasket = getCPWidgetObj('ecommerce_basket');

        $basketArr = $wBasket->getWidget(array(
             'modName' => $c->modName
            ,'returnDataOnly' => true
        ));

        foreach ($dataArray as $key => $row) {
            $arr[$key] = array();
            $arrTemp = &$arr[$key];
            
            $keyId = $row[$c->keyFldName];
            $arrTemp[$c->keyFldName]   = $keyId;
            $arrTemp['title']          = $ln->gfv($row, 'title');
            $arrTemp[$c->unitPriceFld] = $row[$c->unitPriceFld];
            $arrTemp['qty']            = 0;
            $arrTemp['notes']          = '';
            
            foreach ($basketArr as $keyBkt => $rowBkt) {
                if($rowBkt['record_id'] == $keyId){
                    $arrTemp['qty'] = $rowBkt['qty'];
                    $arrTemp['notes'] = isset($rowBkt['notes']) ? $rowBkt['notes'] : '';
                }
            }
        }
        
        $this->dataArray = $arr ;
        return $arr;
    }

}