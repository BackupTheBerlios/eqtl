package de.uni_luebeck.inb.krabbenh;

import java.util.HashMap;
import java.util.Map;

public class Morgan2BP {
	Map<String, Map<Double, Integer>> map = new HashMap<String, Map<Double, Integer>>();

	public Morgan2BP() {
		Map<Double,Integer> tmp;
		tmp = new HashMap<Double,Integer>(); map.put("1", tmp);
		tmp.put(0.0,14458675);
		tmp.put(16.9151,45682398);
		tmp.put(26.2355,63200615);
		tmp.put(33.9378,73237443);
		tmp.put(48.7845,88606326);
		tmp.put(52.8919,101828574);
		tmp.put(62.2329,128897083);
		tmp.put(82.9595,168940224);
		tmp.put(92.0845,180365956);
		tmp.put(117.723,194167514);

		tmp = new HashMap<Double,Integer>(); map.put("10", tmp);
		tmp.put(0.0,20334520);
		tmp.put(23.3432,27014205);
		tmp.put(30.1939,70330845);
		tmp.put(33.3563,82543074);
		tmp.put(43.1582,92471203);
		tmp.put(55.3726,114056638);
		tmp.put(64.8295,124037692);

		tmp = new HashMap<Double,Integer>(); map.put("11", tmp);
		tmp.put(0.0,6825066);
		tmp.put(21.6967,35481820);
		tmp.put(29.068,53942957);
		tmp.put(39.8838,68334916);
		tmp.put(61.5844,94701240);
		tmp.put(90.6082,114951565);
		tmp.put(96.1401,117952858);

		tmp = new HashMap<Double,Integer>(); map.put("12", tmp);
		tmp.put(0.0,32506843);
		tmp.put(17.3838,69134481);
		tmp.put(21.6333,75849973);

		tmp = new HashMap<Double,Integer>(); map.put("13", tmp);
		tmp.put(16.1661,38304007);
		tmp.put(18.511,44631141);
		tmp.put(66.5352,92293864);
		tmp.put(75.8962,104474486);
		tmp.put(80.1275,108754548);

		tmp = new HashMap<Double,Integer>(); map.put("14", tmp);
		tmp.put(0.0,11027218);
		tmp.put(28.9167,46345237);
		tmp.put(35.1108,62136016);
		tmp.put(42.6689,66867222);
		tmp.put(46.4539,85577935);
		tmp.put(65.6016,100813192);

		tmp = new HashMap<Double,Integer>(); map.put("15", tmp);
		tmp.put(0.0,9519257);
		tmp.put(23.8203,52154107);
		tmp.put(39.6001,70229199);
		tmp.put(63.8274,90110231);
		tmp.put(75.3437,103580415);

		tmp = new HashMap<Double,Integer>(); map.put("16", tmp);
		tmp.put(0.0,5912022);
		tmp.put(17.38,31512146);
		tmp.put(31.7261,50248644);
		tmp.put(39.7737,66731287);
		tmp.put(48.0687,77897227);
		tmp.put(76.3153,92299378);

		tmp = new HashMap<Double,Integer>(); map.put("17", tmp);
		tmp.put(0.0,10706753);
		tmp.put(26.5507,48964895);
		tmp.put(46.4345,71582380);
		tmp.put(57.636,76815818);
		tmp.put(74.9695,91181999);

		tmp = new HashMap<Double,Integer>(); map.put("18", tmp);
		tmp.put(0.0,5045864);
		tmp.put(18.4582,32729254);
		tmp.put(36.5722,53376080);
		tmp.put(49.4926,56189627);
		tmp.put(71.6647,72253469);
		tmp.put(78.2964,85666025);

		tmp = new HashMap<Double,Integer>(); map.put("19", tmp);
		tmp.put(0.0,3433850);
		tmp.put(16.5781,21244655);
		tmp.put(26.0368,45099037);
		tmp.put(35.257,46704093);
		tmp.put(52.8836,59265997);

		tmp = new HashMap<Double,Integer>(); map.put("2", tmp);
		tmp.put(0.0,3803361);
		tmp.put(36.8798,36033830);
		tmp.put(53.9752,69320437);
		tmp.put(72.2734,106242123);
		tmp.put(73.4252,106528727);
		tmp.put(85.4672,119038763);
		//#tmp.put(112.053,152314238);
		//#tmp.put(99.5031,159043397);
		tmp.put(127.274,171903442);
		tmp.put(134.151,179508659);

		tmp = new HashMap<Double,Integer>(); map.put("3", tmp);
		tmp.put(8.961,26354725);
		tmp.put(18.8998,39008080);
		tmp.put(27.9861,56429293);
		tmp.put(37.1091,79460891);
		tmp.put(49.1106,96129251);
		tmp.put(57.5209,114600909);
		tmp.put(64.4711,130938761);
		tmp.put(82.1986,147122660);

		tmp = new HashMap<Double,Integer>(); map.put("4", tmp);
		tmp.put(0.0,13937423);
		tmp.put(32.7462,62456486);
		tmp.put(49.7708,94044415);
		tmp.put(68.893,123188413);
		tmp.put(81.3835,135808454);
		tmp.put(88.7574,147189020);
		tmp.put(99.0793,153482802);

		tmp = new HashMap<Double,Integer>(); map.put("5", tmp);
		tmp.put(0.0,4885537);
		tmp.put(17.7422,26800393);
		tmp.put(31.5794,31806952);
		//#tmp.put(0.0,39586617);
		tmp.put(20.9239,52147284);
		tmp.put(25.1256,73988917);
		tmp.put(57.7458,112994337);
		tmp.put(69.1054,123988955);
		tmp.put(80.1628,149942625);

		tmp = new HashMap<Double,Integer>(); map.put("6", tmp);
		tmp.put(0.0,4417770);
		tmp.put(17.0661,25203620);
		tmp.put(32.3823,53440673);
		tmp.put(44.7429,83989939);
		tmp.put(56.7674,104985571);
		tmp.put(62.5236,113342711);
		tmp.put(73.6561,129530089);
		tmp.put(88.3583,146589007);

		tmp = new HashMap<Double,Integer>(); map.put("7", tmp);
		tmp.put(0.0,24961658);
		tmp.put(12.7225,34136995);
		tmp.put(28.0843,45573352);
		tmp.put(77.8855,77442573);
		tmp.put(84.5478,94982613);
		tmp.put(119.328,108776468);
		tmp.put(102.76,124023652);
		tmp.put(133.04,125336831);
		tmp.put(147.693,139169413);

		tmp = new HashMap<Double,Integer>(); map.put("8", tmp);
		tmp.put(0.0,14101643);
		tmp.put(16.1974,32654494);
		tmp.put(33.0431,70201167);
		tmp.put(43.0757,93457798);
		tmp.put(64.9257,114150399);
		tmp.put(74.784,120663014);

		tmp = new HashMap<Double,Integer>(); map.put("9", tmp);
		tmp.put(0.0,23049483);
		tmp.put(9.603,37336441);
		tmp.put(13.62,43873980);
		tmp.put(25.8505,65700130);
		tmp.put(36.9428,88305906);
		tmp.put(42.2482,98718894);
		tmp.put(54.6,117424479);
		tmp.put(78.8685,121494315);

		tmp = new HashMap<Double,Integer>(); map.put("NT_057167", tmp);
		tmp.put(29.3427,806);

		tmp = new HashMap<Double,Integer>(); map.put("NT_110866", tmp);
		tmp.put(83.7165,121492);
	}
	

	public long cM2bp(String chromosome, double cm) {
		Map<Double,Integer> chrConv = map.get(chromosome);
		if(chrConv == null) return -1;
		Map.Entry<Double,Integer> less = null, more = null;
		for (Map.Entry<Double,Integer> cur : chrConv.entrySet()) {
			if(cur.getKey() <= cm && (less == null || less.getKey()<cur.getKey()) ) less = cur;
			if(cur.getKey() >= cm && (more == null || more.getKey()>cur.getKey()) ) more = cur;
		}
		if(more == null) {
			more = less; less = null;
			for (Map.Entry<Double,Integer> cur : chrConv.entrySet()) {
				if(cur.getKey() < more.getKey() && (less == null || less.getKey()<cur.getKey()) ) less = cur;
			}
		} else if(less == null) {
			less = more; more = null;
			for (Map.Entry<Double,Integer> cur : chrConv.entrySet()) {
				if(cur.getKey() > less.getKey() && (more == null || more.getKey()>cur.getKey()) ) more = cur;
			}
		}
		if(more == less) return more.getValue();
		double lerp = (cm - less.getKey()) / (more.getKey() - less.getKey());
		return Math.round(more.getValue()*lerp + less.getValue()*(1-lerp));
	}
}
