package de.uni_luebeck.inb.krabbenh.preparation;

import java.io.IOException;

import org.hibernate.Query;
import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.helpers.RunInsideTransaction;

public class CalculateMBpBoxStatistics {

	public static void main(String[] args) throws IOException {
		new RunInsideTransaction() {
			@Override
			public void work(Transaction transaction, Session session) throws Exception {
				String sqlAll = "SELECT nextval ('hajo_eqtl.hibernate_sequence'), millionbasepairbox_expressionqtl.millionbasepairbox_id, "
						+ "count(expressionqtl.id) AS eqtlcount, "
						+ "sum(expressionqtl.lod) AS sum_lod, "
						+ "avg(expressionqtl.lod) AS avg_lod, "
						+ "min(expressionqtl.lod) AS min_lod, "
						+ "max(expressionqtl.lod) AS max_lod, "
						+ "COALESCE(stddev(expressionqtl.lod),0) AS stddev_lod, "
						+ "expressionqtl.covariate_id, "
						+ "0,0,0,0,0,0,0,0,0,0,0 "
						+ "FROM (hajo_eqtl.expressionqtl as expressionqtl JOIN hajo_eqtl.millionbasepairbox_expressionqtl as millionbasepairbox_expressionqtl ON ((expressionqtl.id = millionbasepairbox_expressionqtl.containedexpressionqtls_id))) "
						+ "GROUP BY millionbasepairbox_expressionqtl.millionbasepairbox_id, expressionqtl.covariate_id ";
				Query queryAll = session.createSQLQuery("INSERT INTO hajo_eqtl.millionbasepairbox_statistics "
						+ "(id,millionBasepairBox_id,alleqtlcount,alllodsum,alllodaverage,alllodmin,alllodmax,alllodstddev,covariate_id,"
						+ "ciseqtlcount,cislodsum,cislodaverage,cislodmin,cislodmax,cislodstddev,cisdistanceaverage,cisdistancemin," + "cisdistancemax,cisdistancestddev,frequencysamechromosome) "
						+ sqlAll);
				String sqlCis = "SELECT "
						+ "count(expressionqtl.id) AS eqtlcount, "
						+ "sum(expressionqtl.lod) AS sum_lod, "
						+ "avg(expressionqtl.lod) AS avg_lod, "
						+ "min(expressionqtl.lod) AS min_lod, "
						+ "max(expressionqtl.lod) AS max_lod, "
						+ "COALESCE(stddev(expressionqtl.lod),0) AS stddev_lod, "
						+ "avg(expressionqtl.distancebp) AS avg_distancebp, "
						+ "min(expressionqtl.distancebp) AS min_distancebp, "
						+ "max(expressionqtl.distancebp) AS max_distancebp, "
						+ "COALESCE(stddev(expressionqtl.distancebp),0) AS stddev_distancebp, "
						+ "expressionqtl.covariate_id as covariate_id, "
						+ "millionbasepairbox_expressionqtl.millionbasepairbox_id as millionbasepairbox_id "
						+ "FROM (hajo_eqtl.expressionqtl as expressionqtl JOIN hajo_eqtl.millionbasepairbox_expressionqtl as millionbasepairbox_expressionqtl ON ((expressionqtl.id = millionbasepairbox_expressionqtl.containedexpressionqtls_id))) "
						+ "WHERE expressionqtl.samechromosome=true  " + "GROUP BY millionbasepairbox_expressionqtl.millionbasepairbox_id, expressionqtl.covariate_id ";
				Query queryCis = session.createSQLQuery("UPDATE hajo_eqtl.millionbasepairbox_statistics as statis SET " + "ciseqtlcount = eqtlcount,cislodsum=sum_lod,cislodaverage=avg_lod,"
						+ "cislodmin=min_lod,cislodmax=max_lod,cislodstddev=stddev_lod," + "cisdistanceaverage=avg_distancebp,cisdistancemin=min_distancebp,"
						+ "cisdistancemax=max_distancebp,cisdistancestddev=stddev_distancebp FROM (" + sqlCis
						+ ") AS source WHERE source.millionbasepairbox_id=statis.millionbasepairbox_id and source.covariate_id=statis.covariate_id");

				session.createQuery("delete from MillionBasepairBox_Statistics").executeUpdate();
				queryAll.executeUpdate();
				queryCis.executeUpdate();
				session.createQuery("UPDATE hajo_eqtl.millionbasepairbox_statistics set frequencysamechromosome = ciseqtlcount / (alleqtlcount + ciseqtlcount)").executeUpdate();
			}
		}.run();
	}
}
