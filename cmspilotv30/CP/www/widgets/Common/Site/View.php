<?
class CP_Www_Widgets_Common_Site_View extends CP_Common_Lib_WidgetViewAbstract
{
    /**
     *
     */
    function getWidget($exp = array()){
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;
        
        $rowsHTML   = $this->getRowsHTML();
        $ulClass    = $c->ulClass;
        $title      = $c->title;
        $showAsMenu = $c->showAsMenu;

        //******************************************************************//
        $title = ($title != '') ? "<div class='title'>{$title}</div>" : '';

        $text = '';
        if ($rowsHTML != ""){
            $ulClass = ($ulClass !='') ? " class='{$ulClass}'" : '';

            if ($showAsMenu){
                if ($cpCfg['cp.site_id'] != '') {
                    $country_code = $fn->getIssetParam($cpCfg['cp.siteRow'],'country_code');
                    $title = "
                    <div class='title'>
                        <div class='selected {$country_code}'>
                            {$ln->gfv($cpCfg['cp.siteRow'], 'title')}
                        </div>
                    </div>
                    ";
                } else {
                    $title = "<div class='title'>{$ln->gd($c->menuTitle)}</div>";
                }

                $text = "
                <div class='site_menu'>
                    {$title}
                    <ul class='{$c->ulClass} noDefault'>
                        {$rowsHTML}
                    </ul>
                </div>
                ";
            } else {
                $text = "{$title}<ul{$ulClass}>{$rowsHTML}</ul>";
            }
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        
        $site_id = $fn->getReqParam('site_id');
        
        $rows = '';

        foreach ($this->model->dataArray as $key => $value ) {
            $row = $this->model->dataArray[$key];
            $url = $row["url"];

            if ($site_id == $key) {
                $rows .= "
                <li class='active {$row['code']}'>
                    <a href='{$url}' cid='{$row['id']}'>{$row['title']}</a>
                </li>\n
                ";
            } else {
                $rows .= "
                <li class='{$row['code']}'>
                    <a href='{$url}' cid='{$row['id']}'>{$row['title']}</a>
                </li>\n
                ";
            }
        }

        return $rows;
    }
}