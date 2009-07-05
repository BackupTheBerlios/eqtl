package de.uni_luebeck.inb.krabbenh;

import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.util.List;
import java.util.Map;

import javax.imageio.ImageIO;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.Covariate;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_Statistics;
import de.uni_luebeck.inb.krabbenh.helpers.DrawChromosomeImagesHelper;
import de.uni_luebeck.inb.krabbenh.helpers.RunInsideTransaction;

public class DrawChromosomeCovariateChanges {

	private static final class CovListFetcher extends RunInsideTransaction {
		List<Covariate> covariates;

		@SuppressWarnings("unchecked")
		@Override
		public void work(Transaction transaction, Session session) throws Exception {
			covariates = session.createQuery("from Covariate as c join fetch c.names").list();
		}
	}

	public static void main(String[] args) throws IOException {
		final CovListFetcher covListFetcher = new CovListFetcher();
		covListFetcher.run();

		new DrawChromosomeImagesHelper() {
			@Override
			protected int drawPseudocolorTracks(final Covariate noCovariatesCovariate, int curX, DrawPseudoColorTrackParameter pseudocolorParameters) {
				for (final Covariate covariate : covListFetcher.covariates) {
					if (noCovariatesCovariate.getId() == covariate.getId())
						continue;

					MillionBasepairBoxValueProvider provider = new MillionBasepairBoxValueProvider() {
						public void addToMap(Map<MillionBasepairBox, Double> box2value, MillionBasepairBox cur) {
							MillionBasepairBox_Statistics from = null, to = null;
							for (MillionBasepairBox_Statistics stat : cur.getStatistics()) {
								if (stat.getCovariate().getId() != noCovariatesCovariate.getId())
									continue;
								from = stat;
							}
							for (MillionBasepairBox_Statistics stat : cur.getStatistics()) {
								if (stat.getCovariate().getId() != covariate.getId())
									continue;
								to = stat;
							}

							double fromV = from == null ? 0.0 : from.getAllLodSum();
							double toV = to == null ? 0.0 : to.getAllLodSum();
							box2value.put(cur, toV - fromV);
						}

						public String getTitle() {
							return covariate.getNames().toString();
						}
					};
					curX = drawPseudoColorTrack(pseudocolorParameters, curX, provider, 15);
				}
				return curX;
			}

			@Override
			protected void imageForChromosomeComplete(String chromosome, BufferedImage subimage) {
				try {
					ImageIO.write(subimage, "png", new File("images/covar_change_" + chromosome + ".png"));
				} catch (IOException e) {
					e.printStackTrace();
				}
			}

		}.run();
	}
}
