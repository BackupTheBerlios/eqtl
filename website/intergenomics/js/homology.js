function trim (zeichenkette) {
  // Erst führende, dann Abschließende Whitespaces entfernen
  // und das Ergebnis dieser Operationen zurückliefern
  return zeichenkette.replace (/^\s+/, '').replace (/\s+$/, '');
}

function refresh(checkBox) {
	var argsDiv = untRe.document.getElementById('refargs');
	var args = trim(argsDiv.firstChild.nodeValue);

	var hide;
	if (checkBox.id == "check1") {
		if (checkBox.checked) {
			if (untRe.document.getElementById('check2').checked) {
				hide = "3";
			} else {
				hide = "1";
			}
		} else {
			if (untRe.document.getElementById('check2').checked) {
				hide = "2";
			} else {
				hide = "0";
			}
		}
	} else {
		if (checkBox.checked) {
			if (untRe.document.getElementById('check1').checked) {
				hide = "3";
			} else {
				hide = "2";
			}
		} else {
			if (untRe.document.getElementById('check1').checked) {
				hide = "1";
			} else {
				hide = "0";
			}
		}
	}
	document.location.href = "../detailHomology.php?"+args+"&hide="+hide;
}