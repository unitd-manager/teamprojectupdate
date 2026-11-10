<?
class CP_Www_Widgets_Media_Carousel_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $tv = Zend_Registry::get('tv');

        $dataArray = array();
        
        $c = &$this->controller;
        if ($c->type == 'picture'){
            
            $exp = array('returnDimensions' => true);
            
            if($c->module == 'webBasic_section'){
                $dataArray = $media->getMediaFilesArray($c->module, $c->mediaRecType, $tv['room'], $exp);
            } else {
                $dataArray = $media->getMediaFilesArray($c->module, $c->mediaRecType, $c->record_id, $exp);
            }
            
        } else if ($c->type == 'picByContentType'){
            $wContentRec = getCPWidgetObj('content_record');
            $contentArr = $wContentRec->getWidget(array(
                 'contentType' => $c->contentType
                ,'returnDataOnly' => true
            ));
            
            if (count($contentArr) > 0){
                $exp = array('returnDimensions' => true);
                $dataArray = $media->getMediaFilesArray('webBasic_content', 'picture', $contentArr[0]['content_id'], $exp);
            }
            
        } else if ($c->type == 'picByProductType'){
            $wProductRec = getCPWidgetObj('ecommerce_productRecord');
            $productArr = $wProductRec->getWidget(array(
                'returnDataOnly' => true
            ));
            if (count($productArr) > 0){
                $exp = array('returnDimensions' => true);
                $dataArrayTemp = array();
                $counter = 0;
                foreach ($productArr as $row){
                    $dataArrayTemp[$counter] = $media->getMediaFilesArray('ecommerce_product', 'picture', $row['product_id'], $exp);
                    $counter++;
                }
                
                $dataArray = array();
                for ($i = 0; $i< count($dataArrayTemp); $i++){
                    $dataArray = array_merge($dataArray, $dataArrayTemp[$i]);
                }
            }
        } else if ($c->type == 'picRelatedPic'){
            $module      = $c->module;
            $recordType1 = $c->recordType1;
            $recordType2 = $c->recordType2;
            $record_id   = $c->record_id;
            
            $exp = array('colorId' => $c->colorId, 'childId' => $c->childId, 'returnDimensions' => true);
            $arr1 = $media->model->getMediaFilesArray($module, $recordType1, $record_id, $exp);
            $arr2 = array();
    
            if (!$this->controller->useRecType1Only){
                $arr2 = $media->model->getMediaFilesArray($module, $recordType2, $record_id, $exp);
            }
            $dataArray = array_merge($arr1, $arr2);
        }

        $arr = array();

        $counter = 0;

        $mediaFolder = $c->mediaFolderThumb;
        if ($c->thumbnailNav) {
            $mediaFolder = $c->mediaFolderLarge;
        }
        
        foreach($dataArray AS $row) {
            $arrTemp = &$arr[$counter];
            $arrTemp['pic']         = "{$cpCfg['cp.mediaFolderAlias']}{$mediaFolder}/{$row['file_name']}";
            $arrTemp['link']        = $cpUrl->getExtIntUrl($row);
            
            if ($arrTemp['link'] == '' && $c->url != ''){
                $arrTemp['link'] = $c->url;
            }
            
            $arrTemp['caption']     = $row['caption'];
            $arrTemp['description'] = $row['description'];
            $arrTemp['imgWidth']    = $row[$mediaFolder . '_w']; // actual width & height of the image is required to be set for this plugin
            $arrTemp['imgHeight']   = $row[$mediaFolder . '_h'];
            $counter++;
        }
        
        return $arr;
    }
}