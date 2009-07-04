package de.uni_luebeck.inb.krabbenh;

import java.io.FileWriter;
import java.io.IOException;
import java.util.List;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.Covariate;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_StatisticsAll;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_StatisticsCis;
import de.uni_luebeck.inb.krabbenh.helpers.RunInsideTransaction;

public class WriteMBpBox {
	public static void main(String[] args) throws IOException {
		new RunInsideTransaction() {
			@Override
			public void work(Transaction transaction, Session session) throws Exception {
				FileWriter writer = new FileWriter("MillionBasepairBox.txt");
				writer.write("id\t");
				writer.write("covariates\t");
				writer.write("chromosome\t");
				writer.write("fromBP\t");
				writer.write("toBP\t");
				writer.write("all_eqtl_count\t");
				writer.write("all_AVG_lod\t");
				writer.write("all_MIN_lod\t");
				writer.write("all_MAX_lod\t");
				writer.write("all_STDDEV_lod\t");
				writer.write("frequency_samechromosome\t");
				writer.write("cis_eqtl_count\t");
				writer.write("cis_AVG_lod\t");
				writer.write("cis_MIN_lod\t");
				writer.write("cis_MAX_lod\t");
				writer.write("cis_STDDEV_lod\t");
				writer.write("cis_AVG_distance\t");
				writer.write("cis_MIN_distance\t");
				writer.write("cis_MAX_distance\t");
				writer.write("cis_STDDEV_distance\n");
				List<?> covariates = session.createQuery("from Covariate").list();
				List<?> mbpbl = session.createQuery("from MillionBasepairBox").list();
				for (Object object : mbpbl) {
					MillionBasepairBox box = (MillionBasepairBox) object;
					for (Object covo : covariates) {
						Covariate covariate = (Covariate) covo;

						writer.write(box.getId() + "\t");
						writer.write(covariate.getNames().toString() + "\t");
						writer.write(box.getChromosome() + "\t");
						writer.write(box.getFromBP() + "\t");
						writer.write(box.getToBP() + "\t");

						MillionBasepairBox_StatisticsAll statisticsAll = null;
						MillionBasepairBox_StatisticsCis statisticsCis = null;

						for (MillionBasepairBox_StatisticsAll cur : box.getStatisticsAll())
							if (cur.getCovariate().getId() == covariate.getId())
								statisticsAll = cur;

						for (MillionBasepairBox_StatisticsCis cur : box.getStatisticsCis())
							if (cur.getCovariate().getId() == covariate.getId())
								statisticsCis = cur;

						if (statisticsAll == null)
							writer.write("0\t0\t0\t0\t0\t0");
						else {
							writer.write(statisticsAll.getEqtlCount() + "\t");
							writer.write(statisticsAll.getLodAverage() + "\t");
							writer.write(statisticsAll.getLodMin() + "\t");
							writer.write(statisticsAll.getLodMax() + "\t");
							writer.write(statisticsAll.getLodStdDev() + "\t");
							writer.write(statisticsAll.getFrequencySameChromosome() + "\t");
						}
						if (statisticsCis == null)
							writer.write("0\t0\t0\t0\t0\t0\t0\t0\t0\n");
						else {
							writer.write(statisticsCis.getEqtlCount() + "\t");
							writer.write(statisticsCis.getLodAverage() + "\t");
							writer.write(statisticsCis.getLodMin() + "\t");
							writer.write(statisticsCis.getLodMax() + "\t");
							writer.write(statisticsCis.getLodStdDev() + "\t");
							writer.write(statisticsCis.getDistanceAverage() + "\t");
							writer.write(statisticsCis.getDistanceMin() + "\t");
							writer.write(statisticsCis.getDistanceMax() + "\t");
							writer.write(statisticsCis.getDistanceStdDev() + "\n");
						}
					}
				}
				writer.close();
			}
		}.run();

	}

}
