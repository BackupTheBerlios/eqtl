package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;
import java.util.HashSet;
import java.util.Set;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.ManyToMany;

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

	private Set<ExpressionQTL> containedExpressionQTLs = new HashSet<ExpressionQTL>();

	@Id
	@GeneratedValue
	@Column(unique = true, nullable = false)
	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	@Index(name = "chromosome_index")
	public String getChromosome() {
		return chromosome;
	}

	public void setChromosome(String chromosome) {
		this.chromosome = chromosome;
	}

	@Index(name = "interpolatedFrom_index")
	public double getInterpolatedFrom() {
		return interpolatedFrom;
	}

	public void setInterpolatedFrom(double interpolatedFrom) {
		this.interpolatedFrom = interpolatedFrom;
	}

	@Index(name = "interpolatedTo_index")
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

	@ManyToMany
	public Set<ExpressionQTL> getContainedExpressionQTLs() {
		return containedExpressionQTLs;
	}

	public void setContainedExpressionQTLs(Set<ExpressionQTL> containedExpressionQTLs) {
		this.containedExpressionQTLs = containedExpressionQTLs;
	}

	public long getInterpolatedBpFor(double position) {
		assert interpolatedFrom <= position;
		assert interpolatedTo >= position;
		double perc = (position - interpolatedFrom) / (interpolatedTo - interpolatedFrom);
		return interpolatedFromBP + (long) ((interpolatedToBP - interpolatedFromBP) * perc);
	}
}
