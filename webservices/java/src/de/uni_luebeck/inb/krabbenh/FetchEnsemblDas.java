package de.uni_luebeck.inb.krabbenh;

import java.net.URL;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;

import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

public class FetchEnsemblDas {
	private static final long serialVersionUID = 1L;

	public static Map<String, Document> cache = new HashMap<String, Document>();

	private static NodeList fetchNodesCached(String type, String segment) {
		Document doc;
		try {
			URL url = new URL("http://www.ensembl.org/das/Rattus_norvegicus.RGSC3.4." + type + "/features?segment=" + segment);
			if (cache.containsKey(url.toString()))
				doc = cache.get(url.toString());
			else {
				DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
				DocumentBuilder db = dbf.newDocumentBuilder();
				doc = db.parse(url.toString());
				doc.getDocumentElement().normalize();
				cache.put(url.toString(), doc);
			}
		} catch (Exception e) {
			throw new RuntimeException(e);
		}
		return doc.getElementsByTagName("FEATURE");
	}

	public EnsemblBand[] getEnsemblBands(String chromosome) {

		ArrayList<EnsemblBand> list = new ArrayList<EnsemblBand>();
		NodeList nodeLst = fetchNodesCached("karyotype", chromosome);
		for (int s = 0; s < nodeLst.getLength(); s++) {
			Node featureNode = nodeLst.item(s);
			Element featureElement = (Element) featureNode;

			EnsemblBand band = new EnsemblBand();
			band.label = featureElement.getAttribute("id");
			band.from = Long.parseLong(featureElement.getElementsByTagName("START").item(0).getTextContent());
			band.to = Long.parseLong(featureElement.getElementsByTagName("END").item(0).getTextContent());
			band.type = 1;
			if (featureElement.getElementsByTagName("TYPE").item(0).getTextContent().contains("gvar"))
				band.type = 2;
			if (featureElement.getElementsByTagName("TYPE").item(0).getTextContent().contains("gpos"))
				band.type = 3;

			list.add(band);
		}

		return list.toArray(new EnsemblBand[] {});
	}

	public EnsemblBand[] getEnsemblContigs(String chromosome, long fromBP, long toBP) {
		System.out.println("getEnsemblContigs: " + chromosome + ":" + fromBP + "," + toBP);
		ArrayList<EnsemblBand> list = new ArrayList<EnsemblBand>();
		NodeList nodeLst = fetchNodesCached("reference", chromosome + ":" + fromBP + "," + toBP);
		for (int s = 0; s < nodeLst.getLength(); s++) {
			Node featureNode = nodeLst.item(s);
			Element featureElement = (Element) featureNode;

			EnsemblBand band = new EnsemblBand();
			band.label = featureElement.getAttribute("id");
			if (!band.label.startsWith("RNOR"))
				continue;

			band.from = Long.parseLong(featureElement.getElementsByTagName("START").item(0).getTextContent());
			band.to = Long.parseLong(featureElement.getElementsByTagName("END").item(0).getTextContent());
			band.type = s % 2;

			list.add(band);
		}
		System.out.println("getEnsemblContigs: " + list.size());

		return list.toArray(new EnsemblBand[] {});
	}

	public EnsemblBand[] getEnsemblTranscripts(String chromosome, long fromBP, long toBP) {
		System.out.println("getEnsemblTranscripts: " + chromosome + ":" + fromBP + "," + toBP);
		ArrayList<EnsemblBand> list = new ArrayList<EnsemblBand>();
		NodeList nodeLst = fetchNodesCached("transcript", chromosome + ":" + fromBP + "," + toBP);
		for (int s = 0; s < nodeLst.getLength(); s++) {
			Node featureNode = nodeLst.item(s);
			Element featureElement = (Element) featureNode;

			EnsemblBand band = new EnsemblBand();
			band.label = ((Element)featureElement.getElementsByTagName("GROUP").item(0)).getAttribute("label");
			//band.label = band.label.substring("ENSRNOT00000062063 (".length()-1);
			//band.label = band.label.substring(1, band.label.length() - 1);
			band.from = Long.parseLong(featureElement.getElementsByTagName("START").item(0).getTextContent());
			band.to = Long.parseLong(featureElement.getElementsByTagName("END").item(0).getTextContent());
			band.type = featureElement.getElementsByTagName("ORIENTATION").item(0).getTextContent().contains("+") ? 0 : 1;

			list.add(band);
		}
		System.out.println("getEnsemblTranscripts: " + list.size());
		return list.toArray(new EnsemblBand[] {});
	}

}
