<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist
 * @subpackage  Biblio
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 * @version     $Id$
 */
namespace Docalist\Biblio\Views;

use Docalist\Biblio\DatabaseSettings;
use Docalist\Schema\Schema;
use Docalist\Forms\Fragment;

/**
 * Edite un format d'affichage.
 *
 * @param DatabaseSettings $database La base à éditer.
 * @param int $dbindex L'index de la base.
 * @param Schema $type Le type à éditer.
 * @param int $typeindex L'index du type.
 */

/* @var $database DatabaseSettings */
/* @var $type Schema */


wp_enqueue_script(
    'docalist-biblio-type-display',
    plugins_url('docalist-biblio/views/type/display.js'),
    array('jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable'),
    '20131026',
    true);
?>
<style type="text/css">
    .toolbox h4 {
        margin: 0.5em 0;
    }
    .postbox{
        margin-bottom: 0.5em;
    }
    .children  {
        padding: 0.5em 2em;
        min-height: 2em; /* pour pouvoir dropper si la liste est vide */
    }

    .children li {
        margin: 0 0 0.5em 0;
    }
/*
    .children .children li {
        margin-left: 2em;
    }
*/
    .closed .children {
        margin-top: 0em;
    }

    /*
        Par défaut, dans wordpress, tous les .inside réagissent au .closed (i.e.
        wordpress n'a pas prévu qu'on puisse avoit des postbox dans des postbox).
        On corrige ça en annulant la règle wordpress et en la remplaçant par une
        régle qui ne vise que les .inside qui sont des descendants directs.
    */
    .js .closed .inside {
        display: block;
    }

    .js .closed>.inside {
        display: none;
    }

</style>

<div class="wrap">
    <?= screen_icon()?>
    <h2><?= sprintf(__('%s - format affichage "%s"', 'docalist-biblio'), $database->label(), $type->label()) ?></h2>

    <p class="description">
        <?= __('todo.', 'docalist-biblio')?>
    </p>

    <?php // @see http://wp.tutsplus.com/tutorials/integrating-with-wordpress-ui-meta-boxes-on-custom-pages/  ?>

    <?php /* Template pour les propriétés d'un champ */?>
    <script type="text/html" id="field-template">
        <li class="field postbox closed">
            <div class="handlediv"></div>
            <h3><span>champ yyy</span></h3>
            <div class="inside">
            <?php getFormFor('field')->render('wordpress') ?>
            </div>
        </li>
    </script>

    <?php /* Template pour les propriétés d'un groupe */?>
    <script type="text/html" id="group-template">
        <div class="postbox closed">
            <div class="handlediv"></div>
            <h3><span>groupe xxx</span></h3>
            <div class="inside">
            <?php getFormFor('group')->render('wordpress') ?>
            </div>
            <ul class="children">
            </ul>
        </div>
    </script>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
