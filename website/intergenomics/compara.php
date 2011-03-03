<?php

/**
STARTOFDOCUMENTATION

=pod

=head1 NAME

compara.php - 

=head1 SYNOPSIS

=head1 DESCRIPTION

=head1 AUTHOR

Michael Brehler <brehler@informatik.uni-luebeck.de>,
Georg Zeplin <zeplin@informatik.uni-luebeck.de>,

=head1 COPYRIGHT

University of LE<uuml>beck, Germany, 2011

=cut

ENDOFDOCUMENTATION
*/

include 'html/header.html';

require_once 'qtl_functions.php';
require_once 'db_functions.php';
require_once 'utils.php';

//measure running-time
$start = tic();

$args = $_GET;

$compara = connectToCompara(3306);

$proj_str = 'project';
if(isset($args[$proj_str])&&(count($args[$proj_str])==2)){
	connectToQtlDBs($args[$proj_str]);
}else{
	fatal_error('No project found or wrong number of projects!');
}





$reg_str = 'regions';
$chr2reg = array();
//TODO: update compara_array with genome_db_ID
$species2genome_db_ids = array("Rattus norvegicus" => 3,"Mus musculus"=>57);
$genome_ids2dbs = array(57 => 'eqtl_rostock_eae', 3 =>'eqtl_stockholm_eae_logplier');

if(isset($args[$reg_str])){
	if(isset($args['species'])){
		if(isset($args['confidence_int'])){
			$confidence_int = $args['confidence_int'];
		}else{
			fatal_error('No confidence interval found!');
		}
		$regs = $args[$reg_str];
		foreach ($regs as $reg){
			$pos = strpos ($reg, ":");
			$regionChr[] = substr($reg,0,$pos);
			$regionBP = substr($reg,$pos+1);
			//build substrings with start and ending of region
			$pos = strpos ($regionBP, "-");
			//start of region in bp
			$regionStart[] = substr($regionBP,0,$pos);
			//end of region in bp
			$regionEnd[] = substr($regionBP,$pos+1);
		}
		//initialize array for mapping groupnumbers to regions
		$group2region = array();
		$group2region2 = array();
		if($args['species'] == 'Rattus norvegicus'){
			//load rat informations (database and so on...)
			$genome_db_ids = array(3,57);
			$species_names = array("Rattus norvegicus","Mus musculus");
			$database1 = 'eqtl_stockholm_eae_logplier';
			$database2 = 'eqtl_rostock_eae';
			//put the region start and region end in arrays
			for ($i = 0; $i < sizeof($regionChr); $i++) {
				//bp to cM for start of region
				$intervalStart[$i] = bp2cM($regionChr[$i], (int)$regionStart[$i],'Rattus norvegicus');
				//bp to cM for end of region
				$intervalEnd[$i] = bp2cM($regionChr[$i], (int)$regionEnd[$i],'Rattus norvegicus');
			}
			$chromosomsEx1 = $regionChr;
			$ex1 =  get_loci_from_sql($database1, $qtldb, 'userinterval', $chromosomsEx1, $confidence_int, $group2region, $intervalStart, $intervalEnd);
			if (!empty($ex1)) {
				// converts $ex1 in 2 arrays: $groups1 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end', 'Chr') $mapEx1 = index -> (locus,groupNr)
				list($groups1, $mapEx1) = $ex1;
			}else {
				echo '<INPUT TYPE=BUTTON VALUE="back" onClick="history.back()">';
				echo '<br />';
				fatal_error('nothing found for the given region(-s)');
			}
			// generates an arrays with index -> locinames
			$loci_ex1 = array_map('current',$mapEx1);
			$chromosomsEx2 = getChromosoms($compara, 57);
			$ex2 =  get_loci_from_sql($database2, $qtldb, 'wholeGenome', $chromosomsEx2, $confidence_int, $group2region2);
			// converts $ex2 in 2 arrays: $groups2 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end') $mapEx2 = index -> (locus,groupNr)
			list($groups2, $mapEx2) = $ex2;
			// generates an arrays with index -> locinames
			$loci_ex2 = array_map('current',$mapEx2);
				
				

		}elseif ($args['species'] == 'Mus musculus'){
			//load mouse informations (database and so on...)
			$genome_db_ids = array(57,3);
			$species_names = array("Mus musculus","Rattus norvegicus");
			$database1 = 'eqtl_rostock_eae';
			$database2 = 'eqtl_stockholm_eae_logplier';
			//put the region start and region end in arrays
			for ($i = 0; $i < sizeof($regionChr); $i++) {
				//bp to cM for start of region
				$intervalStart[$i] = bp2cM($regionChr[$i], (int)$regionStart[$i],'Mus musculus');
				//bp to cM for end of region
				$intervalEnd[$i] = bp2cM($regionChr[$i], (int)$regionEnd[$i],'Mus musculus');
			}
			$chromosomsEx1 = $regionChr;
			$ex1 =  get_loci_from_sql($database1, $qtldb, 'userinterval', $chromosomsEx1, $confidence_int, $group2region, $intervalStart, $intervalEnd);
			if (!empty($ex1)) {
				// converts $ex1 in 2 arrays: $groups1 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end', 'Chr') $mapEx1 = index -> (locus,groupNr)
				list($groups1, $mapEx1) = $ex1;
			}else {
				echo '<INPUT TYPE=BUTTON VALUE="back" onClick="history.back()">';
				echo '<br />';
				fatal_error('nothing found for the given region(-s)');
			}
			// generates an arrays with index -> locinames
			$loci_ex1 = array_map('current',$mapEx1);
			$chromosomsEx2 = getChromosoms($compara, 3);
			$ex2 =  get_loci_from_sql($database2, $qtldb, 'wholeGenome', $chromosomsEx2, $confidence_int, $group2region2);
			// converts $ex2 in 2 arrays: $groups2 = groupnr -> ('loci' -> lociOfGroup, 'start', 'end') $mapEx2 = index -> (locus,groupNr)
			list($groups2, $mapEx2) = $ex2;
			// generates an arrays with index -> locinames
			$loci_ex2 = array_map('current',$mapEx2);

		}else {
			fatal_error('wrong speciesname');
		}
	}else{
		fatal_error('no speciesname');
	}
}else{
	fatal_error('BUGS!');
}
// TODO: multiple regions on one chromosome

// SYNTENY
$dbs = array($database1,$database2);
$groupSynteny_ex12ex2 = getSyntenyGroups($qtldb,$compara,$groups1,$groups2,$species_names,$genome_db_ids,$dbs);

// display -----------------------
include 'display_table.php';
toc($start,'everything');
include 'html/footer.html';
?>
