/**
 * $Id: GZ 2010-12-19 exp$
 * Used by regions.php.
 * 
 * TODO: The functions here, shall not se the Get-comand but rather modify the DOM directly.
 */

/**
 * Returns a get string with all selected regions contained in an associative array regions[]
 */
function prepareGetString(site) {
	// prepare species
	var species_str = "species";
	var species_select = document.getElementsByName(species_str)[0];
	var expr = /(\w.+)\s(\w.+)/;
	expr.exec(species_select[species_select.selectedIndex].value);
	if (site == null) {
		site = "regions.php";
	}
	var str = site + "?" + species_str + "=" + RegExp.$1 + "+" + RegExp.$2;

	// add the selected region to the regions textfield
	// fetch all regions textfields into a get-string
	var ele = document.getElementsByName("regions[]");
	for ( var i = 0; i < ele.length; i++) {
		var region = ele[i];
		if (region.value != '') {
			str += "&regions[]=" + region.id.substring(0, region.id.indexOf("-"))
					+ ":" + region.value;
		}
	}
	str += '&confidence_int='+document.getElementById('conf').value;
	return str;
}
/**
 * Add the selected region to the regions textfield and call the get method with
 * the new region, rather than setting the field content directly...
 */
function addRegion(chr) {
	var str = "&regions[]=" + chr + ":"
			+ document.getElementById("start" + chr).value + "-"
			+ document.getElementById("end" + chr).value;
	window.location.href = prepareGetString() + str;
}

/**
 * delete the selected region from the regions textfield and call the get method
 * with the old region, rather than setting the field content directly...
 */
function deleteRegion(chr) {
	document.getElementById(chr).removeAttribute("name");
	window.location.href = prepareGetString();
}

function submit_page(target) {
	if(target=='overview'){
		window.location.href = prepareGetString("compara.php");
	}else{
		window.location.href = prepareGetString("display_all.php");
	}
	
}