<?php /*
                <div class="postbox closed">
                    <div class="handlediv"></div>
                    <h3><span>Modèle</span></h3>
                    <div class="inside">
                        paramètres du format (params du groupe de premier niveau).
                    </div>
                    <ul class="children">
                        <li class="field postbox closed">
                            <div class="handlediv"></div>
                            <h3><span>champ 1</span></h3>
                            <div class="inside">
                                params champ 1
                            </div>
                        </li>
                        <li class="field postbox closed">
                            <div class="handlediv"></div>
                            <h3><span>champ 2</span></h3>
                            <div class="inside">
                                params champ 2
                            </div>
                        </li>
                        <li class="group postbox closed">
                            <div class="handlediv"></div>
                            <h3><span>Groupe 1</span></h3>
                            <div class="inside">
                                params du groupe 1
                            </div>
                            <ul class="children">
                                <li class="field postbox closed">
                                    <div class="handlediv"></div>
                                    <h3><span>champ 1.1</span></h3>
                                    <div class="inside">
                                        params champ 1.1
                                    </div>
                                </li>
                                <li class="field postbox closed">
                                    <div class="handlediv"></div>
                                    <h3><span>champ 1.2</span></h3>
                                    <div class="inside">
                                        params champ 1.2
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li class="field postbox closed">
                            <div class="handlediv"></div>
                            <h3><span>champ 3</span></h3>
                            <div class="inside">
                                params champ 3
                            </div>
                        </li>
                    </ul>
                </div>
*/ ?>
            </div>

            <div id="postbox-container-1" class="postbox-container toolbox">
                <p class="buttons" style="text-align: right">
                    <button type="submit" class="button-primary ">
                        <?= __('Enregistrer les modifications', 'docalist-biblio') ?>
                    </button>
                </p>

                <div class="postbox">
                    <div class="handlediv"></div>
                    <h3><span>Contrôles</span></h3>
                    <div class="inside">
                        <h4>Ajouter un groupe :</h4>
                        <button type="button" class="button">Standard</button>
                        <button type="button" class="button">Premier rempli</button>

                        <h4>Ajouter un champ :</h4>
        		        <?php foreach($type->fields as $field) : ?>
        	                <?php if ($field->name() === 'group') continue; ?>
                            <button type="button" class="button-secondary add-field" data-field="<?= $field->name() ?>">
                                <?= $field->name() ?>
                            </button>
        		        <?php endforeach; ?>
    		        </div>
                </div>
            </div>

            <div id="postbox-container-2" class="postbox-container preview">
                <div class="postbox">
                    <h3><span>Aperçu</span></h3>
                    <div class="inside">
                        Aperçu de la notice.
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php /*
    <hr />
    <div id="poststuff">
		<div id="post-body" class="NUmetabox-holder columns-2">
			<div id="post-body-content">
    			<div class="postbox">
    			    <h3>Premier groupe de champs</h3>
    			    <div class="inside field-group">
    			        inside
    			    </div>
    			</div>
    			<div class="postbox">
    			    <h3>Second groupe de champs</h3>
    			    <div class="inside field-group">
    			        inside
    			    </div>
    			</div>
			</div>

			<div id="postbox-container-1" class="postbox-container">
			    Liste des champs
			    <ul class="field-list">
			        <?php // var_dump($type->fields->toArray()); ?>
			        <?php foreach($type->fields as $field) : ?>
		                <?php if ($field->name === 'group') continue; ?>
                        <li class="postbox closed">
                            <h3><span><?= $field->label ?: $field->name ?></span></h3>
                        </li>
			        <?php endforeach; ?>
			    </ul>
			</div>
		</div>
	</div>
*/ ?>
</div>

<?php
//require pl
$format = require get_template_directory() . '/format2.php';
$json = json_encode($format, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>

<script type="text/javascript">
var format = <?=$json ?>;
</script>

<?php
function getFormFor($what = 'field') {
    $form = new Fragment();

    if ($what === 'field') {
        $form->input('for')
             ->label(__('Filtre', 'docalist-biblio'))
             ->description(__('Afficher seulement les valeurs qui correspondent au code indiqué (seuls certains champs sont filtrables).', 'docalist-biblio'))
             ->addClass('large-text code');
    }

    $form->textarea('before')
         ->label(__('Avant', 'docalist-biblio'))
         ->description(__('Texte à afficher avant.', 'docalist-biblio'))
         ->attribute('rows', 2)
         ->addClass('large-text code');

    if ($what === 'group') {
        $form->textarea('row')
            ->label(__('Format des items', 'docalist-biblio'))
            ->description(__('Format qui sera appliqué à chacun des champs de ce groupe.', 'docalist-biblio'))
            ->attribute('rows', 2)
            ->addClass('large-text code');
        $form->textarea('between')
            ->label(__('Texte entre les items', 'docalist-biblio'))
            ->description(__('Texte à insérer entre deux items.', 'docalist-biblio'))
            ->attribute('rows', 2)
            ->addClass('large-text code');
    }

    $form->textarea('after')
         ->label(__('Après', 'docalist-biblio'))
         ->description(__('Texte à afficher après.', 'docalist-biblio'))
         ->attribute('rows', 2)
         ->addClass('large-text code');

    $form->textarea('label')
         ->label(__('Libellé', 'docalist-biblio'))
         ->description(__('Variables possibles : %label, %field. Vide = libellé par défaut, "-" = pas de libellé.', 'docalist-biblio'))
         ->attribute('rows', 2)
         ->addClass('large-text code');

    $form->textarea('content')
         ->label(__('Contenu', 'docalist-biblio'))
         ->description(__('Variables possibles : %label, %field.', 'docalist-biblio'))
         ->attribute('rows', 2)
         ->addClass('large-text code');

    $form->input('default')
         ->label(__('Valeur par défaut', 'docalist-biblio'))
         ->description(__('Valeur à afficher si le contenu est vide.', 'docalist-biblio'))
         ->addClass('large-text code');

    return $form;
}
?>