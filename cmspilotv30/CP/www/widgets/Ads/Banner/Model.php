<?
class CP_Www_Widgets_Ads_Banner_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getSQL(){
        $SQL = "
        SELECT bl.*
        FROM banner_link bl
        JOIN banner b ON (bl.banner_id = b.banner_id)
        ";

        return $SQL;
    }

    /**
     *
     */
    function setSearchVar() {
        $searchVar = $this->searchVar;
        $tv = Zend_Registry::get('tv');
        $searchVar->mainTableAlias = 'bl';

        $c = &$this->controller;
        $position = $c->position;
        
        $searchVar->sqlSearchVar[] = "bl.published = 1";
        $searchVar->sqlSearchVar[] = "bl.module = '{$c->module}'";
        $searchVar->sqlSearchVar[] = "bl.banner_position = '{$c->position}'";
        
        if ($c->record_id != ''){
            $searchVar->sqlSearchVar[] = "bl.record_id = '{$c->record_id}'";
        } else if ($tv['room'] != ''){
            $searchVar->sqlSearchVar[] = "bl.record_id = '{$tv['room']}'";
        }

        foreach($c->addSearchCondArr AS $cond) {
            $searchVar->sqlSearchVar[] = $cond;
        }        
        
        $searchVar->sortOrder = "{$c->sort_order}";
    }

    /**
     *
     */
    function getDataArray() {
        $db = Zend_Registry::get('db');
        $media = Zend_Registry::get('media');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $c = &$this->controller;

        $modelHelper = Zend_Registry::get('modelHelper');
        $recArray = $modelHelper->getWidgetDataArray($this->controller, 'ads_banner');
        
        $dataArray = array();
        foreach($recArray AS $row) {
            $dataArray[] = $media->getMediaFilesArray('ads_banner', 'picture', $row['banner_id']);
        }

        return $dataArray;
    }
}