package de.uni_luebeck.inb.krabbenh.preparation;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.EnsemblMarker;
import de.uni_luebeck.inb.krabbenh.entities.Locus;
import de.uni_luebeck.inb.krabbenh.entities.MarkerInterpolation;
import de.uni_luebeck.inb.krabbenh.helpers.RunInsideTransaction;

public class CalculateLocus {
	public static void main(String[] args) throws IOException {
		new RunInsideTransaction() {
			@Override
			public void work(Transaction transaction, Session session) throws Exception {
				List<?> chromosomes = session.createQuery("select chromosome from Locus group by chromosome").list();
				for (Object curo : chromosomes) {
					String chromosome = (String) curo;

					List<?> markero = session.createQuery("select position from Locus where interpolatedPosition = false and chromosome=:chr order by position asc").setParameter("chr", chromosome)
							.list();

					Double last = (Double) session.createQuery("select position from Locus where chromosome=:chr order by position desc").setParameter("chr", chromosome).setMaxResults(1)
							.uniqueResult();

					ArrayList<Double> marker = new ArrayList<Double>();
					for (Object curoo : markero)
						marker.add((Double) curoo);
					marker.add(last);

					double curpos = 0;
					for (Double position : marker) {
						if (Math.abs(curpos - position) < 0.001)
							continue;

						MarkerInterpolation interpolation = new MarkerInterpolation();
						interpolation.setChromosome(chromosome);
						interpolation.setInterpolatedFrom(curpos);
						interpolation.setInterpolatedTo(position);
						// NOTE: since the borders are real markers, we should
						// have ENSEMBL position info

						interpolation.setInterpolatedFromBP(getEnsemblMarkerForPosition(session, chromosome, curpos).getPositionBP());
						interpolation.setInterpolatedToBP(getEnsemblMarkerForPosition(session, chromosome, position).getPositionBP());
						session.persist(interpolation);
						curpos = position;
					}
				}

				session.flush();

				org.hibernate.Query query = session.createQuery("from MarkerInterpolation where chromosome=:chr and interpolatedFrom <= :pos and interpolatedTo > :pos");
				List<?> loci = session.createQuery("from Locus").list();
				for (Object locuso : loci) {
					Locus locus = (Locus) locuso;
					List<?> inter = query.setParameter("chr", locus.getChromosome()).setParameter("pos", locus.getPosition()).list();
					if (inter.size() == 0 && !locus.isInterpolatedPosition()) {
						inter = session.createQuery("from MarkerInterpolation where chromosome=:chr and (interpolatedFrom=:pos or interpolatedTo=:pos)").setParameter("chr", locus.getChromosome())
								.setParameter("pos", locus.getPosition()).list();
					}
					assert inter.size() == 1;
					if (inter.size() > 0)
						locus.setMarkerInterpolation((MarkerInterpolation) inter.get(0));
					locus.setPositionBP(locus.getMarkerInterpolation().getInterpolatedBpFor(locus.getPosition()));
				}

				session.flush();

			}
		}.run();
	}

	private static EnsemblMarker getEnsemblMarkerForPosition(Session session, String chromosome, double curpos) {
		Locus locus = (Locus) session.createQuery("from Locus where interpolatedPosition = false and chromosome=:chr and position=:pos").setParameter("chr", chromosome).setParameter("pos", curpos)
				.uniqueResult();
		EnsemblMarker fromEnsembl = (EnsemblMarker) session.createQuery("from EnsemblMarker where name=?").setParameter(1, locus.getName()).uniqueResult();
		return fromEnsembl;
	}

}
