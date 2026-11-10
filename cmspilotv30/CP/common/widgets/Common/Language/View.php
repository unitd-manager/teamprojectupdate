<?
class CP_Common_Widgets_Common_Language_View extends CP_Common_Lib_WidgetViewAbstract
{

    var $currLangValue;
    /**
     *
     */
    function getWidget() {
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $c = &$this->controller;
        $surroundUl = $c->surroundUl;
        $showAsMenu = $c->showAsMenu;
        $rowsHTML   = $this->getRowsHTML();
        if ($surroundUl) {

            if ($showAsMenu){

                if ($tv['lang'] != '') {
                    $title = "
                    <div class='title'>
                        <div class='selected {$tv['lang']}'>
                            {$this->currLangValue}
                        </div>
                    </div>
                    ";
                } else {
                    $title = "<div class='title'>{$ln->gd($c->menuTitle)}</div>";
                }

                $text = "
                <div class='language_menu'>
                    {$title}
                    <ul class='{$c->ulClass} noDefault'>
                        {$rowsHTML}
                    </ul>
                </div>
                ";

            } else {
                $text = "
                <ul class='{$c->ulClass} noDefault'>
                    {$rowsHTML}
                </ul>
                ";
            }
        } else {
            $text = $rowsHTML;
        }

        return $text;
    }

    /**
     *
     */
    function getRowsHTML() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $pager = Zend_Registry::get('pager');

        $rows = '';

        foreach($this->model->dataArray AS $langKey => $row) {
            if ($tv['lang'] == $langKey) {
                $this->currLangValue = $row['title'];
                if (!$this->controller->hideCurrLang) {
                    $rows .= "
                    <li class='active'>
                        <a href='{$row['url']}'>{$row['title']}</a>
                    </li>\n
                    ";
                }
            } else {
                $rows .= "
                <li>
                    <a href='{$row['url']}'>{$row['title']}</a>
                </li>\n
                ";
            }
        }

        return $rows;
    }
}