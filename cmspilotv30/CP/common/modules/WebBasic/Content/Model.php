<?
class CP_Common_Modules_WebBasic_Content_Model extends CP_Common_Lib_ModuleModelAbstract
{
    /**
     *
     */
    function getWebBasicContentWebBasicContentLinkSQL($id) {
        $SQL = "
        SELECT rc.related_content_id
              ,c.content_id
              ,c.title
              ,cat.title AS category_title
        FROM related_content rc
        JOIN content c ON (c.content_id = rc.content_id_rel)
        LEFT JOIN category cat ON (cat.category_id = c.category_id)
        WHERE rc.content_id = {$id}
        ";
        return $SQL;
    }
}
