package de.uni_luebeck.inb.krabbenh.entities;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;
import javax.persistence.ManyToOne;

import org.hibernate.annotations.Index;

@Entity
public class ExpressionQTL implements java.io.Serializable {
	private static final long serialVersionUID = 1L;
	
	private int id;
	private Covariate covariate;
	
	private double LOD;
	private Locus locus;
	private Gene gene;
	
	//calculated:
	private boolean sameChromosome; // for snip and locus
	private long distanceBP;
	
	@Id
	@GeneratedValue
	@Column(unique = true, nullable = false)
	public int getId() {
		return id;
	}
	public void setId(int id) {
		this.id = id;
	}
	
	@ManyToOne(optional=false)
	public Covariate getCovariate() {
		return covariate;
	}
	public void setCovariate(Covariate covariate) {
		this.covariate = covariate;
	}
	
	public double getLOD() {
		return LOD;
	}
	public void setLOD(double lod) {
		LOD = lod;
	}
	
	@ManyToOne(optional=false)
	@Index(name="locus_index")
	public Locus getLocus() {
		return locus;
	}
	public void setLocus(Locus locus) {
		this.locus = locus;
	}
	
	@ManyToOne(optional=false)
	@Index(name="snip_index")
	public Gene getGene() {
		return gene;
	}
	public void setGene(Gene gene) {
		this.gene = gene;
	}
	public boolean isSameChromosome() {
		return sameChromosome;
	}
	public void setSameChromosome(boolean sameChromosome) {
		this.sameChromosome = sameChromosome;
	}
	public long getDistanceBP() {
		return distanceBP;
	}
	public void setDistanceBP(long distanceBP) {
		this.distanceBP = distanceBP;
	}
}
