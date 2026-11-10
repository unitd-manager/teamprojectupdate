/**
 * @author himanshu_agnihotri
 * This class calls the methods of different controllers to 
 * generate pdf files and finally calls the method of pdfmerger to 
 * generate one combined pdf. 
 *   
 */
package com.dhl.sop.label;

import java.io.File;
import java.util.ArrayList;
import java.util.PropertyResourceBundle;

import com.dhl.ShipmentValidateResponse;

public class LabelReportHandler {
	
	/*public static void main( String[] args ) throws Exception	{
    	
  	  if(args.length != 2)
        {
            System.out.println("Usage : java LabelReportHandler \n");
            System.out.println(" where \n");
            System.out.println("Request XML MessagePath : The complete path of the request XML message to be send. E.g. .\\RequestXML\\shipval.xml \n");
            System.out.println("Response PDF Path : The complete directory path where the respose XML messages are to be saved. E.g. .\\PDFReports\\n");
               
        }
        else
        {
        	
        	System.out.println("Generating SOP compliant label .............. ");
        	ShipmentValidateResponse shipmentValidateResponse = LabelReportController.generateSOPLabel(args[0],args[1]);
        	String awbNumber = shipmentValidateResponse.getAirwayBillNumber();
        	System.out.println("SOP compliant label generated :" + args[1]+"GlobalLabel_"+awbNumber+".pdf");
        	System.out.println("Generating Archive label");
        	ArchiveLabelReportController.generateArchiveLabel(args[0],args[1], shipmentValidateResponse);
        	System.out.println("Archive label generated :" + args[1]+"ArchiveLabel_"+awbNumber+".pdf");
            
            ArrayList<String> argumnets= new ArrayList<String>(); 
            
            
            
            argumnets.add(args[1]+"GlobalLabel_"+awbNumber+".pdf");
            argumnets.add(args[1]+"ArchiveLabel_"+awbNumber+".pdf");
            argumnets.add(args[1]+awbNumber+".pdf");
            PdfMerger.getConcatenatedPdf(argumnets);
            
            System.out.println("Global Label and Archive label merged into:" + args[1]+awbNumber+".pdf");
        }
  }*/
	
	public static void main(String[] args) {		
		
			
			PropertyResourceBundle bundle = (PropertyResourceBundle) PropertyResourceBundle.getBundle("label");
			
			String XMLFilePath = bundle.getString("XML_FILE_PATH");//"..\\TransformXMLtoHTML\\ResponseXMLS\\";
			String processedXMLFilePath = bundle.getString("PROCESSED_XML_FILE_PATH");//"..\\TransformXMLtoHTML\\ProcessedXMLS\\";
			String pdfPath = bundle.getString("RESPONSE_PATH");
			File xmlFile = new File(XMLFilePath);
			String [] xmlFileList = xmlFile.list();
			
			for(int iCount=0;iCount<xmlFileList.length;iCount++) {
				String fileName = xmlFileList[iCount];
				String filePath = XMLFilePath + fileName ;
				String movedFilePath = processedXMLFilePath + fileName ;				
				
				try {
					generate(filePath, pdfPath);
					File responsefile = new File( filePath ) ;
					File processedfile = new File( movedFilePath ) ;
					responsefile.renameTo( processedfile ) ;
					System.out.println("Response file " + fileName +" moved to " + processedXMLFilePath + " directory " ) ;
				} catch (Exception e) {
					System.out.println("Unable to generate label for " + filePath);
				}				
				
			}

		
	}
	private static void generate(String xmlPath, String pdfPath) throws Exception{
		
		System.out.println("Generating SOP compliant label .............. ");
    	ShipmentValidateResponse shipmentValidateResponse = LabelReportController.generateSOPLabel(xmlPath,pdfPath);
    	String awbNumber = shipmentValidateResponse.getAirwayBillNumber();
    	
	    if(awbNumber != null && !"".equals(awbNumber.trim()) ) {
	    	
	    	System.out.println("SOP compliant label generated :" + pdfPath+"GlobalLabel_"+awbNumber+".pdf");
	    	System.out.println("Generating Archive label");
	    	ArchiveLabelReportController.generateArchiveLabel(xmlPath,pdfPath, shipmentValidateResponse);
	    	System.out.println("Archive label generated :" + pdfPath+"ArchiveLabel_"+awbNumber+".pdf");
	        
	        ArrayList<String> argumnets= new ArrayList<String>(); 
	    
	        argumnets.add(pdfPath+"GlobalLabel_"+awbNumber+".pdf");
	        argumnets.add(pdfPath+"ArchiveLabel_"+awbNumber+".pdf");
	        argumnets.add(pdfPath+awbNumber+".pdf");
	        PdfMerger.getConcatenatedPdf(argumnets);
	        
	        String globalLabelFile = pdfPath+"GlobalLabel_"+awbNumber+".pdf";
	        String archiveLabelFile = pdfPath+"ArchiveLabel_"+awbNumber+".pdf";
	        
	        // A File object to represent the filename
	        File globalFile = new File(globalLabelFile);
	        File archiveFile = new File(archiveLabelFile);
	
	        // Attempt to delete it
	        globalFile.delete();
	        archiveFile.delete();
	
	        System.out.println("Global Label and Archive label merged into:" + pdfPath+awbNumber+".pdf");
	        
    	} else {
    		String globalLabelFile = pdfPath+"GlobalLabel_"+awbNumber+".pdf";
    		File globalFile = new File(globalLabelFile);
    		globalFile.delete();
    		throw new Exception("Awbnumber is empty");
    	}
	}

}
