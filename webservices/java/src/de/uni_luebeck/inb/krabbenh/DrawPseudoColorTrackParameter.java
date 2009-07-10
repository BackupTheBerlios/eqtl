package de.uni_luebeck.inb.krabbenh;

import java.awt.Graphics;
import java.util.List;

import de.uni_luebeck.inb.krabbenh.entities.Covariate;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox;

public class DrawPseudoColorTrackParameter {
	public List<MillionBasepairBox> mbpbl;
	public Covariate covariate;
	public long fromBP;
	public int bpPerPixel;
	public int contentHeight;
	public Graphics g;
	public int start;

	public DrawPseudoColorTrackParameter(List<MillionBasepairBox> mbpbl, Covariate covariate, long fromBP, int bpPerPixel, int contentHeight, Graphics g, int start) {
		this.mbpbl = mbpbl;
		this.covariate = covariate;
		this.fromBP = fromBP;
		this.bpPerPixel = bpPerPixel;
		this.contentHeight = contentHeight;
		this.g = g;
		this.start = start;
	}
}