package de.uni_luebeck.inb.krabbenh.preparation;

import java.io.BufferedReader;
import java.io.FileInputStream;
import java.io.InputStreamReader;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.EnsemblMarker;
import de.uni_luebeck.inb.krabbenh.helpers.RunInsideTransaction;

public class ImportEnsemblCsv {
	public static void main(String[] args) {
		new RunInsideTransaction() {
			@Override
			public void work(Transaction transaction, Session session) throws Exception {
				session.createQuery("delete from EnsemblMarker").executeUpdate();
				session.flush();

				/*
				 * SELECT marker_synonym.name, marker_synonym.source,
				 * marker_feature.seq_region_start,
				 * marker_feature.seq_region_end FROM marker_feature INNER JOIN
				 * marker_synonym ON marker_feature.marker_id =
				 * marker_synonym.marker_id WHERE marker_synonym.source = 'RGD'
				 */

				String line;
				BufferedReader read;

				read = new BufferedReader(new InputStreamReader(new FileInputStream("ensemblMarker.txt")));
				while ((line = read.readLine()) != null) {
					String parts[] = line.split("\t");
					EnsemblMarker marker = new EnsemblMarker();
					marker.setName(parts[0]);
					long pos = Long.valueOf(parts[2]) / 2 + Long.valueOf(parts[3]) / 2;
					marker.setPositionBP(pos);
					session.persist(marker);
				}
				session.flush();

			}
		}.run();

	}

}
