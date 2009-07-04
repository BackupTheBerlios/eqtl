package de.uni_luebeck.inb.krabbenh;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStreamReader;

import org.hibernate.Session;

import de.uni_luebeck.inb.krabbenh.entities.Mouse;
import de.uni_luebeck.inb.krabbenh.entities.MouseExpression;
import de.uni_luebeck.inb.krabbenh.entities.Snip;


public class ImportCsv {
	public static void main(String[] args) throws IOException {
		Session session = HibernateUtil.getSessionFactory().openSession();
		session.createQuery("delete from MouseExpression").executeUpdate();
		
		String line;
		BufferedReader read;

		File dir = new File("P:\\HIWI-play\\DATA\\expression");
		for (File cur : dir.listFiles()) {
			if (cur.getName().endsWith(".csv")) {
				read = new BufferedReader(new InputStreamReader(new FileInputStream(cur)));
				read.readLine();
				
				Integer snipID = Integer.valueOf(cur.getName().split("\\.")[0]);
				Snip snip = (Snip) session.get(Snip.class, snipID);
				if(snip == null) {
					snip = new Snip();
					snip.setId(snipID);
					snip.setAccession("DUMMY"+snipID);
					snip.setProbeSequence("DUMMY");
					snip.setStart(-1);
					snip.setSymbol("DUMMY"+snipID);
					snip.setDescription("DUMMY");
					session.save(snip);
				}
				
				while ((line = read.readLine()) != null) {
					MouseExpression exp = new MouseExpression();
					String parts[] = line.split(",");
					exp.setMouse((Mouse) session.get(Mouse.class, Integer.valueOf(parts[0])));
					exp.setSnip(snip);
					exp.setValue(Float.valueOf(parts[1]));
					session.save(exp);
				}
			}
		}
	}

}
