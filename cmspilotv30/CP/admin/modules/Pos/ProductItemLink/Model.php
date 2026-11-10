<?
class CP_Admin_Modules_Pos_ProductItemLink_Model extends CP_Common_Lib_ModuleLinkModelAbstract
{
    /**
     *
     */
    function getFields() {
        $fn = Zend_Registry::get('fn');
        $modulesArr = Zend_Registry::get('modulesArr');

        $fa = array();

        $fa = $fn->addToFieldsArray($fa, 'sku_no');
        $fa = $fn->addToFieldsArray($fa, 'barcode');
        $fa = $fn->addToFieldsArray($fa, 'style_id');
        $fa = $fn->addToFieldsArray($fa, 'color_id');
        $fa = $fn->addToFieldsArray($fa, 'size_id');
        $fa = $fn->addToFieldsArray($fa, 'season_id');
        $fa = $fn->addToFieldsArray($fa, 'element_id');
        $fa = $fn->addToFieldsArray($fa, 'brand_id');

        return $fa;
    }

    /**
     *
     */
    function getAddNewGridItem(){
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $record_id = $fn->getReqParam('product_item_id');
        $cpCfg = Zend_Registry::get('cpCfg');

        $fa = $this->getFields();
        $fa['product_id'] = $tv['srcRoomId'];

        $id = $fn->addRecord($fa);
    }

    /**
     *
     */
    function getSaveGridItem(){
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');
        $record_id = $fn->getReqParam('product_item_id');
        
        $fa = $this->getFields();
        $sku_order = $fn->getSettingsValueByKey('prodSkuLayoutOrder');
        $orderArr = explode(',', $sku_order);
        
        $sku_no = '';
        $sep = $fn->getSettingsValueByKey('prodSkuSeparator');
                
        foreach($orderArr as $fld){
            $fld = trim($fld);
        
            if($fld == 'Style' && $cpCfg['prodEnableStyle'] == 1){
                $vlStyleRec = $fn->getRecordRowByID('valuelist', 'valuelist_id', $fa['style_id']);
                $sku_no .= $vlStyleRec['code'] . $sep;
            }
        
            if($fld == 'Color' && $cpCfg['prodEnableColor'] == 1){
                $vlColorRec = $fn->getRecordRowByID('valuelist', 'valuelist_id', $fa['color_id']);
                $sku_no .= $vlColorRec['code'] . $sep;
            }

            if($fld == 'Size' && $cpCfg['prodEnableSize'] == 1){
                $vlSizeRec = $fn->getRecordRowByID('valuelist', 'valuelist_id', $fa['size_id']);
                $sku_no .= $vlSizeRec['code'] . $sep;
            }
        
            if($fld == 'Season' && $cpCfg['prodEnableSeason'] == 1){
                $vlSeasonRec = $fn->getRecordRowByID('valuelist', 'valuelist_id', $fa['season_id']);
                $sku_no .= $vlSeasonRec['code'] . $sep;
            }

            if($fld == 'Brand' && $cpCfg['prodEnableBrand'] == 1){
                $vlBrandRec = $fn->getRecordRowByID('valuelist', 'valuelist_id', $fa['brand_id']);
                $sku_no .= $vlBrandRec['code'] . $sep;
            }
        
            if($fld == 'Element' && $cpCfg['prodEnableElement'] == 1){
                $vlElementRec = $fn->getRecordRowByID('valuelist', 'valuelist_id', $fa['element_id']);
                $sku_no .= $vlElementRec['code'] . $sep;
            }
        }
        
        $fa['sku_no'] = $sku_no . $record_id;
        //print_r ($fa);
        //return;
        $id = $fn->saveRecord($fa);
    }
}
