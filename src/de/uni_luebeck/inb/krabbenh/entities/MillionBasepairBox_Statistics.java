package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;

@Entity
public class MillionBasepairBox_Statistics implements Serializable {
	private static final long serialVersionUID = 1L;

	private int id;
	private Covariate covariate;

	private int allEqtlCount;

	private double allLodSum;
	private double allLodAverage;
	private double allLodMin;
	private double allLodMax;
	private double allLodStdDev;

	private double frequencySameChromosome;

	private int cisEqtlCount;

	private double cisLodSum;
	private double cisLodAverage;
	private double cisLodMin;
	private double cisLodMax;
	private double cisLodStdDev;

	private double cisDistanceAverage;
	private double cisDistanceMin;
	private double cisDistanceMax;
	private double cisDistanceStdDev;

	@Id
	@GeneratedValue
	@Column(unique = true, nullable = false)
	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public Covariate getCovariate() {
		return covariate;
	}

	public void setCovariate(Covariate covariate) {
		this.covariate = covariate;
	}

	public int getAllEqtlCount() {
		return allEqtlCount;
	}

	public void setAllEqtlCount(int allEqtlCount) {
		this.allEqtlCount = allEqtlCount;
	}

	public double getAllLodSum() {
		return allLodSum;
	}

	public void setAllLodSum(double allLodSum) {
		this.allLodSum = allLodSum;
	}

	public double getAllLodAverage() {
		return allLodAverage;
	}

	public void setAllLodAverage(double allLodAverage) {
		this.allLodAverage = allLodAverage;
	}

	public double getAllLodMin() {
		return allLodMin;
	}

	public void setAllLodMin(double allLodMin) {
		this.allLodMin = allLodMin;
	}

	public double getAllLodMax() {
		return allLodMax;
	}

	public void setAllLodMax(double allLodMax) {
		this.allLodMax = allLodMax;
	}

	public double getAllLodStdDev() {
		return allLodStdDev;
	}

	public void setAllLodStdDev(double allLodStdDev) {
		this.allLodStdDev = allLodStdDev;
	}

	public double getFrequencySameChromosome() {
		return frequencySameChromosome;
	}

	public void setFrequencySameChromosome(double frequencySameChromosome) {
		this.frequencySameChromosome = frequencySameChromosome;
	}

	public int getCisEqtlCount() {
		return cisEqtlCount;
	}

	public void setCisEqtlCount(int cisEqtlCount) {
		this.cisEqtlCount = cisEqtlCount;
	}

	public double getCisLodSum() {
		return cisLodSum;
	}

	public void setCisLodSum(double cisLodSum) {
		this.cisLodSum = cisLodSum;
	}

	public double getCisLodAverage() {
		return cisLodAverage;
	}

	public void setCisLodAverage(double cisLodAverage) {
		this.cisLodAverage = cisLodAverage;
	}

	public double getCisLodMin() {
		return cisLodMin;
	}

	public void setCisLodMin(double cisLodMin) {
		this.cisLodMin = cisLodMin;
	}

	public double getCisLodMax() {
		return cisLodMax;
	}

	public void setCisLodMax(double cisLodMax) {
		this.cisLodMax = cisLodMax;
	}

	public double getCisLodStdDev() {
		return cisLodStdDev;
	}

	public void setCisLodStdDev(double cisLodStdDev) {
		this.cisLodStdDev = cisLodStdDev;
	}

	public double getCisDistanceAverage() {
		return cisDistanceAverage;
	}

	public void setCisDistanceAverage(double cisDistanceAverage) {
		this.cisDistanceAverage = cisDistanceAverage;
	}

	public double getCisDistanceMin() {
		return cisDistanceMin;
	}

	public void setCisDistanceMin(double cisDistanceMin) {
		this.cisDistanceMin = cisDistanceMin;
	}

	public double getCisDistanceMax() {
		return cisDistanceMax;
	}

	public void setCisDistanceMax(double cisDistanceMax) {
		this.cisDistanceMax = cisDistanceMax;
	}

	public double getCisDistanceStdDev() {
		return cisDistanceStdDev;
	}

	public void setCisDistanceStdDev(double cisDistanceStdDev) {
		this.cisDistanceStdDev = cisDistanceStdDev;
	}

}
