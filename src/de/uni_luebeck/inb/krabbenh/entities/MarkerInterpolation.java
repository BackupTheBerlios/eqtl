package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;

import org.hibernate.annotations.Index;

@Entity
public class MarkerInterpolation implements Serializable {
	private static final long serialVersionUID = 1L;

	private int id;

	private String chromosome;

	private double interpolatedFrom; // in cMorgan
	private double interpolatedTo; // in cMorgan
	
	private long interpolatedFromBP;
	private long interpolatedToBP;

	@Id
	@GeneratedValue
	@Column(unique = true, nullable = false)
	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	@Index(name="chromosome_index")
	public String getChromosome() {
		return chromosome;
	}

	public void setChromosome(String chromosome) {
		this.chromosome = chromosome;
	}

	@Index(name="interpolatedFrom_index")
	public double getInterpolatedFrom() {
		return interpolatedFrom;
	}

	public void setInterpolatedFrom(double interpolatedFrom) {
		this.interpolatedFrom = interpolatedFrom;
	}

	@Index(name="interpolatedTo_index")
	public double getInterpolatedTo() {
		return interpolatedTo;
	}

	public void setInterpolatedTo(double interpolatedTo) {
		this.interpolatedTo = interpolatedTo;
	}

	public long getInterpolatedFromBP() {
		return interpolatedFromBP;
	}

	public void setInterpolatedFromBP(long interpolatedFromBP) {
		this.interpolatedFromBP = interpolatedFromBP;
	}

	public long getInterpolatedToBP() {
		return interpolatedToBP;
	}

	public void setInterpolatedToBP(long interpolatedToBP) {
		this.interpolatedToBP = interpolatedToBP;
	}

}
