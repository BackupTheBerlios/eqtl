/*
 * first, mark all loci whose name starts with D as not-interpolated. set all markers to dummy interpolation range.
 * then, delete all marker interpolations. for each chromosome walk marker positions in ascending order and create ranges.
 * do not store ranges for which we dont have a matching ensembl start or end marker.
 * these are regions that are outside our marker range and thus can only be incorrect.
 * now that every loci has its marker interpolation linked to ensembl, 
 * calculate BP by linear interpolation using the ensembl data
 */

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
				MarkerInterpolation dummy = (MarkerInterpolation) session.createQuery("from MarkerInterpolation where chromosome='DUMMY'").uniqueResult();
				List<?> loci = session.createQuery("from Locus").list();
				for (Object locuso : loci) {
					Locus locus = (Locus) locuso;
					locus.setInterpolatedPosition(!locus.getName().startsWith("D"));
					locus.setMarkerInterpolation(dummy);
				}
				session.flush();
				session.createQuery("delete from MarkerInterpolation where id!=:id ").setParameter("id", dummy.getId()).executeUpdate();
				session.flush();

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
						EnsemblMarker markerFrom = getEnsemblMarkerForPosition(session, chromosome, curpos);
						EnsemblMarker markerTo = getEnsemblMarkerForPosition(session, chromosome, position);
						if (markerFrom != null && markerTo != null) {
							interpolation.setInterpolatedFromBP(markerFrom.getPositionBP());
							interpolation.setInterpolatedToBP(markerTo.getPositionBP());
							session.persist(interpolation);
						}
						curpos = position;
					}
				}

				session.flush();

				org.hibernate.Query query = session.createQuery("from MarkerInterpolation where chromosome=:chr and interpolatedFrom <= :pos and interpolatedTo > :pos");
				for (Object locuso : loci) {
					Locus locus = (Locus) locuso;
					List<?> inter = query.setParameter("chr", locus.getChromosome()).setParameter("pos", locus.getPosition()).list();
					if (inter.size() == 0 && !locus.isInterpolatedPosition()) {
						inter = session.createQuery("from MarkerInterpolation where chromosome=:chr and (interpolatedFrom=:pos or interpolatedTo=:pos)").setParameter("chr", locus.getChromosome())
								.setParameter("pos", locus.getPosition()).list();
					}
					if (inter.size() > 0)
						locus.setMarkerInterpolation((MarkerInterpolation) inter.get(0));
					else
						locus.setMarkerInterpolation(dummy);
					locus.setPositionBP(locus.getMarkerInterpolation().getInterpolatedBpFor(locus.getPosition()));
				}

				session.flush();

			}
		}.run();
	}

	private static EnsemblMarker getEnsemblMarkerForPosition(Session session, String chromosome, double curpos) {
		Locus locus = (Locus) session.createQuery("from Locus where interpolatedPosition = false and chromosome=:chr and position>:pos1 and position<:pos2").setParameter("chr", chromosome)
				.setParameter("pos1", curpos - 0.001).setParameter("pos2", curpos + 0.001).uniqueResult();
		if (locus == null)
			return null;
		EnsemblMarker fromEnsembl = (EnsemblMarker) session.createQuery("from EnsemblMarker where name=:name").setParameter("name", locus.getName()).uniqueResult();
		return fromEnsembl;
	}

}
