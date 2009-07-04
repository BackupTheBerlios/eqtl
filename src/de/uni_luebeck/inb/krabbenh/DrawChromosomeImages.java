package de.uni_luebeck.inb.krabbenh;

import java.io.FileWriter;
import java.io.IOException;
import java.util.List;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox;
import de.uni_luebeck.inb.krabbenh.helpers.RunInsideTransaction;

public class DrawChromosomeImages {
	public static void main(String[] args) throws IOException {
		new RunInsideTransaction() {
			@Override
			public void work(Transaction transaction, Session session) throws Exception {
				FileWriter writer = new FileWriter("MillionBasepairBox.txt");
				writer.write("id\t");
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
				List<?> mbpbl = session.createQuery("from MillionBasepairBox as mbpb join fetch mbpb.statisticsAll join fetch mbpb.statisticsCis ").list();
				for (Object object : mbpbl) {
					MillionBasepairBox box = (MillionBasepairBox) object;
					writer.write(box.getId() + "\t");
					writer.write(box.getChromosome() + "\t");
					writer.write(box.getFromBP() + "\t");
					writer.write(box.getToBP() + "\t");
					if (box.getStatisticsAll() == null)
						writer.write("0\t0\t0\t0\t0\t0");
					else {
						writer.write(box.getStatisticsAll().getEqtlCount() + "\t");
						writer.write(box.getStatisticsAll().getLodAverage() + "\t");
						writer.write(box.getStatisticsAll().getLodMin() + "\t");
						writer.write(box.getStatisticsAll().getLodMax() + "\t");
						writer.write(box.getStatisticsAll().getLodStdDev() + "\t");
						writer.write(box.getStatisticsAll().getFrequencySameChromosome() + "\t");
					}
					if (box.getStatisticsCis() == null)
						writer.write("0\t0\t0\t0\t0\t0\t0\t0\t0\n");
					else {
						writer.write(box.getStatisticsCis().getEqtlCount() + "\t");
						writer.write(box.getStatisticsCis().getLodAverage() + "\t");
						writer.write(box.getStatisticsCis().getLodMin() + "\t");
						writer.write(box.getStatisticsCis().getLodMax() + "\t");
						writer.write(box.getStatisticsCis().getLodStdDev() + "\t");
						writer.write(box.getStatisticsCis().getDistanceAverage() + "\t");
						writer.write(box.getStatisticsCis().getDistanceMin() + "\t");
						writer.write(box.getStatisticsCis().getDistanceMax() + "\t");
						writer.write(box.getStatisticsCis().getDistanceStdDev() + "\n");
					}
				}
				writer.close();
			}
		}.run();

	}

}
