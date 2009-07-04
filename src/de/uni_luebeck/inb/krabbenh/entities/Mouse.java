package de.uni_luebeck.inb.krabbenh.entities;

import java.util.Set;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.Id;
import javax.persistence.OneToMany;

@Entity
public class Mouse implements java.io.Serializable {

	private int id;

	private Byte sex;

	private Float auc;

	private Integer onset;

	private Integer severity;
	
	private Set<MouseExpression> expressions;

	@Id
	@Column(unique = true, nullable = false)
	public int getId() {
		return this.id;
	}

	public void setId(int id) {
		this.id = id;
	}

	@Column()
	public Byte getSex() {
		return this.sex;
	}

	public void setSex(Byte sex) {
		this.sex = sex;
	}

	@Column()
	public Float getAuc() {
		return this.auc;
	}

	public void setAuc(Float auc) {
		this.auc = auc;
	}

	@Column()
	public Integer getOnset() {
		return this.onset;
	}

	public void setOnset(Integer onset) {
		this.onset = onset;
	}

	@Column()
	public Integer getSeverity() {
		return this.severity;
	}

	public void setSeverity(Integer severity) {
		this.severity = severity;
	}

	public void setExpressions(Set<MouseExpression> expressions) {
		this.expressions = expressions;
	}

	@OneToMany(mappedBy="mouse")
	public Set<MouseExpression> getExpressions() {
		return expressions;
	}

}
