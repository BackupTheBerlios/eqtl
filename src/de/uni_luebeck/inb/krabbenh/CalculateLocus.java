package de.uni_luebeck.inb.krabbenh;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.Locus;
import de.uni_luebeck.inb.krabbenh.entities.MarkerInterpolation;

public class CalculateLocus {
	public static void main(String[] args) throws IOException {
		new RunInsideTransaction() {
			@Override
			public void work(Transaction transaction, Session session) throws Exception {
				Morgan2BP morgan2BP = new Morgan2BP();

				List<?> chromosomes = session.createQuery("select chromosome from Locus group by chromosome").list();
				for (Object curo : chromosomes) {
					String chromosome = (String) curo;

					List<?> markero = session.createQuery("select position from Locus where interpolatedPosition = false and chromosome=:chr order by position asc").setParameter("chr", chromosome)
							.list();

					Double last = (Double) session.createQuery("select position from Locus where chromosome=:chr order by position desc").setParameter("chr", chromosome).setMaxResults(1).uniqueResult();

					ArrayList<Double> marker = new ArrayList<Double>();
					for (Object curoo : markero)
						marker.add((Double) curoo);
					marker.add(last);

					double curpos = 0;
					for (Double position : marker) {
						if(Math.abs(curpos-position) < 0.001) continue;
						
						MarkerInterpolation interpolation = new MarkerInterpolation();
						interpolation.setChromosome(chromosome);
						interpolation.setInterpolatedFrom(curpos);
						interpolation.setInterpolatedTo(position);
						interpolation.setInterpolatedFromBP(morgan2BP.cM2bp(interpolation.getChromosome(), interpolation.getInterpolatedFrom()));
						interpolation.setInterpolatedToBP(morgan2BP.cM2bp(interpolation.getChromosome(), interpolation.getInterpolatedTo()));
						session.persist(interpolation);
						curpos = position;
					}
				}

				session.flush();

				org.hibernate.Query query = session.createQuery("from MarkerInterpolation where chromosome=:chr and interpolatedFrom <= :pos and interpolatedTo > :pos");
				List<?> loci = session.createQuery("from Locus").list();
				for (Object locuso : loci) {
					Locus locus = (Locus) locuso;
					locus.setPositionBP(morgan2BP.cM2bp(locus.getChromosome(), locus.getPosition()));
					List<?> inter = query.setParameter("chr", locus.getChromosome()).setParameter("pos", locus.getPosition()).list();
					if(inter.size() == 0 && !locus.isInterpolatedPosition()) {
						inter = session.createQuery("from MarkerInterpolation where chromosome=:chr and (interpolatedFrom=:pos or interpolatedTo=:pos)")
						.setParameter("chr", locus.getChromosome()).setParameter("pos", locus.getPosition()).list();
					}
					assert inter.size() == 1;
					if(inter.size() > 0)
						locus.setMarkerInterpolation((MarkerInterpolation) inter.get(0));
					session.persist(locus);
				}

				session.flush();

			}
		}.run();
	}

}
