<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2015 Daniel Ménard
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

use Docalist\Biblio\Settings\DatabaseSettings;
use Docalist\Biblio\Settings\TypeSettings;
use Docalist\Schema\Schema;
use Docalist\Schema\Field;
use Docalist\Forms\Fragment;
use Docalist\Forms\Assets;
use Docalist\Forms\Themes;
use Docalist\Utils;
use Docalist\Biblio\Reference;

/**
 * Edite une grille.
 *
 * @param DatabaseSettings $database La base à éditer.
 * @param int $dbindex L'index de la base.
 * @param TypeSettings $type Le type à éditer.
 * @param int $typeindex L'index du type.
 * @param Schema $grid La grille à éditer.
 * @param string $gridname L'index de la grille.
 */

/* @var $database DatabaseSettings */
/* @var $type TypeSettings */
/* @var $grid Schema */


wp_enqueue_script(
    'docalist-biblio-grid-edit',
    plugins_url('docalist-biblio/views/grid/edit.js'),
    array( 'jquery-ui-sortable'),
    '20140725',
    true
);

$boxes = [];
$assets = new Assets();
foreach($grid->fields as $field) {
    $box = createBox($field, $gridname);
    $boxes[] = $box;
    $assets->add($box->assets());
}
$assets->add(Themes::assets('wordpress'));
Utils::enqueueAssets($assets);
wp_enqueue_style('docalist-biblio-edit-reference');

?>
<style type="text/css">
    <?php if ($gridname !== 'base') :?>
        #fields li {
            margin-left: 3em;
        }
    <?php endif; ?>
    #fields li {
        margin-bottom: 10px;
    }
    #fields li.closed {
        margin-bottom: 0px;
    }
    #fields li h3:before {
        font-family: "dashicons";
        content: "\f464";
        vertical-align: text-bottom;
        -webkit-font-smoothing: antialiased;
        padding-right: .5em;
        color: #777;
    }
    #fields li.has-cap h3:after {
        font-family: "dashicons";
        content: "\f112";
        vertical-align: text-bottom;
        -webkit-font-smoothing: antialiased;
        padding-left: .5em;
        color: #800;
    }
    #fields li.group {
        margin-top: 6px;
        margin-left: 0;
        background-color: #f9f9f9;
    }
    #fields li.group:first-child {
        margin-top: 0px;
    }
    #fields li.group.closed {
    }
    #fields li.group h3:before {
        content: "\f203";
    }
    #fields li.group h3 span {
        font-weight: bold;
    }
</style>

<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__('%s - %s - %s', 'docalist-biblio'), $database->label(), $type->label(), $grid->label()) ?></h2>

    <p class="description">
        <?= __('L\'écran ci-dessous vous permet de personnaliser la grille.', 'docalist-biblio') ?>
        <?= __('Cliquez sur un champ pour afficher et modifier ses propriétés.', 'docalist-biblio') ?>
        <?= __('Utilisez le bouton "Ajouter un groupe" pour créer un nouveau groupe de champs.', 'docalist-biblio') ?>
        <?= __('Vous pouvez également modifier l\'ordre des champs et les déplacer d\'un groupe à un autre en faisant un glisser/déposer.', 'docalist-biblio') ?>
    </p>

    <form action ="" method="post">
        <?php buttons($gridname) ?>
        <ul id="fields" class="metabox-holder meta-box-sortables">
            <?php
                $i = 0;
                foreach($grid->fields as $field) {
                    renderBox($boxes[$i], $field, true);
                    $i++;
                }
            ?>
        </ul>
        <?php buttons($gridname) ?>
    </form>

    <!-- Template utilisé pour créer de nouveaux groupes. -->
    <script type="text/html" id="group-template">
        <?php
            $field = new Field([
                'name' => 'group{group-number}',
                'type' => 'Docalist\Biblio\Type\Group',
                'label' => __('Nouveau groupe de champs', 'docalist-biblio'),
                'newgroup' => true, // utilisé par Group::editForm() et MakeBox
                'state' => '', // = normal
            ]);

            $box = createBox($field, $gridname);
            renderBox($box, $field, false);
        ?>
    </script>
</div>

<?php
/**
 * Génère le formulaire permettant de paramètrer un champ (ou un groupe).
 *
 * Notre page est un gros formulaire, composé de chacun des "bouts de
 * formulaire" propre à chaque champ. Lorsque la page est enregistrée, tous les
 * champs sont transmis dans l'ordre de la page, ce qui fait qu'on n'a pas à
 * gérer nous-mêmes le tri des champs.
 *
 * @param Field $field
 * @param boolean $closed
 */
function createBox(Field $schema, $gridname) {
    $type = $schema->collection() ?: $schema->type();
    $field = new $type(null, $schema);
    switch($gridname) { /* @var $form Fragment */
        case 'base':
            $form = $field->baseSettings(); // paramètres de base
            break;
        case 'edit':
            $form = $field->editSettings(); // paramètres de saisie
            break;
        default:
            $form = $field->displaySettings(); // paramètres d'affichage
            break;
    }

    // pour les nouveaux groupes il faut absolument avoir le type, sinon le groupe Field est créé comme un string (type par défaut)
    if ($schema->newgroup) {
        $form->hidden('type');
    }

    // On veut que les champs aient un nom de la forme champ[label]
    // Pour cela, on insère le formulaire dans un fragment parent qui contient
    // le nom du champ. Par contre, on fait le bind sur $form (sinon on ne
    // peut pas récupérer les libellés/desc par défaut).
    $parent = new Fragment($form->name());
    $form->name('');
    $parent->add($form);
    $form->bind($schema->toArray());

    return $form;
}

function renderBox(Fragment $form, Field $schema, $closed = true) { ?>
    <?php
        $type = $schema->collection() ?: $schema->type();
        $class = ['postbox'];
        $closed && $class[] = 'closed';
        if ($type === 'Docalist\Biblio\Type\Group') {
            $class[] = 'group';
        } else {
            $class[] = $schema->name();
        }
        $schema->capability() && $class[] = 'has-cap';
        $class = implode(' ', $class);
     ?>
    <li id="<?= $schema->name() ?>" class="<?=$class ?>">
        <div class="handlediv"></div>
        <h3 class="hndle"><span><?= $schema->label() ?: $schema->name() ?></span></h3>
        <div class="inside">
            <?php $form->render('wordpress') ?>
        </div>
    </li><?php
}

/**
 * Génère les boutons "Ajouter une groupe" et "Enregistrer les modifications".
 *
 * Sous forme de fonction pour permettre de répéter les boutons en haut et en
 * bas de page.
 */
function buttons($gridname) { ?>
    <p class="buttons" style="text-align: right">
        <?php if ($gridname !== 'base') :?>
            <button type="button" class="button add-group">
                <?= __('Ajouter un groupe', 'docalist-biblio') ?>
            </button>
        <?php endif;?>
        <button type="submit" class="button-primary ">
            <?= __('Enregistrer les modifications', 'docalist-biblio') ?>
        </button>
    </p><?php
}?>