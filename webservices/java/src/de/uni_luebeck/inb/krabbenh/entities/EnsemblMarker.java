package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;

@Entity
public class EnsemblMarker implements Serializable {
	private static final long serialVersionUID = 1L;

	private int id;
	private String name;
	private long positionBP;

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

	public long getPositionBP() {
		return positionBP;
	}

	public void setPositionBP(long positionBP) {
		this.positionBP = positionBP;
	}


}
