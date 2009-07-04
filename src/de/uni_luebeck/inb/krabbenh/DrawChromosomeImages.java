package de.uni_luebeck.inb.krabbenh;

import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.util.Map;

import javax.imageio.ImageIO;

import de.uni_luebeck.inb.krabbenh.entities.Covariate;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_StatisticsAll;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_StatisticsCis;
import de.uni_luebeck.inb.krabbenh.helpers.DrawChromosomeImagesHelper;

public class DrawChromosomeImages {

	public static void main(String[] args) throws IOException {

		new DrawChromosomeImagesHelper() {
			@Override
			protected int drawPseudocolorTracks(final Covariate covariate, int curX, DrawPseudoColorTrackParameter pseudocolorParameters) {
				String[] getters = new String[] { "getEqtlCount", "getLodAverage", "getLodMin", "getLodMax", "getLodStdDev", "getFrequencyCis" };
				for (final String gettername : getters) {
					MillionBasepairBoxValueProvider provider = new MillionBasepairBoxValueProvider() {
						public void addToMap(Map<MillionBasepairBox, Double> box2value, MillionBasepairBox cur) {
							for (MillionBasepairBox_StatisticsAll stat : cur.getStatisticsAll()) {
								if (stat.getCovariate() != covariate.getId())
									continue;
								Object invoke2;
								try {
									invoke2 = MillionBasepairBox_StatisticsAll.class.getMethod(gettername).invoke(cur);
									if (invoke2 instanceof Integer)
										box2value.put(cur, (double) (Integer) invoke2);
									else
										box2value.put(cur, (Double) invoke2);
								} catch (Exception e) {
								}
							}
						}

						public String getTitle() {
							return "All " + gettername.substring(3);
						}
					};
					curX = drawPseudoColorTrack(pseudocolorParameters, curX, provider, gettername.equals("getEqtlCount") ? 30 : 15);
				}
				getters = new String[] { "getEqtlCount", "getLodAverage", "getLodMin", "getLodMax", "getLodStdDev", "getDistanceAverage", "getDistanceMin", "getDistanceMax", "getDistanceStdDev" };
				for (final String gettername : getters) {
					MillionBasepairBoxValueProvider provider = new MillionBasepairBoxValueProvider() {
						public void addToMap(Map<MillionBasepairBox, Double> box2value, MillionBasepairBox cur) {
							for (MillionBasepairBox_StatisticsCis stat : cur.getStatisticsCis()) {
								if (stat.getCovariate() != covariate.getId())
									continue;
								Object invoke2;
								try {
									invoke2 = MillionBasepairBox_StatisticsCis.class.getMethod(gettername).invoke(cur);
									if (invoke2 instanceof Integer)
										box2value.put(cur, (double) (Integer) invoke2);
									else
										box2value.put(cur, (Double) invoke2);
								} catch (Exception e) {
								}
							}
						}

						public String getTitle() {
							return "Cis " + gettername.substring(3);
						}
					};
					curX = drawPseudoColorTrack(pseudocolorParameters, curX, provider, gettername.equals("getEqtlCount") ? 30 : 15);
				}
				return curX;
			}

			@Override
			protected void imageForChromosomeComplete(String chromosome, BufferedImage subimage) {
				try {
					ImageIO.write(subimage, "png", new File("images/chromosome" + chromosome + ".png"));
				} catch (IOException e) {
					e.printStackTrace();
				}
			}

		}.run();
	}
}
