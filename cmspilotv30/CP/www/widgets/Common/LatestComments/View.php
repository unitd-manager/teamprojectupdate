<?
class CP_Www_Widgets_Common_LatestComments_View extends CP_Common_Lib_WidgetViewAbstract
{
    //========================================================//
    function getWidget($exp = array()) {
        $ln = Zend_Registry::get('ln');

        $text = "
        <h1>{$ln->gd('w.common.latestComments.heading')}</h1>
        <div class='wrapper'>
            <ul class='noDefault'>
                {$this->getRowsHTML()}
            </ul>
        </div>
        ";
        return $text;
    }

    //========================================================//
    function getRowsHTML() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');

        $rows = '';
        foreach($this->model->dataArray AS $row) {
            $rows .= "
            <li>
                <h3>Lorem ipsum dolor sit amet</h3>
                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.</p>
            </li>
            <li>
                <h3>Lorem ipsum dolor sit amet</h3>
                <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa.</p>
            </li>
            ";
        }

        return $rows;
    }
}