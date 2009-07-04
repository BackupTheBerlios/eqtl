package de.uni_luebeck.inb.krabbenh;

import org.apache.log4j.Level;
import org.apache.log4j.Logger;
import org.hibernate.Session;
import org.hibernate.Transaction;

public abstract class RunInsideTransaction {
	public void run() {
		Session sess = HibernateUtil.getSessionFactory().openSession();
		Logger.getRootLogger().setLevel(Level.INFO);

		Transaction tx = null;
		try {
			tx = sess.beginTransaction();
			work(tx, sess);
			tx.commit();
		} catch (Exception e) {
			if (tx != null)
				tx.rollback();
			throw new RuntimeException(e);
		} finally {
			sess.close();
		}
	}

	public abstract void work(org.hibernate.Transaction transaction, Session session) throws Exception;
}
