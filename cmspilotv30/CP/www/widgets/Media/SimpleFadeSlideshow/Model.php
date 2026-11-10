<?
class CP_Www_Widgets_Media_SimpleFadeSlideshow_Model extends CP_Common_Lib_WidgetModelAbstract
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

        $type = $this->controller->type;

        if ($type == 'banner'){
            $dataArray = $media->getMediaFilesArray('webBasic_section', 'banner', $tv['room']);
        }

        $arr = array();

        $counter = 0;

        foreach($dataArray AS $row) {
            $arrTemp = &$arr[$counter];
            $arrTemp['pic']         = "{$cpCfg['cp.mediaFolderAlias']}large/{$row['file_name']}";
            $arrTemp['link']        = $cpUrl->getExtIntUrl($row);
            $arrTemp['caption']     = $ln->gfv($row, 'caption');
            $arrTemp['description'] = $ln->gfv($row, 'description');
            $counter++;
        }
        return $arr;
    }
}