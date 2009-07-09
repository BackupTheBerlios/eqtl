package de.uni_luebeck.inb.krabbenh.webservice;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;

import javax.jws.WebMethod;
import javax.jws.WebService;

@WebService(name = "GProfilerServices", serviceName = "eQTL_GProfilerServices", targetNamespace = "http://krabbenh.inb.uni-luebeck.de")
public class GProfilerServices {
	private final static String gProfilerUrl = "http://biit.cs.ut.ee/gprofiler/index.cgi?organism=rnorvegicus&query=###QUERY###&analytical=1&domain_size_type=annotated&term=&user_thr=1.00&output=mini";

	@WebMethod
	public GProfilerResultLine[] invokeGProfilerForGeneNames(final String[] geneNames) {
		StringBuilder builder = new StringBuilder();
		for (String gene : geneNames) {
			if (builder.length() > 0)
				builder.append("+");
			builder.append(gene);
		}
		String url = gProfilerUrl.replace("###QUERY###", builder.toString());
		try {
			BufferedReader bufferedReader = new BufferedReader(new InputStreamReader(new URL(url).openStream()));
			List<GProfilerResultLine> list = new ArrayList<GProfilerResultLine>();
			String line;
			while ((line = bufferedReader.readLine()) != null) {
				String[] parts = line.split("\t");
				if(parts.length < 12) continue;
				
				GProfilerResultLine resultLine = new GProfilerResultLine();
				resultLine.setPValue(Double.parseDouble(parts[2]));
				resultLine.setQ(Integer.parseInt(parts[3]));
				resultLine.setT(Integer.parseInt(parts[4]));
				resultLine.setQAndT(Integer.parseInt(parts[5]));
				resultLine.setQAndTDivQ(Double.parseDouble(parts[6]));
				resultLine.setQAndTDivT(Double.parseDouble(parts[7]));
				resultLine.setTermId(parts[8]);
				resultLine.setTermDomain(parts[9]);
				resultLine.setTermName(parts[11]);
				list.add(resultLine);
			}
			return list.toArray(new GProfilerResultLine[] {});
		} catch (Exception e) {
			throw new RuntimeException(e);
		}
	}
}