package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.IdClass;
import javax.persistence.Table;
import javax.persistence.Transient;

import org.hibernate.annotations.Immutable;

@Entity
@Table(name = "millionbasepairbox_statistics_all")
@Immutable
@IdClass(MillionBasepairBox_StatisticsPrimaryKey.class)
public class MillionBasepairBox_StatisticsAll implements Serializable {
	private static final long serialVersionUID = 1L;

	
	private int millionBasepairBox;
	private int covariate;

	@Id
	public int getMillionBasepairBox() {
		return millionBasepairBox;
	}

	protected void setMillionBasepairBox(int millionBasepairBox) {
		this.millionBasepairBox = millionBasepairBox;
	}

	@Id
	public int getCovariate() {
		return covariate;
	}

	protected void setCovariate(int covariate) {
		this.covariate = covariate;
	}


	private int eqtlCount;

	private double lodAverage;
	private double lodMin;
	private double lodMax;
	private Double lodStdDev; // may be null

	private double frequencySameChromosome;

	@Column(name = "eqtlcount")
	public int getEqtlCount() {
		return eqtlCount;
	}

	@Column(name = "AVG_lod")
	public double getLodAverage() {
		return lodAverage;
	}

	@Column(name = "MIN_lod")
	public double getLodMin() {
		return lodMin;
	}

	@Column(name = "MAX_lod")
	public double getLodMax() {
		return lodMax;
	}

	@Column(name = "STDDEV_lod")
	public double getLodStdDev() {
		if(lodStdDev == null) return 0;
		return lodStdDev;
	}

	@Column(name = "AVG_samechromosome")
	public double getFrequencySameChromosome() {
		return frequencySameChromosome;
	}
	
	@Transient
	public double getFrequencyCis() {
		return getFrequencySameChromosome();
	}

	protected void setEqtlCount(int eqtlCount) {
		this.eqtlCount = eqtlCount;
	}

	protected void setLodAverage(double lodAverage) {
		this.lodAverage = lodAverage;
	}

	protected void setLodMin(double lodMin) {
		this.lodMin = lodMin;
	}

	protected void setLodMax(double lodMax) {
		this.lodMax = lodMax;
	}

	protected void setLodStdDev(Double lodStdDev) {
		this.lodStdDev = lodStdDev;
	}

	protected void setFrequencySameChromosome(double frequencySameChromosome) {
		this.frequencySameChromosome = frequencySameChromosome;
	}

}
