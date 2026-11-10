<?
class CP_Www_Widgets_Ads_Banner_View extends CP_Common_Lib_WidgetViewAbstract
{
    //========================================================//
    function getWidget() {
        $fn = Zend_Registry::get('fn');
        $c = &$this->controller;
        
        $text = '';
        if($this->getRowsHTML() != ''){
            $text = "
            <ul class='noDefault'>
                {$this->getRowsHTML()}
            </ul>
            ";
        }


        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $cpUrl = Zend_Registry::get('cpUrl');

        $rows = '';
        foreach($this->model->dataArray AS $row) {
            foreach($row AS $rowMedia){
                $pic = "<img src='{$rowMedia['file_large']}'/>";

                $url = $cpUrl->getExtIntUrl($rowMedia);
                if ($url != '') {
                    $target = ($rowMedia['internal_link'] == '') ? "target='_blank'" : '';
                    $pic = "<a href='{$url}' {$target}>{$pic}</a>";
                }

                $rows .="
                <li>
                    {$pic}
                </li>
                ";
            }
        }

        return $rows;
    }
}
