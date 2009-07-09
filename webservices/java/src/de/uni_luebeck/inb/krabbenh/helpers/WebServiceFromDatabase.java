package de.uni_luebeck.inb.krabbenh.helpers;

import java.lang.reflect.Array;
import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;
import java.util.ArrayList;
import java.util.List;

import org.apache.log4j.Level;
import org.apache.log4j.Logger;
import org.hibernate.Session;
import org.hibernate.Transaction;
import org.hibernate.annotations.Entity;

public abstract class WebServiceFromDatabase<T extends Object> {
	@SuppressWarnings("unchecked")
	public T run() {
		Session sess = HibernateUtil.getSessionFactory().openSession();
		Logger.getRootLogger().setLevel(Level.INFO);

		Transaction tx = null;
		try {
			tx = sess.beginTransaction();
			T tmp = fetchDataToReturn(tx, sess);
			tmp = (T) copyIntoDTO(tmp);
			tx.commit();
			return tmp;
		} catch (Exception e) {
			if (tx != null)
				tx.rollback();
			throw new RuntimeException(e);
		} finally {
			sess.close();
		}
	}

	public abstract T fetchDataToReturn(org.hibernate.Transaction transaction, Session session) throws Exception;

	public Object copyIntoDTO(Object copyMe) throws InstantiationException, IllegalAccessException, IllegalArgumentException, InvocationTargetException {
		Class<? extends Object> copyclass = copyMe.getClass();
		if (copyclass.getAnnotation(javax.persistence.Entity.class) != null || copyclass.getAnnotation(Entity.class) != null) {
			Object cpy = copyclass.newInstance();
			for (Method setter : copyclass.getMethods()) {
				String name = setter.getName();
				if (!name.startsWith("set") || setter.getParameterTypes().length == 0)
					continue;

				String nameA = "is" + name.substring(3);
				String nameB = "get" + name.substring(3);
				for (Method getter : copyclass.getMethods()) {
					String name2 = getter.getName();
					if (!(name2.equals(nameA) || name2.equals(nameB)))
						continue;

					if (getter.getAnnotation(IgnoreOnWebService.class) != null) {
						 // invoke getter on copy to initialize lazy collections
						getter.invoke(cpy);
						break;
					}

					setter.invoke(cpy, copyIntoDTO(getter.invoke(copyMe)));
					break;
				}
			}
			return cpy;
		}
		if (copyMe instanceof List<?>) {
			ArrayList<Object> returnList = new ArrayList<Object>();
			List<?> sourceList = (List<?>) copyMe;
			for (Object object : sourceList) {
				returnList.add(copyIntoDTO(object));
			}
			return returnList;
		}	
		if (copyclass.isArray()) {
			for (int i = 0; i < Array.getLength(copyMe); i++) {
				Array.set(copyMe, i, copyIntoDTO(Array.get(copyMe, i)));
			}
			return copyMe;
		}
		if(copyclass.getName().contains("hibernate"))
			throw new RuntimeException("you should copy class "+copyclass.getName()+" to DTO !");
		return copyMe;
	}
}
