<?
class CP_Www_Widgets_Media_AnythingSlider_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getDataArray() {
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $cpUrl = Zend_Registry::get('cpUrl');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $dataArray = array();

        $c = $this->controller;

        if ($c->type == 'picture'){
            $dataArray = $media->getMediaFilesArray('webBasic_section', 'banner', $tv['room']);
        }

        if ($c->type == 'picByContentType'){
            $wContentRec = getCPWidgetObj('content_record');
            $contentArr = $wContentRec->getWidget(array(
                 'contentType' => $c->contentType
                ,'returnDataOnly' => true
            ));

            if (count($contentArr) > 0){
                $dataArray = $media->getMediaFilesArray('webBasic_content', 'picture', $contentArr[0]['content_id']);
            }
        }

        $arr = array();

        $counter = 0;

        foreach($dataArray AS $row) {
            $arrTemp = &$arr[$counter];
            $arrTemp['pic']     = "{$cpCfg['cp.mediaFolderAlias']}large/{$row['file_name']}";
            $arrTemp['link']    = $fn->replaceUrlVars($row['url']);
            $arrTemp['caption'] = $row['caption'];
            $arrTemp['description'] = nl2br($row['description']);
            $counter++;
        }
        return $arr;
    }
}