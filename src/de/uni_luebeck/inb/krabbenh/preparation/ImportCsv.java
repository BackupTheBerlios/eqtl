package de.uni_luebeck.inb.krabbenh.preparation;

import java.io.BufferedReader;
import java.io.FileInputStream;
import java.io.InputStreamReader;
import java.util.HashMap;
import java.util.Map;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.Covariate;
import de.uni_luebeck.inb.krabbenh.entities.ExpressionQTL;
import de.uni_luebeck.inb.krabbenh.entities.Locus;
import de.uni_luebeck.inb.krabbenh.entities.MarkerInterpolation;
import de.uni_luebeck.inb.krabbenh.entities.Gene;
import de.uni_luebeck.inb.krabbenh.helpers.RunInsideTransaction;
import edu.emory.mathcs.backport.java.util.Arrays;

public class ImportCsv {
	@SuppressWarnings("unchecked")
	public static void main(String[] args) {
		new RunInsideTransaction() {
			@Override
			public void work(Transaction transaction, Session session) throws Exception {
				session.createQuery("delete from MillionBasepairBox").executeUpdate();
				session.createQuery("delete from ExpressionQTL").executeUpdate();
				session.createQuery("delete from Covariate").executeUpdate();
				session.createQuery("delete from Locus").executeUpdate();
				session.createQuery("delete from Gene").executeUpdate();
				session.createQuery("delete from MarkerInterpolation").executeUpdate();
				session.flush();

				String line;
				BufferedReader read;

				MarkerInterpolation dummy = new MarkerInterpolation();
				dummy.setChromosome("DUMMY");
				session.persist(dummy);

				Map<String, Locus> name2locus = new HashMap<String, Locus>();
				read = new BufferedReader(new InputStreamReader(new FileInputStream("locus.txt")));
				while ((line = read.readLine()) != null) {
					String parts[] = line.split("\t");
					Locus locus = new Locus();
					locus.setName(parts[0]);
					locus.setChromosome(parts[1]);
					locus.setPosition(Double.valueOf(parts[2]));
					locus.setInterpolatedPosition(!locus.getName().startsWith("D"));
					locus.setMarkerInterpolation(dummy);
					session.persist(locus);
					name2locus.put(parts[0].toLowerCase(), locus);
				}

				Map<Integer, Gene> probeset2snip = new HashMap<Integer, Gene>();
				// 10723943 1 - 157453059 157490563 293144
				read = new BufferedReader(new InputStreamReader(new FileInputStream("BEARatChip.txt")));
				while ((line = read.readLine()) != null) {
					String parts[] = line.split("\t");
					if (parts[1].length() == 0) {
						System.out.println("skipping probeset_id " + parts[0]);
						continue;
					}
					Gene snip = new Gene();
					snip.setChromosome(parts[1]);
					snip.setPositiveStrand(parts[2].equals("+"));
					snip.setFromBp(Long.valueOf(parts[3]));
					snip.setToBp(Long.valueOf(parts[4]));
					snip.setEntrezId(Long.valueOf(parts[5]));
					session.persist(snip);
					probeset2snip.put(Integer.valueOf(parts[0]), snip);
				}

				session.flush();

				Map<String, Covariate> name2cov = new HashMap<String, Covariate>();
				// c10.loc36 10917594 5.91376543 sud_int,dpw_int
				read = new BufferedReader(new InputStreamReader(new FileInputStream("qtl.txt")));
				int counter = 0;
				while ((line = read.readLine()) != null) {
					if (counter++ % 1000 == 0) {
						System.out.println("counter: " + counter);
						session.flush();
					}
					String parts[] = line.split("\t");
					ExpressionQTL eqtl = new ExpressionQTL();
					Locus locus = name2locus.get(parts[0].toLowerCase());
					if (locus == null) {
						System.err.println("skipping qtl because of missing locus. locus " + parts[0] + " trait " + parts[1]);
						continue;
					}
					eqtl.setLocus(locus);
					Gene snip = probeset2snip.get(Integer.valueOf(parts[1]));
					if (snip == null) {
						System.err.println("skipping qtl because of missing snip. locus " + parts[0] + " trait " + parts[1]);
						continue;
					}
					eqtl.setGene(snip);
					String covName = parts.length > 3 ? parts[3] : "";
					if (! name2cov.containsKey(covName)) {
						Covariate covariate = new Covariate();
						if (covName.length() > 0) {
							if (covName.contains(","))
								covariate.getNames().addAll(Arrays.asList(covName.split(",")));
							else
								covariate.getNames().add(covName);
						}
						session.persist(covariate);
						name2cov.put(covName, covariate);
					}
					eqtl.setCovariate(name2cov.get(covName));
					eqtl.setLOD(Double.valueOf(parts[2]));
					session.persist(eqtl);
				}

				session.flush();

			}
		}.run();

	}

}
