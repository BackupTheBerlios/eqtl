package de.uni_luebeck.inb.krabbenh.preparation;

import java.io.IOException;
import java.util.List;

import org.hibernate.Query;
import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.ExpressionQTL;
import de.uni_luebeck.inb.krabbenh.helpers.RunInsideTransaction;

public class CalculateEqtlDist {
	public static void main(String[] args) throws IOException {
		new CalculateEqtlDist().ddsd();
	}

	boolean run = true;
	int curBlock = 0;

	private void ddsd() {
		do {
			new RunInsideTransaction() {
				@Override
				public void work(Transaction transaction, Session session) throws Exception {

					Query createQuery = session.createQuery("from ExpressionQTL as e left join fetch e.snip left join fetch e.locus");
					List<?> l;
					createQuery.setFirstResult(curBlock * 1000);
					createQuery.setMaxResults(1000);
					l = createQuery.list();
					for (Object curo : l) {
						ExpressionQTL eqtl = (ExpressionQTL) curo;
						eqtl.setSameChromosome(eqtl.getLocus().getChromosome().equals(eqtl.getSnip().getChromosome()));
						if (eqtl.isSameChromosome()) {
							long dstA = eqtl.getLocus().getPositionBP() - eqtl.getSnip().getFromBp();
							long dstB = eqtl.getLocus().getPositionBP() - eqtl.getSnip().getToBp();
							eqtl.setDistanceBP(Math.min(Math.abs(dstA), Math.abs(dstB)));
						} else {
							eqtl.setDistanceBP(Long.MAX_VALUE);
						}
						session.persist(eqtl);
					}
					session.flush();
					curBlock++;
					if (l.size() == 0)
						run = false;
				}
			}.run();
		} while (run);
	}
}
