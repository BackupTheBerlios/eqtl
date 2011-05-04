/**
 * Adjusted from:
 * http://aktuell.de.selfhtml.org/artikel/javascript/scrolltabelle/index.htm
 * 
 * @author Georg
 */


var aktFrame = "untRe";
var offAbove = "-157px";
var offLeft = "-240px";
//var offAbove = "-138px";
//var offLeft = "-218px";


function init() {
	untLi.document.getElementById('cont').style.top = offAbove;
	obRe.document.getElementById('cont').style.left = offLeft;
	untRe.document.getElementById('cont').style.left = offLeft;
	untRe.document.getElementById('cont').style.top = offAbove;
	balken();
	scrollen();
	for (i = 0; i < frames.length; i++) {
		frames[i].document.onkeydown = MyFocus;
	}
	MyFocus();
}

function MyFocus() {
	aktFrame = "untRe";
	untRe.focus();
}

function balken() {
	if (document.all) {
		var breite = untRe.document.body.clientWidth;
		var hoehe = untRe.document.body.clientHeight;
		breite = breite + ',*';
		hoehe = offAbove + ',' + hoehe + ',*';
		document.all.oben.setAttribute('cols', breite, 'false');
		document.all.links.setAttribute('rows', hoehe, 'false');
	} else {
		var breite = 0;
		while (untRe.outerWidth < obRe.outerWidth) {
			breite++;
			br = '*,' + breite;
			document.getElementById('oben').setAttribute('cols', br, 'false');
		}
		var hoehe = 0;
		while (untRe.outerHeight < untLi.outerHeight) {
			hoehe++;
			ho = offAbove + ',*,' + hoehe;
			document.getElementById('links').setAttribute('rows', ho, 'false');
		}
	}
}

/**
 * helper for scrollen()
 * 
 * @param MyFrame
 * @returns
 */
function hor(MyFrame) {
	if (document.all)
		return MyFrame.document.body.scrollLeft;
	else
		return MyFrame.pageXOffset;
}

/**
 * helper for scrollen()
 * 
 * @param MyFrame
 * @returns
 */
function ver(MyFrame) {
	if (document.all)
		return MyFrame.document.body.scrollTop;
	else
		return MyFrame.pageYOffset;
}

var sc; // Deklaration nur bei Mozilla-Workaround erforderlich (optional)

function scrollen() {
//	window.document.onclick = call_detail_view;
	switch (aktFrame) {

	case "untRe":
		obRe.scrollTo(hor(untRe), 0);
		untLi.scrollTo(0, ver(untRe));
		break;

	case "untLi":
		untRe.scrollTo(hor(untRe), ver(untLi));
		obRe.scrollTo(hor(untRe), 0);
		untLi.scrollTo(0, ver(untLi));
		break;

	case "obRe":
		untRe.scrollTo(hor(obRe), ver(untRe));
		untLi.scrollTo(0, ver(untRe));
		obRe.scrollTo(hor(obRe), 0);
		break;

	case "obLi":
		untRe.scrollTo(hor(obRe), ver(untLi));
		obLi.scrollTo(0, 0);
		break;

	default:
		obRe.scrollTo(hor(untRe), 0);
		untLi.scrollTo(0, ver(untRe));
		aktFrame = "untRe";
	}
	// Beginn Mozilla Workaround Teil2 (optional)
	if (!document.all && !document.layers) {
		sc = window.setTimeout("scrollen()", 83);
	}
	// Ende Mozilla Workaround Teil2
}

window.onresize = function() {
	balken();
};
