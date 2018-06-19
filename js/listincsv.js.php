// Function found here : https://stackoverflow.com/questions/16078544/export-to-csv-using-jquery-and-html
function exportTableToCSV($table, filename) {

	var $rows = $table.find('tr:has(th),tr:has(td)'),

	// Temporary delimiter characters unlikely to be typed by keyboard
	// This is to avoid accidentally splitting the actual contents
	tmpColDelim = String.fromCharCode(11), // vertical tab character
	tmpRowDelim = String.fromCharCode(0), // null character

	// actual delimiter characters for CSV format
	colDelim = '"<?php global $conf; echo (! empty($conf->global->IMPORT_CSV_SEPARATOR_TO_USE)?$conf->global->IMPORT_CSV_SEPARATOR_TO_USE:';')?>"',
	rowDelim = '"\r\n"',

	// Grab text from table into CSV formatted string
	csv = '\ufeff"' + $rows.map(function(i, row) {
		var $row = $(row),
		$cols = $row.find('th,td');

		return $cols.map(function(j, col) {
			var $col = $(col),
			text = $col.text().trim();

			// Spécifique pour "nettoyer" les données
			// Si texte vide, on cherche une image et on prend le title
			if(text == '' && $col.find('img').length > 0) {
				text = $col.find('img').attr('title').trim();
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
		returnArray[formArray[i]['name']] = formArray[i]['value'];
	}
	return returnArray;
}