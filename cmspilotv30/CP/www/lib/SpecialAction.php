<?
class CP_Www_Lib_SpecialAction extends CP_Common_Lib_SpecialAction
{


    /**
     *
     * @return <type>
     */
    function getNewsletterPreview() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');

        $tv['bodyExtraClass'] = 'newsletter-preview';
        CP_Common_Lib_Registry::arrayMerge('tv', $tv);

        $random_no = $fn->getReqParam('rid', '', true);
        $broadcast_id = $fn->getReqParam('id', '', true);
        
        if ($broadcast_id != ''){
            $brodRec = $fn->getRecordRowByID("broadcast", 'broadcast_id', $broadcast_id);
        } else {
            $brodRec = $fn->getRecordByCondition("broadcast", "random_no = '{$random_no}'");
        }

        $message = $brodRec['description'];

        $text = "
        <div id='newsletterPreview'>
            {$message}
        </div>
        <script>
            $(function(){
            });
        </script>
        ";

        CP_Common_Lib_Registry::arrayMerge('inlineScripts', array("
            $(\"a[href*='newsletter-preview']\").remove();
        "));

        return $text;
    }
}