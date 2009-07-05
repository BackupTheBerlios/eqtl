package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;
import java.util.HashSet;
import java.util.Set;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.ManyToMany;
import javax.persistence.OneToMany;

@Entity
public class MillionBasepairBox implements Serializable {
	private static final long serialVersionUID = 1L;

	private int id;

	private String chromosome;
	private long fromBP;
	private long toBP;

	private Set<ExpressionQTL> containedExpressionQTLs = new HashSet<ExpressionQTL>();
	private Set<MillionBasepairBox_Statistics> statistics = new HashSet<MillionBasepairBox_Statistics>();

	@Id
	@GeneratedValue
	@Column(unique = true, nullable = false)
	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public String getChromosome() {
		return chromosome;
	}

	public void setChromosome(String chromosome) {
		this.chromosome = chromosome;
	}

	public long getFromBP() {
		return fromBP;
	}

	public void setFromBP(long fromBP) {
		this.fromBP = fromBP;
	}

	public long getToBP() {
		return toBP;
	}

	public void setToBP(long toBP) {
		this.toBP = toBP;
	}

	@ManyToMany
	public Set<ExpressionQTL> getContainedExpressionQTLs() {
		return containedExpressionQTLs;
	}

	public void setContainedExpressionQTLs(Set<ExpressionQTL> containedExpressionQTLs) {
		this.containedExpressionQTLs = containedExpressionQTLs;
	}

	@OneToMany
	public Set<MillionBasepairBox_Statistics> getStatistics() {
		return statistics;
	}

	public void setStatistics(Set<MillionBasepairBox_Statistics> statistics) {
		this.statistics = statistics;
	}

}
