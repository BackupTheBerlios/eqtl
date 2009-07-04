package de.uni_luebeck.inb.krabbenh;

import java.awt.Color;
import java.awt.Graphics;
import java.awt.Image;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.util.Map;
import java.util.SortedMap;
import java.util.TreeMap;

import javax.imageio.ImageIO;

import de.uni_luebeck.inb.krabbenh.entities.Covariate;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_StatisticsAll;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_StatisticsCis;
import de.uni_luebeck.inb.krabbenh.helpers.DrawChromosomeImagesHelper;

public class DrawChromosomeImages {

	public static void main(String[] args) throws IOException {

		final SortedMap<String, Image> chr2img = new TreeMap<String, Image>();

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
									invoke2 = MillionBasepairBox_StatisticsAll.class.getMethod(gettername).invoke(stat);
									if (invoke2 instanceof Integer)
										box2value.put(cur, (double) (Integer) invoke2);
									else
										box2value.put(cur, (Double) invoke2);
								} catch (Exception e) {
									e.printStackTrace();
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
									invoke2 = MillionBasepairBox_StatisticsCis.class.getMethod(gettername).invoke(stat);
									if (invoke2 instanceof Integer)
										box2value.put(cur, (double) (Integer) invoke2);
									else
										box2value.put(cur, (Double) invoke2);
								} catch (Exception e) {
									e.printStackTrace();
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
				chr2img.put(chromosome, subimage);
			}

		}.run();

		int width = 0, height = 0;
		for (Image img : chr2img.values()) {
			width += img.getWidth(null) + 10;
			height = Math.max(height, img.getHeight(null) + 30);
		}

		int curx = 5;
		BufferedImage complete = new BufferedImage(width, height, BufferedImage.TYPE_INT_RGB);
		Graphics graphics = complete.getGraphics();
		graphics.setColor(Color.WHITE);
		graphics.fillRect(0, 0, complete.getWidth(), complete.getHeight());
		graphics.setColor(Color.BLACK);
		for (Map.Entry<String, Image> cur : chr2img.entrySet()) {
			graphics.drawImage(cur.getValue(), curx, 25, null);
			graphics.drawString("Chromosome " + cur.getKey(), curx, 15);
			curx += cur.getValue().getWidth(null) + 10;
		}
		graphics.dispose();
		ImageIO.write(complete, "png", new File("images/chromosomes.png"));
	}
}
