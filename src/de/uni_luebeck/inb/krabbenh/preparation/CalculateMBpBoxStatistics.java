package de.uni_luebeck.inb.krabbenh.preparation;

import java.io.IOException;
import java.math.BigDecimal;
import java.math.BigInteger;
import java.util.List;

import org.hibernate.Query;
import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.Covariate;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_Statistics;
import de.uni_luebeck.inb.krabbenh.helpers.RunInsideTransaction;

public class CalculateMBpBoxStatistics {

	public static void main(String[] args) throws IOException {
		new RunInsideTransaction() {
			@SuppressWarnings("unchecked")
			@Override
			public void work(Transaction transaction, Session session) throws Exception {
				Query queryAll = session
						.createSQLQuery("SELECT "
								+ "count(expressionqtl.id) AS eqtlcount, "
								+ "sum(expressionqtl.lod) AS sum_lod, "
								+ "avg(expressionqtl.lod) AS avg_lod, "
								+ "min(expressionqtl.lod) AS min_lod, "
								+ "max(expressionqtl.lod) AS max_lod, "
								+ "stddev(expressionqtl.lod) AS stddev_lod "
								+ "FROM (hajo_eqtl.expressionqtl as expressionqtl JOIN hajo_eqtl.millionbasepairbox_expressionqtl as millionbasepairbox_expressionqtl ON ((expressionqtl.id = millionbasepairbox_expressionqtl.containedexpressionqtls_id))) "
								+ "WHERE millionbasepairbox_expressionqtl.millionbasepairbox_id=:box and expressionqtl.covariate_id=:cov "
								+ "GROUP BY millionbasepairbox_expressionqtl.millionbasepairbox_id, expressionqtl.covariate_id ");
				Query queryCis = session
						.createSQLQuery("SELECT "
								+ "count(expressionqtl.id) AS eqtlcount, "
								+ "sum(expressionqtl.lod) AS sum_lod, "
								+ "avg(expressionqtl.lod) AS avg_lod, "
								+ "min(expressionqtl.lod) AS min_lod, "
								+ "max(expressionqtl.lod) AS max_lod, "
								+ "stddev(expressionqtl.lod) AS stddev_lod, "
								+ "avg(expressionqtl.distancebp) AS avg_distancebp, "
								+ "min(expressionqtl.distancebp) AS min_distancebp, "
								+ "max(expressionqtl.distancebp) AS max_distancebp, "
								+ "stddev(expressionqtl.distancebp) AS stddev_distancebp "
								+ "FROM (hajo_eqtl.expressionqtl as expressionqtl JOIN hajo_eqtl.millionbasepairbox_expressionqtl as millionbasepairbox_expressionqtl ON ((expressionqtl.id = millionbasepairbox_expressionqtl.containedexpressionqtls_id))) "
								+ "WHERE millionbasepairbox_expressionqtl.millionbasepairbox_id=:box and expressionqtl.covariate_id=:cov and expressionqtl.samechromosome=true "
								+ "GROUP BY millionbasepairbox_expressionqtl.millionbasepairbox_id, expressionqtl.covariate_id ");

				List<Covariate> covariateList = session.createQuery("from Covariate").list();
				List<MillionBasepairBox> mbpb = session.createQuery("from MillionBasepairBox").list();
				for (MillionBasepairBox millionBasepairBox : mbpb) {
					millionBasepairBox.getStatistics().clear();
					for (Covariate covariate : covariateList) {
						List<Object[]> statList = queryAll.setParameter("box", millionBasepairBox.getId()).setParameter("cov", covariate.getId()).list();
						if (statList.size() == 0)
							continue; // no all => no cis
						if (statList.size() != 1)
							throw new Exception("multiple stats for box & cov?");

						MillionBasepairBox_Statistics statistics = new MillionBasepairBox_Statistics();
						statistics.setCovariate(covariate);

						Object[] stats = statList.get(0);
						statistics.setAllEqtlCount(((BigInteger) stats[0]).intValue());
						statistics.setAllLodSum((Double) stats[1]);
						statistics.setAllLodAverage((Double) stats[2]);
						statistics.setAllLodMin((Double) stats[3]);
						statistics.setAllLodMax((Double) stats[4]);
						statistics.setAllLodStdDev(stats[5] == null ? 0.0 : (Double) stats[5]);

						statList = queryCis.setParameter("box", millionBasepairBox.getId()).setParameter("cov", covariate.getId()).list();
						if (statList.size() == 1) {
							stats = statList.get(0);
							statistics.setCisEqtlCount(((BigInteger) stats[0]).intValue());
							statistics.setCisLodSum((Double) stats[1]);
							statistics.setCisLodAverage((Double) stats[2]);
							statistics.setCisLodMin((Double) stats[3]);
							statistics.setCisLodMax((Double) stats[4]);
							statistics.setCisLodStdDev(stats[5] == null ? 0.0 : (Double) stats[5]);
							statistics.setCisDistanceAverage(((BigDecimal) stats[6]).doubleValue());
							statistics.setCisDistanceMin(((BigInteger) stats[7]).doubleValue());
							statistics.setCisDistanceMax(((BigInteger) stats[8]).doubleValue());
							statistics.setCisDistanceStdDev(stats[9] == null ? 0.0 : ((BigDecimal) stats[9]).doubleValue());
						}
						statistics.setFrequencySameChromosome(statistics.getCisEqtlCount() / (statistics.getCisEqtlCount() + statistics.getAllEqtlCount()));

						session.persist(statistics);
						millionBasepairBox.getStatistics().add(statistics);
					}
					session.persist(millionBasepairBox);
				}
			}
		}.run();
	}
}
