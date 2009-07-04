package de.uni_luebeck.inb.krabbenh.entities;

import java.util.Set;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.OneToMany;



@Entity
public class Snip implements java.io.Serializable {
	private int id;
	private Set<MouseExpression> expressions;
	private String accession;
	private String symbol;
	private int start;
	private String probeSequence;
	private String description;

	public void setId(int id) {
		this.id = id;
	}

	@Id
	public int getId() {
		return id;
	}

	public void setExpressions(Set<MouseExpression> expressions) {
		this.expressions = expressions;
	}

	@OneToMany(mappedBy="snip")
	public Set<MouseExpression> getExpressions() {
		return expressions;
	}

	public void setAccession(String accession) {
		this.accession = accession;
	}

	@Column(nullable=false)
	public String getAccession() {
		return accession;
	}

	public void setSymbol(String symbol) {
		this.symbol = symbol;
	}

	@Column(nullable=false)
	public String getSymbol() {
		return symbol;
	}

	public void setStart(int start) {
		this.start = start;
	}

	@Column(nullable=false)
	public int getStart() {
		return start;
	}

	public void setProbeSequence(String probeSequence) {
		this.probeSequence = probeSequence;
	}

	@Column(nullable=false)
	public String getProbeSequence() {
		return probeSequence;
	}

	public void setDescription(String description) {
		this.description = description;
	}

	@Column(nullable=false, length=2048)
	public String getDescription() {
		return description;
	}
}
