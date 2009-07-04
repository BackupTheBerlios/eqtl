package de.uni_luebeck.inb.krabbenh;

import java.awt.Color;
import java.awt.Font;
import java.awt.Graphics;
import java.awt.geom.AffineTransform;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;
import java.text.DecimalFormat;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;
import java.util.TreeSet;

import javax.imageio.ImageIO;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.Covariate;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_StatisticsAll;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_StatisticsCis;
import de.uni_luebeck.inb.krabbenh.helpers.RunInsideTransaction;

public class DrawChromosomeImages {
	public static void main(String[] args) throws IOException {
		final Color fakeColors[] = new Color[256];
		for (int i = 0; i < fakeColors.length; i++) {
			fakeColors[i] = new Color(Math.min(1, (float) (256 - i) / 128.0f), Math.min(1, (float) i / 128.0f), 0, 1);
		}

		new RunInsideTransaction() {

			@SuppressWarnings("unchecked")
			@Override
			public void work(Transaction transaction, Session session) throws Exception {
				List<Covariate> covariates = session.createQuery("from Covariate").list();
				Covariate covariate = null;
				for (Covariate cur : covariates) {
					if (cur.getNames().size() == 0)
						covariate = cur;
				}

				FetchEnsemblDas ensemblDas = new FetchEnsemblDas();
				List<String> chromosomes = session.createQuery("select chromosome from Locus group by chromosome").list();
				for (String chromosome : chromosomes) {
					EnsemblBand[] ensemblBands = ensemblDas.getEnsemblBands(chromosome);
					List<MillionBasepairBox> mbpbl = session.createQuery("from MillionBasepairBox where chromosome=:chr").setParameter("chr", chromosome).list();
					if (mbpbl.size() == 0)
						continue;

					long fromBP = Long.MAX_VALUE;
					long toBP = Long.MIN_VALUE;
					for (MillionBasepairBox cur : mbpbl) {
						fromBP = Math.min(fromBP, cur.getFromBP());
						toBP = Math.max(toBP, cur.getToBP());
					}
					for (EnsemblBand cur : ensemblBands) {
						fromBP = Math.min(fromBP, cur.from);
						toBP = Math.max(toBP, cur.to);
					}

					int bpPerPixel = 100 * 1000;
					int yStart = 150;
					int contentHeight = (int) ((toBP - fromBP) / bpPerPixel) + yStart;
					BufferedImage image = new BufferedImage(500, contentHeight + 350, BufferedImage.TYPE_INT_RGB);
					Graphics g = image.getGraphics();
					g.setColor(new Color(0.0f, 0.0f, 0.3f, 1.0f));
					g.fillRect(0, 0, image.getWidth(), image.getHeight());
					g.setColor(Color.WHITE);
					g.fillRect(0, contentHeight, image.getWidth(), image.getHeight() - contentHeight);
					g.fillRect(0, 0, image.getWidth(), 100);
					contentHeight += 100;
					int curX = 0;
					Color col4type[] = new Color[] { Color.BLACK, Color.GRAY, Color.WHITE };
					Color icol4type[] = new Color[] { Color.WHITE, Color.BLACK, Color.BLACK };
					Set<Long> drawPos = new TreeSet<Long>();
					for (EnsemblBand ensemblBand : ensemblBands) {
						g.setColor(col4type[ensemblBand.type - 1]);
						g.fillRect(curX, yStart + (int) ((ensemblBand.from - fromBP) / bpPerPixel), 30, (int) ((ensemblBand.to - ensemblBand.from) / bpPerPixel + 1));
						g.setColor(icol4type[ensemblBand.type - 1]);
						g.drawString(ensemblBand.label, curX, yStart + (int) (((ensemblBand.to + ensemblBand.from) / 2 - fromBP) / bpPerPixel) + 5);
						drawPos.add(ensemblBand.from);
						drawPos.add(ensemblBand.to);
					}
					curX += 30;
					curX = drawBpLines(fromBP, bpPerPixel, yStart, image, g, curX, drawPos);

					Method getter1 = MillionBasepairBox.class.getMethod("getStatisticsAll");
					String[] getters = new String[] { "getEqtlCount", "getLodAverage", "getLodMin", "getLodMax", "getLodStdDev", "getFrequencyCis" };
					for (String gettername : getters) {
						Method getter2 = MillionBasepairBox_StatisticsAll.class.getMethod(gettername);
						Method getter3 = MillionBasepairBox_StatisticsAll.class.getMethod("getCovariate");
						curX = drawPseudoColorTrack(mbpbl, covariate, fromBP, bpPerPixel, contentHeight, g, curX, getter2, getter1, yStart, getter3, gettername.equals("getEqtlCount") ? 50 : 15);
					}
					getter1 = MillionBasepairBox.class.getMethod("getStatisticsCis");
					getters = new String[] { "getEqtlCount", "getLodAverage", "getLodMin", "getLodMax", "getLodStdDev", "getDistanceAverage", "getDistanceMin", "getDistanceMax", "getDistanceStdDev" };
					for (String gettername : getters) {
						Method getter2 = MillionBasepairBox_StatisticsCis.class.getMethod(gettername);
						Method getter3 = MillionBasepairBox_StatisticsCis.class.getMethod("getCovariate");
						curX = drawPseudoColorTrack(mbpbl, covariate, fromBP, bpPerPixel, contentHeight, g, curX, getter2, getter1, yStart, getter3, gettername.equals("getEqtlCount") ? 50 : 15);
					}

					g.dispose();
					ImageIO.write(image.getSubimage(0, 0, curX, image.getHeight()), "png", new File("images/chromosome" + chromosome + ".png"));
				}
			}

			private int drawBpLines(long fromBP, int bpPerPixel, int yStart, BufferedImage image, Graphics g, int curX, Set<Long> drawPos) {
				g.setColor(Color.WHITE);
				g.fillRect(curX, 0, 25, image.getHeight());
				g.setColor(Color.BLACK);
				Font theFont = g.getFont();
				AffineTransform fontAT = new AffineTransform();
				fontAT.rotate(Math.PI / 2.0);
				g.setFont(theFont.deriveFont(fontAT));
				int curY = -1000;
				for (Long cur : drawPos) {
					int yy = yStart + (int) ((cur - fromBP) / bpPerPixel);
					if (yy - curY < 100)
						continue;
					curY = yy;
					g.drawString("" + cur, curX + 5, yy);
					g.drawLine(curX - 10, yy, curX + 5, yy);
				}
				curX += 30;
				g.setFont(theFont);
				return curX;
			}

			private int drawPseudoColorTrack(List<MillionBasepairBox> mbpbl, Covariate covariate, long fromBP, int bpPerPixel, int contentHeight, Graphics g, int curX, Method getter2, Method getter1,
					int yStart, Method getter3, int width) throws IllegalAccessException, InvocationTargetException, IllegalArgumentException, SecurityException, NoSuchFieldException,
					NoSuchMethodException {
				System.out.println(getter1.getName() + " -> " + getter2.getName());
				Map<MillionBasepairBox, Double> box2value = new HashMap<MillionBasepairBox, Double>();
				for (MillionBasepairBox cur : mbpbl) {
					Set<?> invoke = (Set<?>) getter1.invoke(cur);
					for (Object curi : invoke) {
						int cov = (Integer) getter3.invoke(curi);
						if (cov != covariate.getId())
							continue;
						Object invoke2 = getter2.invoke(curi);
						double value = 0;
						if (invoke2 instanceof Integer)
							value = (Integer) invoke2;
						else
							value = (Double) invoke2;
						box2value.put(cur, value);
					}
				}

				double minVal = Double.MAX_VALUE;
				double maxVal = Double.MIN_VALUE;
				for (MillionBasepairBox cur : mbpbl) {
					if (!box2value.containsKey(cur))
						continue;
					double value = box2value.get(cur);
					minVal = Math.min(minVal, value);
					maxVal = Math.max(maxVal, value);
				}

				g.setColor(Color.BLACK);
				Font theFont = g.getFont();
				AffineTransform fontAT = new AffineTransform();
				fontAT.rotate(-Math.PI / 2.0);
				g.setFont(theFont.deriveFont(fontAT));
				g.drawString(new DecimalFormat("#.#").format(minVal), curX + 12, contentHeight);
				fontAT = new AffineTransform();
				fontAT.rotate(Math.PI / 2.0);
				g.setFont(theFont.deriveFont(fontAT));
				g.drawString(new DecimalFormat("#.#").format(maxVal), curX, contentHeight + 100);

				String trackTitle = getter1.getName().substring(getter1.getName().length() - 3) + "-" + getter2.getName().substring(3);
				g.drawString(trackTitle, curX, 0);
				g.setFont(theFont);

				for (MillionBasepairBox cur : mbpbl) {
					if (!box2value.containsKey(cur))
						continue;
					double value = box2value.get(cur);
					g.setColor(fakeColors[(int) Math.floor((value - minVal) / (maxVal - minVal) * 255.99)]);
					g.fillRect(curX, yStart + (int) ((cur.getFromBP() - fromBP) / bpPerPixel), width, (int) ((cur.getToBP() - cur.getFromBP()) / bpPerPixel));
				}

				for (int i = 0; i <= 4; i++) {
					double value = (maxVal - minVal) * (double) i / 4.0 + minVal;
					g.setColor(fakeColors[(int) Math.floor((value - minVal) / (maxVal - minVal) * 255.99)]);
					g.fillRect(curX, contentHeight + i * 20, width, 20);
				}

				return curX + width * 4 / 3;
			}
		}.run();

	}
}
