<?
class CP_Common_Modules_Web2_Tags_View extends CP_Common_Lib_ModuleViewAbstract
{

    /**
     *
     */
    function getTagsList() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');

        $fnsModTags = includeCPClass('ModuleFns', 'web2_tags');
        $SQL        = $fnsModTags->getSQL();
        $result     = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $cls = ($row['tag_text'] == $tv['tag']) ? " class='current'": "";
            $tags .= "
            <li>
                <a href=''{$cls}>{$row['tag_text']}</a>
            </li>
            ";
        }

        $text = "
        <ul class='tag'>
            {$tags}
        </ul>
        ";

        return $text;
    }
}