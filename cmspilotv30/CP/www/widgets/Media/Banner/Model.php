<?
class CP_Www_Widgets_Media_Banner_Model extends CP_Common_Lib_WidgetModelAbstract
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
        $global = $c->global;
        $strictToPage = $c->strictToPage;

        $exp = array('returnDimensions' => true);
        if ($global == true){

        } else if ($strictToPage == 1){
            if ($tv['subCat'] != '' && is_numeric($tv['subCat'])){
                $dataArray = $media->getMediaFilesArray('webBasic_subCategory', $c->mediaRecordType, $tv['subCat'], $exp);

            } else if ($tv['subRoom'] != '' && is_numeric($tv['subRoom'])){
                $dataArray = $media->getMediaFilesArray('webBasic_category', $c->mediaRecordType, $tv['subRoom'], $exp);

            } else if ($tv['room'] != '' && is_numeric($tv['room'])){
                $dataArray = $media->getMediaFilesArray('webBasic_section', $c->mediaRecordType, $tv['room'], $exp);
            }

        } else {
            $found = false;

            if ($tv['subCat'] != "" && is_numeric($tv['subCat'])){
                $dataArray = $media->getMediaFilesArray('webBasic_subCategory', $c->mediaRecordType, $tv['subCat'], $exp);
                $found = count($dataArray) > 0 ? true : false;
            }

            if ($tv['subRoom'] != "" && is_numeric($tv['subRoom']) && !$found){
                $dataArray = $media->getMediaFilesArray('webBasic_category', $c->mediaRecordType, $tv['subRoom'], $exp);
                $found = count($dataArray) > 0 ? true : false;
            }

            if ($tv['room'] != "" && is_numeric($tv['room']) && !$found){
                $dataArray = $media->getMediaFilesArray('webBasic_section', $c->mediaRecordType, $tv['room'], $exp);
            }
        }

        return $dataArray;
    }
}