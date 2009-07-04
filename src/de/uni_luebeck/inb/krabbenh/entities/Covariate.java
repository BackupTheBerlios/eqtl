package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.List;

import javax.persistence.Column;
import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.Id;

import org.hibernate.annotations.CollectionOfElements;

@Entity
public class Covariate implements Serializable {
	private static final long serialVersionUID = 1L;

	private int id;
	private List<String> names = new ArrayList<String>();

	@Id
	@GeneratedValue
	@Column(unique = true, nullable = false)
	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	@CollectionOfElements
	public List<String> getNames() {
		return names;
	}

	public void setNames(List<String> names) {
		this.names = names;
	}

}
