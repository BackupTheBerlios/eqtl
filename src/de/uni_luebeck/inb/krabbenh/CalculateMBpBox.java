package de.uni_luebeck.inb.krabbenh;

import java.io.IOException;
import java.util.List;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.ExpressionQTL;
import de.uni_luebeck.inb.krabbenh.entities.MarkerInterpolation;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox;

public class CalculateMBpBox {
	public static void main(String[] args) throws IOException {
		new CalculateMBpBox().dodo();
	}

	List<?> chromosomes;

	private void dodo() {
		new RunInsideTransaction() {
			@Override
			public void work(Transaction transaction, Session session) throws Exception {
				session.createQuery("delete from MillionBasepairBox").executeUpdate();
				chromosomes = session.createQuery("select chromosome from Locus group by chromosome").list();
			}
		}.run();

		for (Object curo : chromosomes) {
			final String chromosome = (String) curo;

			new RunInsideTransaction() {
				@Override
				public void work(Transaction transaction, Session session) throws Exception {
					List<?> markero = session.createQuery("from MarkerInterpolation where chromosome=:chr").setParameter("chr", chromosome).list();
					long fromBP = Long.MAX_VALUE;
					long toBP = Long.MIN_VALUE;
					for (Object curoo : markero) {
						MarkerInterpolation interpolation = (MarkerInterpolation) curoo;
						fromBP = Math.min(fromBP, interpolation.getInterpolatedFromBP());
						toBP = Math.max(toBP, interpolation.getInterpolatedToBP());
					}

					for (long curPos = fromBP; curPos < toBP; curPos += 1000 * 1000) {
						MillionBasepairBox box = new MillionBasepairBox();
						box.setChromosome(chromosome);
						box.setFromBP(curPos);
						box.setToBP(curPos + 1000 * 1000);

						List<?> eqtls = session
								.createQuery(
										"from ExpressionQTL where locus.id in (select id from Locus where chromosome=:chr and positionBP >= :from and positionBP <= :to) or snip.id in (select id from Snip where chromosome=:chr and toBP >= :from and fromBp <= :to)")
								.setParameter("chr", chromosome).setParameter("from", box.getFromBP()).setParameter("to", box.getToBP()).list();
						box.getContainedExpressionQTLs().clear();
						for (Object eqtl : eqtls) {
							box.getContainedExpressionQTLs().add((ExpressionQTL) eqtl);
						}
						session.persist(box);
					}
				}
			}.run();
		}
	}

}
