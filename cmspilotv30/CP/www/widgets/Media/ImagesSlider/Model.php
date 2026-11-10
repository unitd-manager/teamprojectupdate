<?
class CP_Www_Widgets_Media_ImagesSlider_Model extends CP_Common_Lib_WidgetModelAbstract
{

    /**
     *
     */
    function getDataArray() {
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');

        $module      = $this->controller->module;
        $recordType1 = $this->controller->recordType1;
        $recordType2 = $this->controller->recordType2;
        $record_id   = $this->controller->record_id;

        $arr1 = $media->model->getMediaFilesArray($module, $recordType1, $record_id);
        $arr2 = array();

        if (!$this->controller->useRecType1Only){
            $arr2 = $media->model->getMediaFilesArray($module, $recordType2, $record_id);
        }

        $this->dataArray = array_merge($arr1, $arr2);
        $this->controller->totalRecords = count($this->dataArray);
        return $this->dataArray;
    }
}