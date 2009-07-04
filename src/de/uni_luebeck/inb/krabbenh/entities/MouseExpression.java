package de.uni_luebeck.inb.krabbenh.entities;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.ManyToOne;



@Entity
public class MouseExpression implements java.io.Serializable {
	private int id;
	private Mouse mouse;
	private Snip snip;
	private Float value;

	public void setId(int id) {
		this.id = id;
	}

	@Id
	@GeneratedValue(strategy=GenerationType.IDENTITY)
	public int getId() {
		return id;
	}

	public void setMouse(Mouse mouse) {
		this.mouse = mouse;
	}

	@ManyToOne
	public Mouse getMouse() {
		return mouse;
	}

	public void setSnip(Snip snip) {
		this.snip = snip;
	}

	@ManyToOne
	public Snip getSnip() {
		return snip;
	}

	public void setValue(Float value) {
		this.value = value;
	}

	@Column(nullable=false)
	public Float getValue() {
		return value;
	}
}
