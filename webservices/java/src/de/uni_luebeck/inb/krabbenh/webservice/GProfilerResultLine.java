package de.uni_luebeck.inb.krabbenh.webservice;

public class GProfilerResultLine {
	private double pValue;
	private int q;
	private int t;
	private int qAndT;
	private double qAndTDivQ;
	private double qAndTDivT;
	private String termId;
	private String termDomain;
	private String termName;

	public double getPValue() {
		return pValue;
	}

	public void setPValue(double value) {
		pValue = value;
	}

	public int getQ() {
		return q;
	}

	public void setQ(int q) {
		this.q = q;
	}

	public int getT() {
		return t;
	}

	public void setT(int t) {
		this.t = t;
	}

	public int getQAndT() {
		return qAndT;
	}

	public void setQAndT(int andT) {
		qAndT = andT;
	}

	public double getQAndTDivQ() {
		return qAndTDivQ;
	}

	public void setQAndTDivQ(double andTDivQ) {
		qAndTDivQ = andTDivQ;
	}

	public double getQAndTDivT() {
		return qAndTDivT;
	}

	public void setQAndTDivT(double andTDivT) {
		qAndTDivT = andTDivT;
	}

	public String getTermId() {
		return termId;
	}

	public void setTermId(String termId) {
		this.termId = termId;
	}

	public String getTermDomain() {
		return termDomain;
	}

	public void setTermDomain(String termDomain) {
		this.termDomain = termDomain;
	}

	public String getTermName() {
		return termName;
	}

	public void setTermName(String termName) {
		this.termName = termName;
	}

}