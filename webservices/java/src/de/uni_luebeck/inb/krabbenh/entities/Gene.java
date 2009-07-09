package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;

import org.hibernate.annotations.Index;


@Entity
public class Gene implements Serializable {
	private static final long serialVersionUID = 1L;

	private int id;
	private long entrezId;

	private String chromosome;
	private boolean positiveStrand;
	private long fromBp;
	private long toBp;

	public Gene() {
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

	public long getEntrezId() {
		return entrezId;
	}

	public void setEntrezId(long entrezId) {
		this.entrezId = entrezId;
	}

	@Index(name="chromosome_index")
	public String getChromosome() {
		return chromosome;
	}

	public void setChromosome(String chromosome) {
		this.chromosome = chromosome;
	}

	public boolean isPositiveStrand() {
		return positiveStrand;
	}

	public void setPositiveStrand(boolean positiveStrand) {
		this.positiveStrand = positiveStrand;
	}

	@Index(name="fromBP_index")
	public long getFromBp() {
		return fromBp;
	}

	public void setFromBp(long fromBp) {
		this.fromBp = fromBp;
	}

	@Index(name="toBP_index")
	public long getToBp() {
		return toBp;
	}

	public void setToBp(long toBp) {
		this.toBp = toBp;
	}

}
