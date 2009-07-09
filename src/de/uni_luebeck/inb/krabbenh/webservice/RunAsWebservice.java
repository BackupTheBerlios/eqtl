package de.uni_luebeck.inb.krabbenh.webservice;


import org.apache.axis2.AxisFault;
import org.apache.axis2.context.ConfigurationContext;
import org.apache.axis2.context.ConfigurationContextFactory;
import org.apache.axis2.description.AxisService;
import org.apache.axis2.transport.http.SimpleHTTPServer;

public class RunAsWebservice {
	public static void main(String[] args) throws Exception {
		ConfigurationContext context = ConfigurationContextFactory.createDefaultConfigurationContext();
		
		addClassToWebservice(context, EntityServices.class);
		addClassToWebservice(context, GProfilerServices.class);

		new SimpleHTTPServer(context, 12348).start();
	}

	private static void addClassToWebservice(ConfigurationContext context, Class<?> class1) throws AxisFault {
		AxisService service = AxisService.createService(class1.getName(), context.getAxisConfiguration());
		context.getAxisConfiguration().addService(service);
	}

}
