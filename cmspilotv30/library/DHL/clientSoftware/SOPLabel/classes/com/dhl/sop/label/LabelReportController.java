package com.dhl.sop.label;

import com.dhl.ShipmentValidateResponse;
import com.dhl.datatypes.Billing;
import com.dhl.datatypes.Consignee;
import com.dhl.datatypes.Contact;
import com.dhl.datatypes.DestinationServiceArea;
import com.dhl.datatypes.Dutiable;
import com.dhl.datatypes.DutyTaxPaymentType;
import com.dhl.datatypes.OriginServiceArea;
import com.dhl.datatypes.Reference;
import com.dhl.datatypes.ShipValResponsePiece;
import com.dhl.datatypes.ShipValResponsePieces;
import com.dhl.datatypes.Shipper;
import com.dhl.datatypes.SpecialService;
import java.io.FileReader;
import java.io.PrintStream;
import java.math.BigDecimal;
import java.math.BigInteger;
import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import javax.xml.bind.JAXBContext;
import javax.xml.bind.Unmarshaller;
import javax.xml.datatype.XMLGregorianCalendar;
import net.sf.jasperreports.engine.JasperExportManager;
import net.sf.jasperreports.engine.JasperFillManager;
import net.sf.jasperreports.engine.JasperPrint;
import net.sf.jasperreports.engine.JasperReport;
import net.sf.jasperreports.engine.data.JRMapCollectionDataSource;
import net.sf.jasperreports.engine.util.JRLoader;

