package de.uni_luebeck.inb.krabbenh;

import java.awt.Color;
import java.awt.Font;
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
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_Statistics;
import de.uni_luebeck.inb.krabbenh.helpers.DrawChromosomeImagesHelper;

public class DrawChromosomeImages {

	public static void main(String[] args) throws IOException {

		final SortedMap<Integer, Image> chr2img = new TreeMap<Integer, Image>();

		new DrawChromosomeImagesHelper() {
			@Override
			protected int drawPseudocolorTracks(final Covariate covariate, int curX, DrawPseudoColorTrackParameter pseudocolorParameters) {
				String[] getters = new String[] { "getAllEqtlCount", "getAllLodAverage", "getAllLodMin", "getAllLodMax", "getAllLodStdDev", "getFrequencySameChromosome", "getCisEqtlCount", "getCisLodAverage", "getCisLodMin", "getCisLodMax", "getCisLodStdDev", "getCisDistanceAverage", "getCisDistanceMin", "getCisDistanceMax", "getCisDistanceStdDev" };
				for (final String gettername : getters) {
					MillionBasepairBoxValueProvider provider = new MillionBasepairBoxValueProvider() {
						public void addToMap(Map<MillionBasepairBox, Double> box2value, MillionBasepairBox cur) {
							for (MillionBasepairBox_Statistics stat : cur.getStatistics()) {
								if (stat.getCovariate().getId() != covariate.getId())
									continue;
								Object invoke2;
								try {
									invoke2 = MillionBasepairBox_Statistics.class.getMethod(gettername).invoke(stat);
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
							return gettername.substring(3);
						}
					};
					curX = drawPseudoColorTrack(pseudocolorParameters, curX, provider, gettername.equals("getEqtlCount") ? 30 : 15);
				}
				return curX;
			}

			@Override
			protected void imageForChromosomeComplete(String chromosome, BufferedImage subimage) {
				chr2img.put(Integer.valueOf(chromosome), subimage);
			}

		}.run();

		int width = 0, height = 0;
		for (Image img : chr2img.values()) {
			width += img.getWidth(null) + 10;
			height = Math.max(height, img.getHeight(null) + 60);
		}

		int curx = 5;
		BufferedImage complete = new BufferedImage(width, height, BufferedImage.TYPE_INT_RGB);
		Graphics graphics = complete.getGraphics();
		graphics.setColor(Color.WHITE);
		graphics.fillRect(0, 0, complete.getWidth(), complete.getHeight());
		graphics.setColor(Color.BLACK);
		graphics.setFont(new Font("Calibri", Font.BOLD, 50));
		for (Map.Entry<Integer, Image> cur : chr2img.entrySet()) {
			graphics.drawImage(cur.getValue(), curx, 60, null);
			graphics.drawString("" + cur.getKey(), curx, 55);
			curx += cur.getValue().getWidth(null) + 10;
		}
		graphics.dispose();
		ImageIO.write(complete, "png", new File("images/chromosomes.png"));
	}
}
