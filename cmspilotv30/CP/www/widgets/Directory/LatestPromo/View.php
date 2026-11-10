<?
class CP_Www_Widgets_Directory_LatestPromo_View extends CP_Common_Lib_WidgetViewAbstract
{
    //========================================================//
    function getWidget() {
        $ln = Zend_Registry::get('ln');

        $text = "
        <h1>{$ln->gd('w.directory.latestPromo.heading')}</h1>
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
        $ln = Zend_Registry::get('ln');

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