package de.uni_luebeck.inb.krabbenh.webservice;

import java.util.ArrayList;
import java.util.List;
import java.util.Set;

import javax.jws.WebMethod;
import javax.jws.WebService;

import org.hibernate.Session;
import org.hibernate.Transaction;

import de.uni_luebeck.inb.krabbenh.entities.ExpressionQTL;
import de.uni_luebeck.inb.krabbenh.entities.Gene;
import de.uni_luebeck.inb.krabbenh.entities.Locus;
import de.uni_luebeck.inb.krabbenh.entities.MarkerInterpolation;
import de.uni_luebeck.inb.krabbenh.helpers.WebServiceFromDatabase;
import edu.emory.mathcs.backport.java.util.Arrays;

@WebService(name = "EntityServices", serviceName = "eQTL_EntityServices", targetNamespace = "http://krabbenh.inb.uni-luebeck.de")
public class EntityServices {
	public EntityServices() {
	}

	@SuppressWarnings("unchecked")
	private Integer[] groupOfEQTL(List<?> ll) {
		return ((List<Integer>) ll).toArray(new Integer[] {});
	}

	@WebMethod
	public Integer[] findExpressionQtlsForRegion(final String chromosome, final long fromBP, final long toBP) {
		return new WebServiceFromDatabase<Integer[]>() {
			@Override
			public Integer[] fetchDataToReturn(Transaction transaction, Session session) throws Exception {
				return groupOfEQTL(session.createQuery(
						"select id from ExpressionQTL where locus.id in " + "(select id from Locus where chromosome=:chr and positionBP >= :from and positionBP <= :to)" + " or gene.id in "
								+ "(select id from Gene where chromosome=:chr and toBP >= :from and fromBp <= :to) order by LOD").setParameter("chr", chromosome).setParameter("from", fromBP)
						.setParameter("to", toBP).setMaxResults(500).list());
			}
		}.run();
	}

	@WebMethod
	public MarkerInterpolation[] enumerateMarkerInterpolations() {
		return new WebServiceFromDatabase<MarkerInterpolation[]>() {
			@SuppressWarnings("unchecked")
			@Override
			public MarkerInterpolation[] fetchDataToReturn(Transaction transaction, Session session) throws Exception {
				return ((List<MarkerInterpolation>) session.createQuery("from MarkerInterpolation").list()).toArray(new MarkerInterpolation[] {});
			}
		}.run();
	}

	@WebMethod
	public Integer[] getExpressionQtlsContainedInMarkerInterpolation(final Integer markerInterpolationId) {
		return new WebServiceFromDatabase<Integer[]>() {
			@Override
			public Integer[] fetchDataToReturn(Transaction transaction, Session session) throws Exception {
				Set<ExpressionQTL> containedExpressionQTLs = ((MarkerInterpolation) session.load(MarkerInterpolation.class, markerInterpolationId)).getContainedExpressionQTLs();
				List<Integer> ret = new ArrayList<Integer>();
				for (ExpressionQTL addme : containedExpressionQTLs) {
					ret.add(addme.getId());
				}
				return groupOfEQTL(ret);
			}
		}.run();
	}

	@WebMethod
	public Locus[] getLociInRegion(final String chromosome, final long fromBP, final long toBP) {
		return new WebServiceFromDatabase<Locus[]>() {
			@SuppressWarnings("unchecked")
			@Override
			public Locus[] fetchDataToReturn(Transaction transaction, Session session) throws Exception {
				return ((List<Locus>) session.createQuery("from Locus where chromosome=:chr and positionBP >= :from and positionBP <= :to").setParameter("chr", chromosome)
						.setParameter("from", fromBP).setParameter("to", toBP).list()).toArray(new Locus[] {});
			}
		}.run();
	}

	@WebMethod
	public Gene[] getGenesInRegion(final String chromosome, final long fromBP, final long toBP) {
		return new WebServiceFromDatabase<Gene[]>() {
			@SuppressWarnings("unchecked")
			@Override
			public Gene[] fetchDataToReturn(Transaction transaction, Session session) throws Exception {
				return ((List<Gene>) session.createQuery("from Gene where chromosome=:chr and toBP >= :from and fromBP <= :to").setParameter("chr", chromosome).setParameter("from", fromBP)
						.setParameter("to", toBP).list()).toArray(new Gene[] {});
			}
		}.run();
	}

	@WebMethod
	public Integer[] getExpressionQtlsForLocusId(final Integer locusId) {
		return new WebServiceFromDatabase<Integer[]>() {
			@Override
			public Integer[] fetchDataToReturn(Transaction transaction, Session session) throws Exception {
				return groupOfEQTL(session.createQuery("select id from ExpressionQTL where locus.id=:id").setParameter("id", locusId).list());
			}
		}.run();
	}

	@WebMethod
	public Integer[] getExpressionQtlsForGeneId(final Integer geneId) {
		return new WebServiceFromDatabase<Integer[]>() {
			@Override
			public Integer[] fetchDataToReturn(Transaction transaction, Session session) throws Exception {
				return groupOfEQTL(session.createQuery("select id from ExpressionQTL where gene.id=:id").setParameter("id", geneId).list());
			}
		}.run();
	}

	@SuppressWarnings("unchecked")
	private List<Integer> groupToList(Integer[] a) {
		return Arrays.asList(a);
	}

	@WebMethod
	public Integer[] groupOfExpressionQTL_Or(Integer[] groupA, Integer[] groupB) {
		List<Integer> rr = new ArrayList<Integer>();
		rr.addAll(groupToList(groupA));
		rr.addAll(groupToList(groupB));
		return groupOfEQTL(rr);
	}

	@WebMethod
	public Integer[] groupOfExpressionQTL_And(Integer[] groupA, Integer[] groupB) {
		List<Integer> rr = new ArrayList<Integer>();
		rr.addAll(groupToList(groupA));
		rr.retainAll(groupToList(groupB));
		return groupOfEQTL(rr);
	}

	@WebMethod
	public Integer[] groupOfExpressionQTL_Remove(Integer[] groupToModify, Integer[] removeThese) {
		List<Integer> rr = new ArrayList<Integer>();
		rr.addAll(groupToList(groupToModify));
		rr.removeAll(groupToList(removeThese));
		return groupOfEQTL(rr);
	}

	@WebMethod
	public ExpressionQTL[] groupOfExpressionQTL_ConvertToListOfDataSets(final Integer[] convertMe) {
		return new WebServiceFromDatabase<ExpressionQTL[]>() {
			@SuppressWarnings("unchecked")
			@Override
			public ExpressionQTL[] fetchDataToReturn(Transaction transaction, Session session) throws Exception {
				if (convertMe.length == 0)
					return new ExpressionQTL[] {};
				return ((List<ExpressionQTL>) session.createQuery("from ExpressionQTL where id in (:idd) ").setParameterList("idd", convertMe).list()).toArray(new ExpressionQTL[] {});
			}
		}.run();
	}

	@WebMethod
	public Long[] groupOfExpressionQTL_ConvertToListOfEntrezGeneIds(final Integer[] convertMe) {
		return new WebServiceFromDatabase<Long[]>() {
			@SuppressWarnings("unchecked")
			@Override
			public Long[] fetchDataToReturn(Transaction transaction, Session session) throws Exception {
				if (convertMe.length == 0)
					return new Long[] {};
				return ((List<Long>) session.createQuery("select gene.entrezId from ExpressionQTL where id in (:idd) ").setParameterList("idd", convertMe).list()).toArray(new Long[] {});
			}
		}.run();
	}
}