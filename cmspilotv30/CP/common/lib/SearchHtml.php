<?
class CP_Common_Lib_SearchHTML
{

    //========================================================//
    function SearchHTML() {
    }

    //========================================================//
    function getSearchHTML($moduleName) {
        $tv = Zend_Registry::get('tv');
        $ln = Zend_Registry::get('ln');

        if ($tv['action'] != 'list'){
            return;
        }

        $funcName = "getQuickSearch";

        $clsInst = Zend_Registry::get('currentModule')->view;

        if (method_exists($clsInst, $funcName)) {
            $searchText = $clsInst->$funcName();
        } else {
            return;
        }

        $tabFld = ($tv['tab'] != '') ? "<input type='hidden' name='_tab' value='{$tv['tab']}'>" : "";

        $keyword = $ln->gd('cp.lbl.keywordSearch', 'Keyword Search');

        if(CP_SCOPE == 'admin'){
            $tdPos = strpos($searchText, '</td>');
            if ($tdPos === false) {
                $text = "
                <div class='cpSearch'>
                    {$searchText}
                    <div class='keywordWrap'><input type='text' rel='pptxt: {$keyword}' name='keyword' value='{$tv['keyword']}'></div>
                    <div><input type='submit' value='GO' class='button'></div>
                </div>
                ";
            } else {
                $text = "
                <table class='cpSearch'>
                    <tr>
                        {$searchText}
                        <td>
                            <input type='text' rel='pptxt: {$keyword}' name='keyword' value='{$tv['keywordDisp']}'>
                        </td>
                        <td>
                            <input type='submit' value='GO' class='button'>
                        </td>
                    </tr>
                </table>
                ";
            }

            $text = "
            <form name='searchTop' id='searchTop' action='index.php' method='get'>
                <input type='hidden' name='_topRm' value='{$tv['topRm']}'>
                <input type='hidden' name='module' value='{$tv['module']}'>
                <input type='hidden' name='_action' value='list'>
                <input type='hidden' name='searchDone' value='1'>
                {$tabFld}
                {$text}
            </form>
            ";
        } else {
            $formAction = preg_replace('/\/(Page)-[0-9]+\//', '/', $_SERVER['REQUEST_URI']);

            $text = "
            <form name='searchTop' id='searchTop' action='{$formAction}' method='get'>
                <input type='hidden' name='searchDone' value='1'>
                {$tabFld}
                <div class='cpSearch'>
                    {$searchText}
                    <div><input type='text' rel='pptxt: {$keyword}' name='keyword' value='{$tv['keyword']}'></div>
                    <div><input type='submit' value='GO' class='button'></div>
                </div>
            </form>
            ";
        }

        return $text;
    }

}
