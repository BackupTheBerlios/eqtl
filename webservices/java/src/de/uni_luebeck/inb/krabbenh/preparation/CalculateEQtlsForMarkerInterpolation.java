package de.uni_luebeck.inb.krabbenh.preparation;

import java.io.IOException;
import java.util.List;
import java.util.Set;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.ExpressionQTL;
import de.uni_luebeck.inb.krabbenh.entities.MarkerInterpolation;
import de.uni_luebeck.inb.krabbenh.helpers.RunInsideTransaction;

public class CalculateEQtlsForMarkerInterpolation {
	public static void main(String[] args) throws IOException {
		new CalculateEQtlsForMarkerInterpolation().dodo();
	}

	List<?> markerRanges;

	private void dodo() {
		new RunInsideTransaction() {
			@Override
			public void work(Transaction transaction, Session session) throws Exception {
				markerRanges = session.createQuery("from MarkerInterpolation").list();
			}
		}.run();

		for (final Object curo : markerRanges) {
			new RunInsideTransaction() {
				@Override
				public void work(Transaction transaction, Session session) throws Exception {
					MarkerInterpolation markerInterpolation = (MarkerInterpolation) session.merge(curo);
					List<?> eqtls = session
							.createQuery(
									"from ExpressionQTL where locus.id in (select id from Locus where chromosome=:chr and positionBP >= :from and positionBP <= :to) or gene.id in (select id from Gene where chromosome=:chr and toBP >= :from and fromBp <= :to)")
							.setParameter("chr", markerInterpolation.getChromosome()).setParameter("from", markerInterpolation.getInterpolatedFromBP()).setParameter("to",
									markerInterpolation.getInterpolatedToBP()).list();
					Set<ExpressionQTL> ll = markerInterpolation.getContainedExpressionQTLs();
					ll.clear();
					for (Object eqtl : eqtls) {
						ll.add((ExpressionQTL) eqtl);
					}
					session.flush();
				}
			}.run();
		}
	}

}
