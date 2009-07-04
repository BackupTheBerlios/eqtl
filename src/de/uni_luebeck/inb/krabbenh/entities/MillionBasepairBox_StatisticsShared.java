package de.uni_luebeck.inb.krabbenh.entities;

import java.io.Serializable;

public class MillionBasepairBox_StatisticsShared implements Serializable {
	private static final long serialVersionUID = 1L;

	private int millionBasepairBox;
	private int covariate;

	public int getMillionBasepairBox() {
		return millionBasepairBox;
	}

	public void setMillionBasepairBox(int millionBasepairBox) {
		this.millionBasepairBox = millionBasepairBox;
	}

	public int getCovariate() {
		return covariate;
	}

	public void setCovariate(int covariate) {
		this.covariate = covariate;
	}

	@Override
	public int hashCode() {
		final int prime = 31;
		int result = 1;
		result = prime * result + covariate;
		result = prime * result + millionBasepairBox;
		return result;
	}

	@Override
	public boolean equals(Object obj) {
		if (this == obj)
			return true;
		if (obj == null)
			return false;
		if (getClass() != obj.getClass())
			return false;
		MillionBasepairBox_StatisticsShared other = (MillionBasepairBox_StatisticsShared) obj;
		if (covariate != other.covariate)
			return false;
		if (millionBasepairBox != other.millionBasepairBox)
			return false;
		return true;
	}
}