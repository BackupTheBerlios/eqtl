package de.uni_luebeck.inb.krabbenh;

import java.awt.Color;
import java.awt.Frame;
import java.awt.Graphics;
import java.awt.Image;
import java.io.IOException;
import java.util.List;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox;
import de.uni_luebeck.inb.krabbenh.helpers.RunInsideTransaction;


public class DrawChromosomeImages {
	public static void main(String[] args) throws IOException {
		new RunInsideTransaction() {
			@SuppressWarnings("unchecked")
			@Override
			public void work(Transaction transaction, Session session) throws Exception {
				FetchEnsemblDas ensemblDas = new FetchEnsemblDas();
				List<String> chromosomes = session.createQuery("select chromosome from Locus group by chromosome").list();
				for (String chromosome : chromosomes) {
					EnsemblBand[] ensemblBands = ensemblDas.getEnsemblBands(chromosome);
					List<MillionBasepairBox> mbpbl = session.createQuery("from MillionBasepairBox as mbpb join fetch mbpb.statisticsAll join fetch mbpb.statisticsCis ").list();
					long fromBP = Long.MAX_VALUE;
					long toBP = Long.MIN_VALUE;
					for (MillionBasepairBox cur : mbpbl) {
						fromBP = Math.min(fromBP, cur.getFromBP());
						toBP = Math.max(toBP, cur.getToBP());
					}
					
					int bpPerPixel = 1000;
					Frame f = new Frame();
				    Image image = f.createImage(100, (int) ((toBP-fromBP) / bpPerPixel));
				    Graphics g = image.getGraphics();
				    g.setColor(new Color(1,1,1,0));
				    g.fillRect(0, 0, image.getWidth(null), image.getHeight(null));
				    int curX = 0;
				    Color col4type[] = new Color[]{ Color.BLACK, Color.GRAY, Color.WHITE };
				    Color icol4type[] = new Color[]{ Color.WHITE, Color.BLACK, Color.BLACK };
				    for (EnsemblBand ensemblBand : ensemblBands) {
						g.setColor(col4type[ensemblBand.type-1]);
					}
				}
			}
		}.run();

	}
}
