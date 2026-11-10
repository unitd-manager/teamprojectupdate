<?
class CP_Www_Modules_Ecommerce_Product_Model extends CP_Common_Modules_Ecommerce_Product_Model
{
    /**
     *
     */
    function getModuleDataArray(){
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $tv = Zend_Registry::get('tv');
        $media = Zend_Registry::get('media');
        $cpUrl = Zend_Registry::get('cpUrl');
        $cpUtil = Zend_Registry::get('cpUtil');
        $modelHelper = Zend_Registry::get('modelHelper');
        $modelHelper->setModuleDataArray();
        $dataArray = $this->dataArray;

        $arr = array();
        foreach($dataArray AS $row){
            $picArray = $media->getFirstMediaRecord('ecommerce_product', 'picture', $row['product_id']);

            if (count($picArray) == 0){
                $picArray = array(
                    'file_thumb'  => '//placehold.it/116x75',
                    'file_normal' => '//placehold.it/200x200',
                    'file_large' => '//placehold.it/300x300',
                );
            }
            $row['file_thumb'] = $picArray['file_thumb'];
            $row['file_normal'] = $picArray['file_normal'];
            $row['file_large'] = $picArray['file_large'];

            $row['url'] = $cpUrl->getUrlByRecord($row, 'product_id');

            if ($tv['action'] == 'detail'){
                $row['relPicArray'] = $media->getMediaFilesArray('ecommerce_product', 'relatedPicture', $row['product_id']);
            }

            $arr[] = $row;
        }

        return $arr;
    }

    //==================================================================//
    function getTwigParams() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $pager = Zend_Registry::get('pager');
        $theme = getCPThemeObj($cpCfg['cp.theme']);
        $wBread = getCPWidgetObj('common_breadcrumb');

        $params = array(
            'breadcrumbs' => $wBread->getWidget(array('showPrefixText' => true)),
            'pagerData' => $pager->getNavButtonsData(),
        );

        return $params;
    }

}
