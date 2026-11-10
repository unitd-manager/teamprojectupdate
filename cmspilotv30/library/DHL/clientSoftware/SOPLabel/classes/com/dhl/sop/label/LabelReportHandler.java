package com.dhl.sop.label;

import com.dhl.ShipmentValidateResponse;
import java.io.File;
import java.io.PrintStream;
import java.util.ArrayList;
import java.util.PropertyResourceBundle;

public class LabelReportHandler
{
  public static void main(String[] args)
  {
    PropertyResourceBundle bundle = (PropertyResourceBundle)PropertyResourceBundle.getBundle("label");

    String XMLFilePath = bundle.getString("XML_FILE_PATH");
    String processedXMLFilePath = bundle.getString("PROCESSED_XML_FILE_PATH");
    String pdfPath = bundle.getString("RESPONSE_PATH");
    File xmlFile = new File(XMLFilePath);
    String[] xmlFileList = xmlFile.list();

    for (int iCount = 0; iCount < xmlFileList.length; iCount++) {
      String fileName = xmlFileList[iCount];
      String filePath = XMLFilePath + fileName;
      String movedFilePath = processedXMLFilePath + fileName;
      try
      {
        generate(filePath, pdfPath);
        File responsefile = new File(filePath);
        File processedfile = new File(movedFilePath);
        responsefile.renameTo(processedfile);
        System.out.println("Response file " + fileName + " moved to " + processedXMLFilePath + " directory ");
      } catch (Exception e) {
      	e.printStackTrace();
        System.out.println("Unable to generate label for " + filePath);
      }
    }
  }

  private static void generate(String xmlPath, String pdfPath)
    throws Exception
  {
    System.out.println("Generating SOP compliant label .............. ");
    ShipmentValidateResponse shipmentValidateResponse = LabelReportController.generateSOPLabel(xmlPath, pdfPath);
    return;
    
    //String awbNumber = shipmentValidateResponse.getAirwayBillNumber();
    //
    //if ((awbNumber != null) && (!"".equals(awbNumber.trim())))
    //{
    //  System.out.println("SOP compliant label generated :" + pdfPath + "GlobalLabel_" + awbNumber + ".pdf");
    //  System.out.println("Generating Archive label");
    //  ArchiveLabelReportController.generateArchiveLabel(xmlPath, pdfPath, shipmentValidateResponse);
    //  System.out.println("Archive label generated :" + pdfPath + "ArchiveLabel_" + awbNumber + ".pdf");
    //
    //  ArrayList argumnets = new ArrayList();
    //
    //  argumnets.add(pdfPath + "GlobalLabel_" + awbNumber + ".pdf");
    //  argumnets.add(pdfPath + "ArchiveLabel_" + awbNumber + ".pdf");
    //  argumnets.add(pdfPath + awbNumber + ".pdf");
    //  PdfMerger.getConcatenatedPdf(argumnets);
    //
    //  String globalLabelFile = pdfPath + "GlobalLabel_" + awbNumber + ".pdf";
    //  String archiveLabelFile = pdfPath + "ArchiveLabel_" + awbNumber + ".pdf";
    //
    //  File globalFile = new File(globalLabelFile);
    //  File archiveFile = new File(archiveLabelFile);
    //
    //  globalFile.delete();
    //  archiveFile.delete();
    //
    //  System.out.println("Global Label and Archive label merged into:" + pdfPath + awbNumber + ".pdf");
    //}
    //else {
    //  String globalLabelFile = pdfPath + "GlobalLabel_" + awbNumber + ".pdf";
    //  File globalFile = new File(globalLabelFile);
    //  globalFile.delete();
    //  throw new Exception("Awbnumber is empty");
    //}
  }
}