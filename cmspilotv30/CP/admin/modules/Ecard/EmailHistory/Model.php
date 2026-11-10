<?
class CP_Admin_Modules_Ecard_EmailHistory_Model extends CP_Common_Lib_ModuleModelAbstract
{
    function getSQL() {
        $cpCfg = Zend_Registry::get('cpCfg');

        $SQL = "
        SELECT ap.title AS design
              ,am.title AS music
              ,e.language
              ,c.email AS sender_email
              ,CONCAT_WS(' ', c.first_name, c.last_name) AS contact_name
              ,eh.name AS recp_name
              ,eh.email AS recp_email
              ,eh.sent_date
              ,eh.creation_date
              ,eh.opened
              ,eh.viewed
              ,eh.sent
        FROM ecard_history eh
        JOIN ecard e   ON (e.ecard_id   = eh.ecard_id)
        JOIN assets ap ON (ap.assets_id = e.picture_id)
        JOIN assets am ON (am.assets_id = e.music_id)
        JOIN contact c ON (c.contact_id = e.contact_id)
        ";

        
        return $SQL;
    }
}
