package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;
import java.util.HashSet;
import java.util.Set;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.ManyToMany;
import javax.persistence.OneToOne;
import javax.persistence.PrimaryKeyJoinColumn;

@Entity
public class MillionBasepairBox implements Serializable {
	private static final long serialVersionUID = 1L;

	private int id;

	private String chromosome;
	private long fromBP;
	private long toBP;

	private Set<ExpressionQTL> containedExpressionQTLs = new HashSet<ExpressionQTL>();

	private MillionBasepairBox_StatisticsAll statisticsAll;
	private MillionBasepairBox_StatisticsCis statisticsCis;

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

	@OneToOne
	@PrimaryKeyJoinColumn
	public MillionBasepairBox_StatisticsAll getStatisticsAll() {
		return statisticsAll;
	}

	public void setStatisticsAll(MillionBasepairBox_StatisticsAll statisticsAll) {
		this.statisticsAll = statisticsAll;
	}

	@OneToOne
	@PrimaryKeyJoinColumn
	public MillionBasepairBox_StatisticsCis getStatisticsCis() {
		return statisticsCis;
	}

	public void setStatisticsCis(MillionBasepairBox_StatisticsCis statisticsCis) {
		this.statisticsCis = statisticsCis;
	}

}
