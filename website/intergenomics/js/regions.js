/**
 * $Id: GZ 2010-12-19 exp$
 * Used by regions.php.
 * 
 * TODO: some functions here, shall not use the Get-comand but rather modify the DOM directly.
 */

/**
 * prepare species string for $_GET
 */
function prepareSpecies(site) {
	var project_str = "projects";
	// var project_selects = document.getElementsByName(project_str);
	var src_sel = document.getElementById(project_str + "0");
	var tar_sel = document.getElementById(project_str + "1");

	// indicates that something is wrong
	var error = "";

	if (src_sel.selectedIndex >= 0) {
		var src_val = src_sel[src_sel.selectedIndex].value;
	} else {
		error = "&err=src";
		var src_val = "NULL";
	}

	if (tar_sel.selectedIndex >= 0) {
		var tar_val = tar_sel[tar_sel.selectedIndex].value;
	} else {
		error = "&err=tar";
		var tar_val = "NULL";
	}

	if (site == null) {
		error = "";
		site = "regions.php";
	} else if (error != "") {// error
		site = "regions.php";
	}
	var proj_arg = project_str + "[]=";
	return site + "?" + proj_arg + src_val + "&" + proj_arg + tar_val + error;

}

/**
 * Returns a $_GET string with all selected regions contained in an array
 * regions[].
 */
function prepareGetString(site) {
	// prepare species
	var str = prepareSpecies(site);

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
	str += '&confidence_int=' + document.getElementById('conf').value;
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

/**
 * Called to go to the next page.
 * 
 * @param target
 *          modus, either 'overview' or all
 */
function submit_page(target) {
	if (target == 'overview') {
		window.location.href = prepareGetString("compara.php");
	} else if (target == 'all') {
		window.location.href = prepareGetString("display_all.php");
	} else {// this
		window.location.href = prepareSpecies();
	}

}
