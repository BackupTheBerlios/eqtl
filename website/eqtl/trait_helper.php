
<?php

function add_sql_where_for_phen_analysis($trait_phen_analysis_id,$trait_phen_analysis_analysis,
                                           $trait_phen_analysis_value_min,$trait_phen_analysis_value_max, $tablename="trait_phen_analysis_value") {
	$addme="";
	if( !empty($trait_phen_analysis_value_min) or !empty($trait_phen_analysis_value_max))
	{
		if (!empty($trait_phen_analysis_value_min) and !empty($trait_phen_analysis_value_max)) {
			$addme .= " ($tablename.value >= $trait_phen_analysis_value_min AND $tablename.value <= $trait_phen_analysis_value_max)";
		}
		else if($trait_phen_analysis_value_min){
			$addme .= " $tablename.value >= $trait_phen_analysis_value_min";
		}
		else if($trait_phen_analysis_value_max){
			$addme .= " $tablename.value <= $trait_phen_analysis_value_max";
		}
		else {
		//	$addme .= "value <= 0.05";
		}
	}
	if (!empty($trait_phen_analysis_analysis) and empty($trait_phen_analysis_id)) {
		// FIXME: this needs an extra join and should not be used for now
		$addme .= (empty($addme)?"":" AND ") . "trait_phen_analysis.analysis='$trait_phen_analysis_analysis' ";
	}
	if (!empty($trait_phen_analysis_id)) {
		$addme .= (empty($addme)?"":" AND ") . "$tablename.trait_phen_analysis_id=$trait_phen_analysis_id ";
	}
	return $addme;
}

?>