public class LabelReportController
{
  public static ShipmentValidateResponse generateSOPLabel(String requestXMLPath, String sopLabelPath)
    throws Exception
  {
    System.out.println("LabelReportController: Inside method generateSOPLabel");

    JAXBContext jaxbContext = JAXBContext.newInstance("com.dhl");
    Unmarshaller unmarshaller = jaxbContext.createUnmarshaller();

    ShipmentValidateResponse shipmentValidateResponse = 
      (ShipmentValidateResponse)unmarshaller.unmarshal(
      new FileReader(requestXMLPath));

		String filePath = "D:\\Projects\\quarkie\\httpdocs\\media\\DHL\\ResponseXMLS\\"
    JasperReport jasperReport = (JasperReport)JRLoader.loadObject("JasperReports/SOPLabel.jasper");

    HashMap shipmentDetails = new HashMap();

    shipmentDetails.put("ProductShortName", shipmentValidateResponse.getProductShortName());
    System.out.println("getProductShortName" +  shipmentValidateResponse.getProductShortName());
    return shipmentValidateResponse;
    
    
    String shipperCNPJ = shipmentValidateResponse.getShipper().getFederalTaxId();
    String consigneeCNPJ = shipmentValidateResponse.getConsignee().getFederalTaxId();
    String shipperIE = shipmentValidateResponse.getShipper().getStateTaxId();
    String consigneeIE = shipmentValidateResponse.getConsignee().getStateTaxId();

    if ("BR".equalsIgnoreCase(shipmentValidateResponse.getShipper().getCountryCode()))
    {
      if ((shipperCNPJ != null) && (!"".equals(shipperCNPJ.trim())))
      {
        shipmentDetails.put("ShipperCNPJ", "CNPJ / CPF:" + shipperCNPJ);
      }

      if ((consigneeCNPJ != null) && (!"".equals(consigneeCNPJ.trim())))
      {
        shipmentDetails.put("ConsigneeCNPJ", "CNPJ / CPF:" + consigneeCNPJ);
      }

      if ((shipperIE != null) && (!"".equals(shipperIE.trim())))
      {
        shipmentDetails.put("ShipperIE", shipperIE);
      }
      if ((consigneeIE != null) && (!"".equals(consigneeIE.trim())))
      {
        shipmentDetails.put("ConsigneeIE", consigneeIE);
      }
    }
    else
    {
      if ((shipperCNPJ != null) && (!"".equals(shipperCNPJ.trim())))
      {
        shipmentDetails.put("ShipperCNPJ", "ID:" + shipperCNPJ);
      }

      if ((consigneeCNPJ != null) && (!"".equals(consigneeCNPJ.trim())))
      {
        shipmentDetails.put("ConsigneeCNPJ", "ID:" + consigneeCNPJ);
      }

    }

    if (("US".equalsIgnoreCase(shipmentValidateResponse.getShipper().getCountryCode())) && 
      (!"US".equalsIgnoreCase(shipmentValidateResponse.getConsignee().getCountryCode())))
    {
      shipmentDetails.put("CommerceControlStatement", 
        "These commodities, technology or software were exported from the United States in accordance with the Export Administration regulations. Diversion contrary to U.S law prohibited. Shipment may be varied via intermediate stopping places which DHL deems appropriate.");
    }

    shipmentDetails.put("ProductContentCode", shipmentValidateResponse.getProductContentCode());

    shipmentDetails.put("ShipperCompanyName", shipmentValidateResponse.getShipper().getCompanyName());

    StringBuilder shipperAddress = new StringBuilder();

    List shipperAddressList = shipmentValidateResponse.getShipper().getAddressLine();

    Iterator addressIterator = shipperAddressList.iterator();

    while (addressIterator.hasNext()) {
      String addressLine = (String)addressIterator.next();
      shipperAddress.append(addressLine);
      shipperAddress.append("\n");
    }

    shipmentDetails.put("ShipperAddress", shipperAddress.toString());

    StringBuilder shipperAdressDetails = new StringBuilder();
    String shipperCity = shipmentValidateResponse.getShipper().getCity();
    if ((shipperCity != null) && (!"".equals(shipperCity.trim()))) {
      shipperAdressDetails.append(shipperCity);
      shipperAdressDetails.append("  ");
    }

    String shipperDivisionCode = shipmentValidateResponse.getShipper().getDivisionCode();
    if ((shipperDivisionCode != null) && (!"".equals(shipperDivisionCode.trim()))) {
      shipperAdressDetails.append(shipperDivisionCode);
      shipperAdressDetails.append("  ");
    }
    String shipperPostalCode = shipmentValidateResponse.getShipper().getPostalCode();
    if ((shipperPostalCode != null) && (!"".equals(shipperPostalCode.trim()))) {
      shipperPostalCode = shipperPostalCode.toUpperCase();

      shipperAdressDetails.append(shipperPostalCode);
    }

    shipmentDetails.put("ShipperCity", shipperAdressDetails.toString());
    shipmentDetails.put("ShipperCountry", shipmentValidateResponse.getShipper().getCountryName());

    shipmentDetails.put("OriginServiceAreaCode", shipmentValidateResponse.getOriginServiceArea().getServiceAreaCode());

    shipmentDetails.put("ShipperContactName", shipmentValidateResponse.getShipper().getContact().getPersonName());

    shipmentDetails.put("ShipperPhoneNumber", "Ph:" + shipmentValidateResponse.getShipper().getContact().getPhoneNumber());

    StringBuilder destinationAddressSB = new StringBuilder();
    destinationAddressSB.append(shipmentValidateResponse.getConsignee().getCompanyName());

    destinationAddressSB.append("\n");
    destinationAddressSB.append(shipmentValidateResponse.getConsignee().getContact().getPersonName());

    List receiverAddressList = shipmentValidateResponse.getConsignee().getAddressLine();

    Iterator consigneeAddressIterator = receiverAddressList.iterator();

    while (consigneeAddressIterator.hasNext()) {
      String addressLine = (String)consigneeAddressIterator.next();
      destinationAddressSB.append("\n");
      destinationAddressSB.append(addressLine);
    }

    String destinationAddress = destinationAddressSB.toString();

    if (destinationAddress.endsWith("\n")) {
      destinationAddress = destinationAddress.substring(0, destinationAddress.length() - 1);
    }

    shipmentDetails.put("DestinationAddress", destinationAddress);

    StringBuilder receiverAddressDetails = new StringBuilder();
    String receiverCity = shipmentValidateResponse.getConsignee().getCity();
    if ((receiverCity != null) && (!"".equals(receiverCity.trim()))) {
      receiverAddressDetails.append(receiverCity);
      receiverAddressDetails.append("  ");
    }

    String receiverDivisionCode = shipmentValidateResponse.getConsignee().getDivisionCode();
    if ((receiverDivisionCode != null) && (!"".equals(receiverDivisionCode.trim()))) {
      receiverAddressDetails.append(receiverDivisionCode);
      receiverAddressDetails.append("  ");
    }

    String receiverPostalCode = shipmentValidateResponse.getConsignee().getPostalCode();

    if ((receiverPostalCode != null) && (!"".equals(receiverPostalCode.trim()))) {
      receiverPostalCode = receiverPostalCode.toUpperCase();

      receiverAddressDetails.append(receiverPostalCode);
    }

    int destCityLength = receiverAddressDetails.toString().length();
    if (destCityLength > 46)
      shipmentDetails.put("DestinationCity", receiverAddressDetails.toString());
    else {
      shipmentDetails.put("DestinationCityExtra", receiverAddressDetails.toString());
    }
    shipmentDetails.put("DestinationCountry", shipmentValidateResponse.getConsignee().getCountryName());

    StringBuilder receiverContactDetails = new StringBuilder();

    receiverContactDetails.append("Ph:" + shipmentValidateResponse.getConsignee().getContact().getPhoneNumber());

    shipmentDetails.put("ReceiverContactDetails", receiverContactDetails.toString());

    shipmentDetails.put("OutBoundSortCode", shipmentValidateResponse.getOriginServiceArea().getOutboundSortCode());
    shipmentDetails.put("InBoundSortCode", shipmentValidateResponse.getDestinationServiceArea().getInboundSortCode());

    StringBuffer destinationFacilityCodeSB = new StringBuffer();
    destinationFacilityCodeSB.append(shipmentValidateResponse.getConsignee().getCountryCode());
    destinationFacilityCodeSB.append("-");
    destinationFacilityCodeSB.append(shipmentValidateResponse.getDestinationServiceArea().getServiceAreaCode());
    String facilityCode = shipmentValidateResponse.getDestinationServiceArea().getFacilityCode();
    if ((facilityCode != null) && (!facilityCode.equals(""))) {
      destinationFacilityCodeSB.append("-");
      destinationFacilityCodeSB.append(shipmentValidateResponse.getDestinationServiceArea().getFacilityCode());
    }
    shipmentDetails.put("DestinationFacilityCode", destinationFacilityCodeSB.toString());

    List internalServiceCodeList = shipmentValidateResponse.getInternalServiceCode();
    StringBuffer internalServiceCodeSB = new StringBuffer();

    if (internalServiceCodeList.contains("C"))
    {
      shipmentDetails.put("DuitableFlag", "true");
    }
    else {
      shipmentDetails.put("DuitableFlag", "false");
    }

    Collections.sort(internalServiceCodeList, new InternalServiceCodeComparator());
    for (String iSC : internalServiceCodeList) {
      internalServiceCodeSB.append(iSC);
      internalServiceCodeSB.append("-");
    }

    String internalServiceCode = internalServiceCodeSB.toString();

    if (internalServiceCode.endsWith("-")) {
      internalServiceCode = internalServiceCode.substring(0, internalServiceCode.length() - 1);
    }
    if ((internalServiceCodeList.size() == 0) || (internalServiceCodeList.size() == 1))
    {
      shipmentDetails.put("InternalServiceFlag", "one");
    } else if (internalServiceCodeList.size() == 2)
      shipmentDetails.put("InternalServiceFlag", "two");
    else if (internalServiceCodeList.size() == 3)
      shipmentDetails.put("InternalServiceFlag", "three");
    else if (internalServiceCodeList.size() == 4)
      shipmentDetails.put("InternalServiceFlag", "four");
    else {
      shipmentDetails.put("InternalServiceFlag", "five");
    }
    shipmentDetails.put("InternalServiceCode", internalServiceCode);

    shipmentDetails.put("DeliveryTime", shipmentValidateResponse.getDeliveryTimeCode());
    shipmentDetails.put("CalendarDate", shipmentValidateResponse.getDeliveryDateCode());

    Long registeredAccountObj = Long.valueOf(shipmentValidateResponse.getBilling().getShipperAccountNumber());
    shipmentDetails.put("AccountNumber", registeredAccountObj.toString());

    List referenceList = shipmentValidateResponse.getReference();
    String referenceId = "";
    if ((referenceList != null) && (referenceList.size() > 0)) {
      Reference reference = (Reference)referenceList.get(0);
      referenceId = reference.getReferenceID();
    }
    shipmentDetails.put("ReferenceNumber", referenceId);
    shipmentDetails.put("Date", shipmentValidateResponse.getShipmentDate().toString());

    shipmentDetails.put("ContentsDescription", "Content: " + shipmentValidateResponse.getContents());
    shipmentDetails.put("DataId", "(" + shipmentValidateResponse.getDHLRoutingDataId() + ")");

    String airWayBillNumber = shipmentValidateResponse.getAirwayBillNumber();
    String airWayBillNumberWithSpaces = "";

    while (airWayBillNumber.length() > 0)
    {
      int len = airWayBillNumber.length() - 4;
      if (len > 0) {
        airWayBillNumberWithSpaces = airWayBillNumber.substring(len) + " " + airWayBillNumberWithSpaces;
        airWayBillNumber = airWayBillNumber.substring(0, len);
      }
      else {
        airWayBillNumberWithSpaces = airWayBillNumber + " " + airWayBillNumberWithSpaces;
        airWayBillNumber = "";
      }
    }

    shipmentDetails.put("AirWayBillNumber", airWayBillNumberWithSpaces);

    String routingBarCodeText = shipmentValidateResponse.getDHLRoutingCode();
    if (routingBarCodeText != null) {
      routingBarCodeText = routingBarCodeText.toUpperCase();
    }
    shipmentDetails.put("DHLRoutingBarCodeText", routingBarCodeText);

    shipmentDetails.put("AirWayBillBarCode", shipmentValidateResponse.getAirwayBillNumber());

    String routingBarcode = shipmentValidateResponse.getDHLRoutingDataId() + shipmentValidateResponse.getDHLRoutingCode();

    shipmentDetails.put("DHLRoutingBarCode", routingBarcode);

    ArrayList pieceList = (ArrayList)shipmentValidateResponse.getPieces().getPiece();
    Iterator itrPiece = pieceList.iterator();
    String totalPieces = "0";
    if (pieceList != null) {
      totalPieces = pieceList.size();
    }
    shipmentDetails.put("Pieces", totalPieces);

    ArrayList pieceFieldMaps = new ArrayList();

    String unit = shipmentValidateResponse.getWeightUnit();
    String weightUnit;
    String weightUnit;
    if (unit != null)
      weightUnit = unit.toString();
    else {
      weightUnit = "";
    }

    if (weightUnit.equalsIgnoreCase("G"))
      weightUnit = "gm";
    else if (weightUnit.equalsIgnoreCase("K"))
      weightUnit = "Kg";
    else {
      weightUnit = "Lbs";
    }

    while (itrPiece.hasNext())
    {
      HashMap pieceMap = new HashMap();
      ShipValResponsePiece shipValResponsePiece = (ShipValResponsePiece)itrPiece.next();
      pieceMap.put("PieceNumber", shipValResponsePiece.getPieceNumber().toString() + "/" + totalPieces);
      pieceMap.put("PieceCounter", shipValResponsePiece.getPieceNumber().toString());
      pieceMap.put("PieceWeight", shipValResponsePiece.getWeight().toString() + " " + weightUnit);
      pieceMap.put("PieceIdentifier", "(" + shipValResponsePiece.getDataIdentifier() + ")");
      String licensePlateWithSpaces = ArchiveLabelReportController.getLicensePlateWithSpaces(shipValResponsePiece.getLicensePlate());
      pieceMap.put("LicensePlateBarCodeText", licensePlateWithSpaces);
      pieceMap.put("LicensePlateBarCode", shipValResponsePiece.getDataIdentifier() + shipValResponsePiece.getLicensePlate());
      pieceFieldMaps.add(pieceMap);
    }

    List serviceList = shipmentValidateResponse.getSpecialService();
    DutyTaxPaymentType dutyPaymentType = shipmentValidateResponse.getBilling().getDutyPaymentType();
    Long dutyAccountNumber = shipmentValidateResponse.getBilling().getDutyAccountNumber();
    if (serviceList != null) {
      for (SpecialService specialService : serviceList)
      {
        if ((!"D".equalsIgnoreCase(specialService.getSpecialServiceType())) || 
          (DutyTaxPaymentType.fromValue("T") != dutyPaymentType)) continue;
        shipmentDetails.put("DutyAccountNumber", dutyAccountNumber);
      }

    }

    if (shipmentValidateResponse.getInsuredAmount() != null)
    {
      shipmentDetails.put("InsuredAmount", shipmentValidateResponse.getInsuredAmount() + " " + shipmentValidateResponse.getCurrencyCode());
    }

    Dutiable dutiable = shipmentValidateResponse.getDutiable();
    if (dutiable != null) {
      shipmentDetails.put("DeclaredValue", dutiable.getDeclaredValue() + " " + dutiable.getDeclaredCurrency());

      if (dutiable.getTermsOfTrade() != null) {
        shipmentDetails.put("TermsOfTrade", dutiable.getTermsOfTrade());
      }
    }

    if (shipmentValidateResponse.getBilling().getBillingAccountNumber() != 0L) {
      shipmentDetails.put("BillingAccountNumber", 
        shipmentValidateResponse.getBilling().getBillingAccountNumber());
    }

    JRMapCollectionDataSource mapDS = new JRMapCollectionDataSource(pieceFieldMaps);
    JasperPrint jasperPrint = JasperFillManager.fillReport(jasperReport, shipmentDetails, mapDS);
    JasperExportManager.exportReportToPdfFile(jasperPrint, 
      sopLabelPath + "GlobalLabel_" + shipmentValidateResponse.getAirwayBillNumber() + ".pdf");
    System.out.println("LabelReportController:generateSOPLabel() : Label generated");

    return shipmentValidateResponse;
  }
}