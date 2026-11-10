<?
class CP_Www_Widgets_Ui_Tabs_Model extends CP_Common_Lib_WidgetModelAbstract
{
    /**
     *
     */
    function getDataArray() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');

        $c = &$this->controller;
        $arr = array();

        $counter = 0;

        if ($c->type == 'byArray'){
            foreach($c->contentArray AS $content) {
                $arrTemp = &$arr[$counter];
                $arrTemp['description'] = $content;
                $counter++;
            }
            $c->lblArray = $c->lblArray;
        }

        if ($c->type == 'byContentDataArray'){
            $lblArray = array();
            foreach($c->contentArray AS $row) {
                $lblArray[] = $ln->gfv($row, 'title');
            }
            
            $c->lblArray = $lblArray;
            $arr = $c->contentArray;
        }

        return $arr;
    }
}