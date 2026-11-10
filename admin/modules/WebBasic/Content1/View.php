<?
class CPL_Admin_Modules_WebBasic_Content_View extends CP_Admin_Modules_WebBasic_Content_View
{

    /**
     * Adding GET STARTED BUTTON form in the content list. Used in USS Products (THAMIM)
     */
    function getHelpContentTask() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

		$SQL = "
		SELECT c.title
		      ,c.description
		FROM content c
		WHERE c.published = 1
		AND c.content_type = 'Get Started'
		ORDER BY c.sort_order
		";
        $result = $db->sql_query($SQL);

		$text = '';
        $i = 1;

        while ($row = $db->sql_fetchrow($result)) {
            $text .= "
            ";
            $i++;
        }

        $text = "
        <div>
            Please find the video demo of the modules below.            
            <h1 style='font-size:20px;font-weight:bold;'>Tender</h1>
            <div style='position: relative; padding-bottom: 56.25%; height: 0;'><iframe src='https://www.loom.com/embed/72f55e40185e4ca29f28025120140191' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen style='position: absolute; top: 0; left: 0; width: 100%; height: 100%;'></iframe></div><br/><br/>
            <h1 style='font-size:20px;font-weight:bold;'>Quote Drawings</h1>
            <div style='position: relative; padding-bottom: 56.162246489859605%; height: 0;'><iframe src='https://www.loom.com/embed/6ab152778d5d4f07a1384d9a33bdcc13' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen style='position: absolute; top: 0; left: 0; width: 100%; height: 100%;'></iframe></div><br/><br/>
            <h1 style='font-size:20px;font-weight:bold;'>Material Request</h1>
            <div style='position: relative; padding-bottom: 56.162246489859605%; height: 0;'><iframe src='https://www.loom.com/embed/35ae4969a344410ebe6b7f852f7ae39b' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen style='position: absolute; top: 0; left: 0; width: 100%; height: 100%;'></iframe></div>
            <h1 style='font-size:20px;font-weight:bold;'>Project Costing Summary</h1>
            <div style='position: relative; padding-bottom: 56.25%; height: 0;'><iframe src='https://www.loom.com/embed/72c93dafef4944f6ba24402affe3d9de' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen style='position: absolute; top: 0; left: 0; width: 100%; height: 100%;'></iframe></div>
            <h1 style='font-size:20px;font-weight:bold;'>Material Used & Material Return</h1>
            <div style='position: relative; padding-bottom: 56.25%; height: 0;'><iframe src='https://www.loom.com/embed/2caea4dc1a7644a2b1495b68a0cc4157' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen style='position: absolute; top: 0; left: 0; width: 100%; height: 100%;'></iframe></div>
            <h1 style='font-size:20px;font-weight:bold;'>Material Transfer</h1>
            <div style='position: relative; padding-bottom: 56.25%; height: 0;'><iframe src='https://www.loom.com/embed/e124be688c6a4a5d99631529200f6ae7' frameborder='0' webkitallowfullscreen mozallowfullscreen allowfullscreen style='position: absolute; top: 0; left: 0; width: 100%; height: 100%;'></iframe></div>
        </div>
        ";
        return $text;
    }

}