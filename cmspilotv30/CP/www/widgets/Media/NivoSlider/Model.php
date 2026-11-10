<?
class CP_Www_Widgets_Media_NivoSlider_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getDataArray() {
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');

        $dataArray = array();

        $type = $this->controller->type;

        if ($type == 'picture'){
            $dataArray = $media->getMediaFilesArray('webBasic_section', 'banner', $tv['room']);
        }

        $arr = array();

        $counter = 0;

        foreach($dataArray AS $row) {
            $arrTemp = &$arr[$counter];
            $arrTemp['pic']     = "{$cpCfg['cp.mediaFolderAlias']}large/{$row['file_name']}";
            $arrTemp['link']    = $row['external_link'];
            $arrTemp['caption'] = $row['caption'];
            $counter++;
        }
        return $arr;
    }
}