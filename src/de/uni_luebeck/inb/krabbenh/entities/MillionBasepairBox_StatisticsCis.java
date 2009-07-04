package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.Table;

import org.hibernate.annotations.Immutable;

@Entity
@Table(name = "millionbasepairbox_statistics_cis")
@Immutable
public class MillionBasepairBox_StatisticsCis implements Serializable {
	private static final long serialVersionUID = 1L;

	private int id;

	private int eqtlCount;

	private double lodAverage;
	private double lodMin;
	private double lodMax;
	private double lodStdDev;

	private double distanceAverage;
	private double distanceMin;
	private double distanceMax;
	private double distanceStdDev;

	@Id
	@Column(name = "millionbasepairbox_id")
	public int getId() {
		return id;
	}

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
		return lodStdDev;
	}

	@Column(name = "AVG_distance")
	public double getDistanceAverage() {
		return distanceAverage;
	}

	@Column(name = "MIN_distance")
	public double getDistanceMin() {
		return distanceMin;
	}

	@Column(name = "MAX_distance")
	public double getDistanceMax() {
		return distanceMax;
	}

	@Column(name = "STDDEV_distance")
	public double getDistanceStdDev() {
		return distanceStdDev;
	}

}
