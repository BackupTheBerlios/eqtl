package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.ManyToOne;

import org.hibernate.annotations.Index;

@Entity
public class Locus implements Serializable {
	private static final long serialVersionUID = 1L;

	private int id;

	private String name;
	private String chromosome;
	private double position; // in cMorgan

	//calculated:
	private long positionBP;
	private boolean interpolatedPosition;
	private MarkerInterpolation markerInterpolation; //if not interpolated, use range that contains me as start
	
	public Locus() {
	}
	
	@Id
	@GeneratedValue
	@Column(unique = true, nullable = false)
	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public String getName() {
		return name;
	}

	public void setName(String name) {
		this.name = name;
	}

	@Index(name="chromosome_index")
	public String getChromosome() {
		return chromosome;
	}

	public void setChromosome(String chromosome) {
		this.chromosome = chromosome;
	}

	public double getPosition() {
		return position;
	}

	public void setPosition(double position) {
		this.position = position;
	}

	@Index(name="positionBP_index")
	public long getPositionBP() {
		return positionBP;
	}

	public void setPositionBP(long positionBP) {
		this.positionBP = positionBP;
	}

	public boolean isInterpolatedPosition() {
		return interpolatedPosition;
	}

	public void setInterpolatedPosition(boolean interpolatedPosition) {
		this.interpolatedPosition = interpolatedPosition;
	}

	@ManyToOne(optional=false)
	public MarkerInterpolation getMarkerInterpolation() {
		return markerInterpolation;
	}

	public void setMarkerInterpolation(MarkerInterpolation markerInterpolation) {
		this.markerInterpolation = markerInterpolation;
	}


}
