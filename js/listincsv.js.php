<?php
/* Copyright (C) 2005-2014  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2014  Regis Houssin       <regis.houssin@capnetworks.com>
 * Copyright (C) 2015       Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 * \file		htdocs/listincsv/js/lib_head.js.php
 * \brief		File that include javascript functions (included if option use_javascript activated)
 * 				JQuery (providing object $) and JQuery-UI (providing $datepicker) libraries must be loaded before this file.
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled cause need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');
if (! defined('NOREQUIRESOC'))    define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled cause need to do translations
if (! defined('NOCSRFCHECK'))     define('NOCSRFCHECK',1);
if (! defined('NOTOKENRENEWAL'))  define('NOTOKENRENEWAL',1);
if (! defined('NOLOGIN'))         define('NOLOGIN',1);
if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);
if (! defined('NOREQUIREHTML'))   define('NOREQUIREHTML',1);
if (! defined('NOREQUIREAJAX'))   define('NOREQUIREAJAX','1');

session_cache_limiter(FALSE);

$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=@include_once '../../main.inc.php';       // to work if your module directory is into a subdir of root htdocs directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include_once '../../../main.inc.php'; // to work if your module directory is into a subdir of root htdocs directory

// Define javascript type
top_httphead('text/javascript; charset=UTF-8');
// Important: Following code is to avoid page request by browser and PHP CPU at each Dolibarr page access.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');
?>

/**
 * Returns a clone of the jquery collection $elem with all invisible (display:none) elements removed
 * @param $elem  A jQuery collection
 * @returns cloned jQuery collection
 */
function stripInvisible($elem) {
	// https://stackoverflow.com/a/28963556/11987795
	var $clone = $elem.clone();
	$('body').append($clone);
	$clone.find('*:not(:visible)').remove();
	$clone.remove();
	return $clone;
}

// Function found here : https://stackoverflow.com/questions/16078544/export-to-csv-using-jquery-and-html
function exportTableToCSV($table, filename) {
	var $rows = stripInvisible($table).find('tr:has(th),tr:has(td)'),
	// Temporary delimiter characters unlikely to be typed by keyboard
	// This is to avoid accidentally splitting the actual contents
	tmpColDelim = String.fromCharCode(11), // vertical tab character
	tmpRowDelim = String.fromCharCode(0), // null character

	// actual delimiter characters for CSV format
	colDelim = '"<?php global $conf; echo (! empty($conf->global->EXPORT_CSV_SEPARATOR_TO_USE)?$conf->global->EXPORT_CSV_SEPARATOR_TO_USE:';')?>"',
	rowDelim = '"\r\n"',

	// Grab text from table into CSV formatted string
	csv = '\ufeff"' + $rows.map(function(i, row) {
		var $row = $(row),
		$cols = $row.find('th,td');

		return $cols.map(function(j, col) {
			var $col = $(col);

			var text = "";
			if ($col.find("span.linkobject:not(.hideobject)").length > 0) {
				// Fix sur liste produit si conf MAIN_DIRECT_STATUS_UPDATE active
				text = $col.find("span.linkobject:not(.hideobject)").children().first().attr('title').trim();
			} else if ($col.find('a').length > 0 && $col.find('a')[0].href.indexOf('mailto:') == 0) {
				// Fix mails tronqués dans les listes par dol_trunc dans la fonction dol_print_email
				link=$col.find('a')[0].href;
				text = link.substr(7);
			} else text = $col.text().trim();

			// Spécifique pour "nettoyer" les données
			// Si texte vide, on cherche une image et on prend le title
			if(text == '' && $col.find('img').length > 0) {
				imgtitle = $col.find('img').attr('title');
				if (imgtitle != undefined) text = imgtitle.trim();
			}

			return text.replace(/"/g, '""'); // escape double quotes

		}).get().join(tmpColDelim);

	}).get().join(tmpRowDelim)
	.split(tmpRowDelim).join(rowDelim)
	.split(tmpColDelim).join(colDelim) + '"';

	// Deliberate 'false', see comment below
	if (false && window.navigator.msSaveBlob) {

		var blob = new Blob([decodeURIComponent(csv)], {
			type: 'text/csv;charset=utf8'
		});

		// Crashes in IE 10, IE 11 and Microsoft Edge
		// See MS Edge Issue #10396033
		// Hence, the deliberate 'false'
		// This is here just for completeness
		// Remove the 'false' at your own risk
		window.navigator.msSaveBlob(blob, filename);

	} else if (window.Blob && window.URL) {
		// HTML5 Blob
		var blob = new Blob([csv], {
			type: 'text/csv;charset=utf-8'
		});
		var csvUrl = URL.createObjectURL(blob);

		$(this)
		.attr({
			'download': filename,
			'href': csvUrl
		});
	} else {
		// Data URI
		var csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

		$(this)
		.attr({
			'download': filename,
			'href': csvData,
			'target': '_blank'
		});
	}
}

//serialize data function
function objectifyForm(formArray) {
	var returnArray = {};
	for (var i = 0; i < formArray.length; i++) {
		if (formArray[i]['name'].substring(formArray[i]['name'].length - 2) == "[]") {
			var name = formArray[i]['name'].substring(0, formArray[i]['name'].length - 2);
			if (!returnArray.hasOwnProperty(name)) returnArray[name] = [];
			returnArray[name].push(formArray[i]['value']);
		}
		else returnArray[formArray[i]['name']] = formArray[i]['value'];
	}
	return returnArray;
}
