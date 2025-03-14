<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_listincsv.class.php
 * \ingroup listincsv
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */
require_once __DIR__ . '/../backport/v19/core/class/commonhookactions.class.php';

/**
 * Class ActionsListInCSV
 */
class ActionsListInCSV extends \listincsv\RetroCompatCommonHookActions
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct() {
	}

	/**
	 * doActions
	 *
	 * @param array()         $parameters     Hook metadatas (context, etc...)
	 * @param CommonObject    &$object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string          &$action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function doActions($parameters, &$object, &$action, $hookmanager) {

		global $db, $user;

		if (GETPOSTISSET('exportlistincsv', 'bool') && is_object($object) && method_exists($object, 'call_trigger')) {
			$object->call_trigger('LISTINCSV_EXPORT_FILE_' . strtoupper($object->element), $user);
		}
	}

	/**
	 * printCommonFooter
	 *
	 * @param array()         $parameters     Hook metadatas (context, etc...)
	 * @param CommonObject    &$object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param string          &$action Current action (if set). Generally create or edit or null
	 * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function printCommonFooter($parameters, &$object, &$action, $hookmanager) {
		$TContext = explode(':', $parameters['context']);
		$context_list = preg_grep('/(.*list$)/i', $TContext);

		// Gestion des objets référents produits
		if (empty($context_list)) {
			$context_list = preg_grep('/(productstats.*)/i', $TContext);
		}

		// Gestion des écrans objets référents tiers
		if (empty($context_list)) {
			$context_list = preg_grep('/(consumptionthirdparty)/i', $TContext);
		}

		// Gestion des écrans objets référents tiers
		if (empty($context_list)) {
			$context_list = preg_grep('/(stockatdate)/i', $TContext);
		}

		// Gestion des écrans objets référents tiers
		if (empty($context_list)) {
			$context_list = preg_grep('/(thirdpartycustomerprice)/i', $TContext);
		}
		if (empty($context_list)) {
			$context_list = preg_grep('/(agefoddsessionsubscribers)/i', $TContext);
		}

		// Permettre à d'autres modules externes d'utiliser listInCSV
		$parameters['context_list'] = &$context_list;
		$reshook = $hookmanager->executeHooks('listInCSVFooterContext', $parameters);
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if (! empty($context_list)) {
			global $langs, $user, $conf;
			$langs->load('listincsv@listincsv');

			if (! empty($user->hasRight('listincsv', 'export'))) {

				require_once __DIR__ . './../lib/listincsv.lib.php';

				$pathtojs = dol_buildpath('/listincsv/js/listincsv.js.php', 1);

				$download = getListInCSVDownloadLink();

				$socid = GETPOST('socid');
				if (empty($socid)) $socid = 0;

				$varsForJs = array(
					'downloadButton' => $download,
					'TContexts' => explode(':', $parameters['context']),
					'socid' => $socid,
					'langs' => array(
						'FileGenerationInProgress' => $langs->trans('FileGenerationInProgress')
					),
					'conf' => array(
						'LISTINCSV_DONT_REMOVE_TOTAL' => ! empty(getDolGlobalInt('LISTINCSV_DONT_REMOVE_TOTAL')),
						'LISTINCSV_DELETESPACEFROMNUMBER' => ! empty(getDolGlobalInt('LISTINCSV_DELETESPACEFROMNUMBER'))
					),
				);
				// Inclusion d'un JS qui va permettre de télécharger la liste
				?>
				<script type="text/javascript" language="javascript" src="<?php echo $pathtojs; ?>"></script>
				<script type="text/javascript" language="javascript">
					/**
					 * @param {string}   varsFromPHP.downloadButton  Bouton ListInCSV
					 * @param {string[]} varsFromPHP.TContexts       Liste des contextes de hooks
					 * @param {Number}   varsFromPHP.socid           ID du tiers si applicable, 0 sinon
					 * @param {string[]} varsFromPHP.langs           Traductions utilisées par js
					 * @param {Object}   varsFromPHP.conf            Confs utilisées par js
					 */
					const listInCSVMain = function (varsFromPHP) {
						// Ajout du bouton ListInCSV
						if (varsFromPHP.TContexts.includes('projecttasklist')) {
							// Cas particulier de la liste des tâches sur un projet
							// `first()` parce qu'il peut y avoir plusieurs titres sur la page.
							$('#id-right > form#searchFormList div.titre').first().append(varsFromPHP.downloadButton);
						} else if (varsFromPHP.TContexts.some(ctx => ctx.match(/^productstats/))) {
							// Cas particulier des objets référents sur un produit
							$('form[name="search_form"] table.table-fiche-title div.titre').first().append(varsFromPHP.downloadButton);
						} else if (varsFromPHP.TContexts.some(ctx => ctx === 'agefoddsessionsubscribers')) {
							// Cas particulier de la liste des inscrits à une session de formation
							$('div.fiche div.titre').eq(1).append(varsFromPHP.downloadButton);
						} else {
							const divTitre = $('div.fiche div.titre').first();
							if (typeof divTitre.val() !== 'undefined') {
								// Cas général
								divTitre.append(varsFromPHP.downloadButton);
							} else {
								// S'il n'y a pas de titre, on l'ajoute à côté de la loupe c'est mieux que rien...
								$('[name="button_search"]').after(varsFromPHP.downloadButton);
							}
						}

						// Action du clic sur le bouton
						$(document).on('click', ".export", function (event) {
							// Récupération des données du formulaire de filtre et transformation en objet
							const $form = $('div.fiche form').first(); // Les formulaire de liste n'ont pas tous les même name
							const data = objectifyForm($form.serializeArray());

							// Pas de limite, on veut télécharger la liste totale
							data.limit = 10000000;
							data.socid = varsFromPHP.socid;
							data.exportlistincsv = 1;

							const $self = $(this);
							const $dialogPopup = $('#dialogforpopup');

							$dialogPopup.html(varsFromPHP.langs['FileGenerationInProgress']);
							$dialogPopup.dialog({
								open: function (event, ui) {
									var used_url = $form.attr('data-listincsv-url');
									if (typeof used_url === 'undefined') used_url = $form.attr('action');

									// Envoi de la requête HTTP en mode synchrone
									$.ajax({
										url: used_url,
										type: $form.attr('method'),
										data: data,
										async: false
									}).done(function (html) {
										// Récupération de la table html qui nous intéresse
										var $table = $(html).find('table.liste');
										let search = $table.find('tr.liste_titre_filter');
										// Nettoyage de la table avant conversion en CSV

										// Suppression des filtres de la liste
										$table.find('tr.liste_titre_filter').remove(); // >= 6.0
										$table.find('tr:has(td.liste_titre)').remove(); // < 6.0

                                        // Suppression de la dernière colonne qui contient seulement les loupes des filtres
										<?php
										if (!getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN')) { ?>
											$table.find('th:last-child, td:last-child').each(function(index) {
												$(this).find('dl').remove();
												if ($(search).length > 0 && $(this).closest('table').hasClass('liste')) {
													$(this).remove(); // Dans les listes ne contenant pas de recherche, il ne faut pas supprimer la dernière colonne
												}
											});
										<?php } else { ?>
											$table.find('th:first-child, td:first-child').each(function(index) {
												$(this).find('dl').remove();
												if ($(search).length > 0 && $(this).closest('table').hasClass('liste')) {
													$(this).remove(); // Dans les listes ne contenant pas de recherche, il ne faut pas supprimer la dernière colonne
												}
											});
										<?php } ?>

										// Suppression de la ligne TOTAL en pied de tableau
										if (varsFromPHP.conf['LISTINCSV_DONT_REMOVE_TOTAL']) {
											$table.find('tr.liste_total').remove();
										}

										//Suppression des espaces pour les nombres
										if (varsFromPHP.conf['LISTINCSV_DELETESPACEFROMNUMBER']) {
											$table.find('td').each(function (e) {
												let nbWthtSpace = $(this).text().replace(/ /g, '').replace(/\xa0/g, '');
												let commaToPoint = nbWthtSpace.replace(',', '.');
												if ($.isNumeric(commaToPoint)) $(this).html(nbWthtSpace);
											});
										}

										// Remplacement des sous-table par leur valeur text(), notamment pour la ref dans les listes de propales, factures...
										$table.find('td > table').map(function (i, cell) {
											$cell = $(cell);
											$cell.html($cell.text());
										});

										// Transformation de la table liste en CSV + téléchargement
										var args = [$table, 'export.csv'];
										exportTableToCSV.apply($self, args);

										$('#dialogforpopup').dialog('close');
									});
								}
							});
						});
					};

					$(document).ready(() => listInCSVMain(<?php echo json_encode($varsForJs) ?>));
				</script>
				<?php
			} // End Rights test
		}

		return 0;
	}
}
