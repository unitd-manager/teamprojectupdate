<?
class CP_Www_Widgets_Media_Supersized_Model extends CP_Common_Lib_WidgetModelAbstract
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
        $c = &$this->controller;

        if ($c->module != '' && $c->mediaType != '' && $c->recordId != ''){
            $dataArray = $media->model->getMediaFilesArray($c->module, $c->mediaType, $c->recordId);
        }

        if (count($dataArray) == 0){
            $wBanner = getCPWidgetObj('media_banner', false);
            $dataArray = $wBanner->getWidget(array(
                'returnDataOnly' => true
            ));
        }
        return $dataArray;
    }
}