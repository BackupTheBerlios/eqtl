package de.uni_luebeck.inb.krabbenh;

import java.io.FileWriter;
import java.io.IOException;
import java.util.List;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.Covariate;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_Statistics;
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

						MillionBasepairBox_Statistics statistics = null;

						for (MillionBasepairBox_Statistics cur : box.getStatistics())
							if (cur.getCovariate().getId() == covariate.getId())
								statistics = cur;

						if (statistics == null)
							writer.write("0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\t0\n");
						else {
							writer.write(statistics.getAllEqtlCount() + "\t");
							writer.write(statistics.getAllLodAverage() + "\t");
							writer.write(statistics.getAllLodMin() + "\t");
							writer.write(statistics.getAllLodMax() + "\t");
							writer.write(statistics.getAllLodStdDev() + "\t");
							writer.write(statistics.getFrequencySameChromosome() + "\t");
							writer.write(statistics.getCisEqtlCount() + "\t");
							writer.write(statistics.getCisLodAverage() + "\t");
							writer.write(statistics.getCisLodMin() + "\t");
							writer.write(statistics.getCisLodMax() + "\t");
							writer.write(statistics.getCisLodStdDev() + "\t");
							writer.write(statistics.getCisDistanceAverage() + "\t");
							writer.write(statistics.getCisDistanceMin() + "\t");
							writer.write(statistics.getCisDistanceMax() + "\t");
							writer.write(statistics.getCisDistanceStdDev() + "\n");
						}
					}
				}
				writer.close();
			}
		}.run();

	}

}
