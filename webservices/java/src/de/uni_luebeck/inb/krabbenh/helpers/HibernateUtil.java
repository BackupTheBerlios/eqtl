package de.uni_luebeck.inb.krabbenh.helpers;

import org.hibernate.SessionFactory;
import org.hibernate.cfg.AnnotationConfiguration;

import de.uni_luebeck.inb.krabbenh.entities.Covariate;
import de.uni_luebeck.inb.krabbenh.entities.EnsemblMarker;
import de.uni_luebeck.inb.krabbenh.entities.ExpressionQTL;
import de.uni_luebeck.inb.krabbenh.entities.Locus;
import de.uni_luebeck.inb.krabbenh.entities.MarkerInterpolation;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox;
import de.uni_luebeck.inb.krabbenh.entities.MillionBasepairBox_Statistics;
import de.uni_luebeck.inb.krabbenh.entities.Gene;

public class HibernateUtil {

    private static final SessionFactory sessionFactory;

    static {
        try {
            // Create the SessionFactory from hibernate.cfg.xml
            sessionFactory = new AnnotationConfiguration().configure()
            .addAnnotatedClass(Covariate.class)
             .addAnnotatedClass(MarkerInterpolation.class)
             .addAnnotatedClass(Locus.class)
             .addAnnotatedClass(Gene.class)
            .addAnnotatedClass(ExpressionQTL.class)
            .addAnnotatedClass(MillionBasepairBox_Statistics.class)
            .addAnnotatedClass(MillionBasepairBox.class)
            .addAnnotatedClass(EnsemblMarker.class)
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
