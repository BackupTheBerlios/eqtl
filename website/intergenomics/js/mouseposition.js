/**
 * Get the absolute position of te mouse in the window.
 * Currently unused. 
 * 
 * @param e
 *          Mouse event carying location information
 * @returns p the position with p.top, the y-coordinate and p.left, the
 *          x-coordinate
 * @author Georg
 */
function mouse_pos(e) {
	if (!e)
		e = window.event;
	var body = (window.document.compatMode && window.document.compatMode == "CSS1Compat") ? window.document.documentElement
			: window.document.body;
	return {
		// Position in the document
		top : e.pageY ? e.pageY : e.clientY + body.scrollTop - body.clientTop,
		left : e.pageX ? e.pageX : e.clientX + body.scrollLeft - body.clientLeft
	};
}

function mouseMove(e) {
	var evt = e || window.event;
	return {
		// Position in the document
		top : evt.clientY,
		left : evt.clientX
	};
}

function getSpecies(str) {
	var species = document.getElementById(str).firstChild.nodeValue;
	var expr = /(\w.+)\s(\w.+)/;
	expr.exec(species);
	return str + "=" + RegExp.$1 + "+" + RegExp.$2;
}

function trim (zeichenkette) {
  // Erst führende, dann Abschließende Whitespaces entfernen
  // und das Ergebnis dieser Operationen zurückliefern
  return zeichenkette.replace (/^\s+/, '').replace (/\s+$/, '');
}

function getExp(str) {
	var exp = document.getElementById(str).firstChild.nodeValue;
	return "projects[]=" + trim(exp);
}

function getReg(ex) {
	var reg_ex = ex.lastChild.nodeValue;
	var chr_ex = ex.firstChild.nodeValue;
	var expr = /(\d+)\D+(\d+)/;
	expr.exec(reg_ex);
	return chr_ex + ":" + RegExp.$1 + "-" + RegExp.$2;
}

/**
 * In the header cells, the regions in cM are seperated by line breaks.
 * 
 * @param ex
 * @returns {String}
 */
function getRegHeader(ex) {
	var chr = ex.firstChild;
	return chr.nodeValue + ":" + chr.nextSibling.nextSibling.nodeValue + "-"
			+ ex.lastChild.nodeValue;
}

function call_detail_view(e) {
	var p = mouseMove(e);
	var oElement = document.elementFromPoint(p.left, p.top);
	if (oElement.tagName == "TD" && oElement.className == "syn") {
		oElement.id = "id1";
		var cell = $("#id1");
		var tr = cell.parent("tr");
		var colIndex = tr.children().index(cell);
		var rowIndex = tr.parent("tbody").children().index(tr);
		oElement.removeAttribute("id");
		var row = oElement.parentNode;
		var ex2 = row.children[0];
		var head = row.parentNode.parentNode.getElementsByTagName('thead')[0];
		var ex1 = head.getElementsByTagName('th')[colIndex];
		// var species1 = getSpecies('species1');
		// var species2 = getSpecies('species2');
		var species1 = getExp('exp1');
		var species2 = getExp('exp2');
		window.location.href = "detailHomology.php?" + species1 + "&" + species2
				+ "&region1=" + getRegHeader(ex1) + "&region2=" + getReg(ex2);
		// alert("row: " + getReg(ex2) + " col: " + getReg(ex2) + " sp1");

	} else {
		// alert(oElement.tagName);
	}
}

/*
 * use, if multirow-cells are needed $.fn.getNonColSpanIndex = function() { if
 * (!$(this).is('td') && !$(this).is('th')) return -1;
 * 
 * var allCells = this.parent('tr').children(); var normalIndex =
 * allCells.index(this); var nonColSpanIndex = 0;
 * 
 * allCells.each(function(i, item) { if (i == normalIndex) return false;
 * 
 * var colspan = $(this).attr('colspan'); colspan = colspan ? parseInt(colspan) :
 * 1; nonColSpanIndex += colspan; });
 * 
 * return nonColSpanIndex; };
 */

/*
 * (function($) { var check = false, isRelative = true;
 * 
 * $.elementFromPoint[function(x, y) { if (!document.elementFromPoint) return
 * null;
 * 
 * if (!check) { var sl; if ((sl = $(document).scrollTop()) > 0) { isRelative =
 * (document.elementFromPoint(0, sl + $(window).height() - 1) == null); } else
 * if ((sl = $(document).scrollLeft()) > 0) { isRelative =
 * (document.elementFromPoint(sl + $(window).width() - 1, 0) == null); } check =
 * (sl > 0); }
 * 
 * if (!isRelative) { x += $(document).scrollLeft(); y +=
 * $(document).scrollTop(); }
 * 
 * return document.elementFromPoint(x, y); }]
 * 
 * })(jQuery);
 */