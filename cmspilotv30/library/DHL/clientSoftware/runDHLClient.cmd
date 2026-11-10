set RESPONSE_PATH=TransformXMLtoPDF\ResponseXMLS\
set SERVER_URL=http://xmlpiqa.prg-dc.dhl.com/XMLShippingServlet
set INPUT_FILE=TransformXMLtoPDF\RequestXML\ShipmentValidateRequest_APToAP.xml

java DHLClient %INPUT_FILE% %SERVER_URL% %RESPONSE_PATH%
