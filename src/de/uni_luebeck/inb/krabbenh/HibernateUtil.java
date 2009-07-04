package de.uni_luebeck.inb.krabbenh;

import org.hibernate.SessionFactory;
import org.hibernate.cfg.AnnotationConfiguration;
import org.hibernate.cfg.Configuration;

import de.uni_luebeck.inb.krabbenh.entities.Mouse;
import de.uni_luebeck.inb.krabbenh.entities.MouseExpression;
import de.uni_luebeck.inb.krabbenh.entities.Snip;

public class HibernateUtil {

    private static final SessionFactory sessionFactory;

    static {
        try {
            // Create the SessionFactory from hibernate.cfg.xml
            sessionFactory = new AnnotationConfiguration().configure()
            .addAnnotatedClass(Mouse.class)
            .addAnnotatedClass(MouseExpression.class)
            .addAnnotatedClass(Snip.class)
            .buildSessionFactory();
        } catch (Throwable ex) {
            // Make sure you log the exception, as it might be swallowed
            System.err.println("Initial SessionFactory creation failed." + ex);
            throw new ExceptionInInitializerError(ex);
        }
    }

    public static SessionFactory getSessionFactory() {
        return sessionFactory;
    }

}
