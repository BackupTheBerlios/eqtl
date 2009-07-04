package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.IdClass;
import javax.persistence.Table;

import org.hibernate.annotations.Immutable;

@Entity
@Table(name="millionbasepairbox_statistics_cis")
@Immutable
@IdClass(MillionBasepairBox_StatisticsPrimaryKey.class)
public class MillionBasepairBox_StatisticsCis implements Serializable {
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

	private double distanceAverage;
	private double distanceMin;
	private double distanceMax;
	private Double distanceStdDev; // may be null

	
	@Column(name="eqtlcount")
	public int getEqtlCount() {
		return eqtlCount;
	}


	@Column(name="AVG_lod")
	public double getLodAverage() {
		return lodAverage;
	}


	@Column(name="MIN_lod")
	public double getLodMin() {
		return lodMin;
	}


	@Column(name="MAX_lod")
	public double getLodMax() {
		return lodMax;
	}


	@Column(name="STDDEV_lod")
	public double getLodStdDev() {
		if(lodStdDev == null) return 0;
		return lodStdDev;
	}



	@Column(name="AVG_distance")
	public double getDistanceAverage() {
		return distanceAverage;
	}


	@Column(name="MIN_distance")
	public double getDistanceMin() {
		return distanceMin;
	}


	@Column(name="MAX_distance")
	public double getDistanceMax() {
		return distanceMax;
	}


	@Column(name="STDDEV_distance")
	public double getDistanceStdDev() {
		if(distanceStdDev == null) return 0;
		return distanceStdDev;
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


	protected void setDistanceAverage(double distanceAverage) {
		this.distanceAverage = distanceAverage;
	}


	protected void setDistanceMin(double distanceMin) {
		this.distanceMin = distanceMin;
	}


	protected void setDistanceMax(double distanceMax) {
		this.distanceMax = distanceMax;
	}


	protected void setDistanceStdDev(Double distanceStdDev) {
		this.distanceStdDev = distanceStdDev;
	}

}
