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
use Docalist\Biblio\TypeSettings;
use Docalist\Biblio\FieldSettings;
use Docalist\Forms\Fragment;

/**
 * Edite la grille de saisie d'un type.
 *
 * @param DatabaseSettings $database La base à éditer.
 * @param int $dbindex L'index de la base.
 * @param TypeSettings $type Le type à éditer.
 * @param int $typeindex L'index du type.
 */

/* @var $database DatabaseSettings */
/* @var $type TypeSettings */


wp_enqueue_script(
    'docalist-biblio-type-fields',
    plugins_url('docalist-biblio/js/type-fields.js'),
    array( 'jquery-ui-sortable'),
    '20131026',
    true
);

?>
<style type="text/css">
#fields li {
    margin-left: 3em;
    margin-bottom: 6px;
}
#fields .group {
    margin-left: 0;
}

#fields .group h3 span {
    font-weight: bold;
}
</style>

<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= sprintf(__('%s - grille de saisie "%s"', 'docalist-biblio'), $database->label, $type->label) ?></h2>

    <p class="description">
        <?= __('L\'écran ci-dessous vous permet de personnaliser la grille de saisie.', 'docalist-biblio') ?>
        <?= __('Cliquez sur un champ pour afficher et modifier ses propriétés.', 'docalist-biblio') ?>
        <?= __('Utilisez le bouton "Ajouter un groupe" pour créer un nouveau groupe de champs.', 'docalist-biblio') ?>
        <?= __('Vous pouvez également modifier l\'ordre des champs et les déplacer d\'un groupe à un autre en faisant un glisser/déposer.', 'docalist-biblio') ?>
    </p>

    <form action ="" method="post">
        <?php buttons() ?>
        <ul id="fields" class="metabox-holder">
            <?php
                $lastGroup = 0;
                foreach($type->fields as $field) makeBox($field, $lastGroup);
            ?>
        </ul>
        <?php buttons() ?>
    </form>

    <!-- Template utilisé pour créer de nouveaux groupes. -->
    <script type="text/html" id="group-template" data-last-group="<?= ++$lastGroup ?>">
        <?php
            $field = new FieldSettings([
                'name' => 'group',
                'label' => __('Nouveau groupe de champs', 'docalist-biblio'),
            ]);

            $lastGroup = '{group-number}';
            makeBox($field, $lastGroup, false)
        ?>
    </script>
</div>

<?php
/**
 * Génère la boite pour un champ.
 *
 * @param FieldSettings $field
 * @param boolean $closed
 */
function makeBox(FieldSettings $field, & $lastGroup, $closed = true) { ?>
    <li class="postbox <?= $closed ? 'closed' : '' ?> <?= $field->name ?>">
        <div class="handlediv"></div>
        <h3><span><?= $field->label ?: $field->name ?></span></h3>
        <div class="inside"><?php fieldForm($field, $lastGroup) ?></div>
    </li><?php
}

/**
 * Génère le formulaire permettant de paramètrer un champ (ou un groupe).
 *
 * Notre page est un gros formulaire, composé de chacun des "bouts de
 * formulaire" propre à chaque champ. Lorsque la page est enregistrée, tous les
 * champs sont transmis dans l'ordre de la page, ce qui fait qu'on n'a pas à
 * gérer nous-mêmes le tri des champs.
 *
 * @param FieldSettings $field Le champ à éditer.
 */
function fieldForm(FieldSettings $field, & $lastGroup) {
    // Chaque séparateur doit avoir un nom unique
    $name = $field->name;
    $name === 'group' && $name .= ++$lastGroup;

    // Crée le formulaire du champ
    $form = new Fragment();
    $form->hidden('name')
         ->attribute('class', 'name');
    $form->input('label')
         ->attribute('id', $name . '-label')
         ->attribute('class', 'label regular-text');
    $form->textarea('description')
         ->attribute('id', $name . '-description')
         ->attribute('class', 'description large-text')
         ->attribute('rows', 2);

    // Affiche le formulaire
    // On veut que les champs aient un nom de la forme champ[label]
    // Pour cela, on insère le formulaire dans un fragment qui contient le nom du champ
    $parent = new Fragment($name);
    $parent->add($form);
    $form->bind($field)->render('wordpress', array('indent' => true));

    if ($field->name === 'group') { ?>
        <button class="delete-group button right" type="button"><?= __('Supprimer ce groupe', 'docalist-biblio') ?></button>
        <br class="clear" />
        <?php
    }
}?>

<?php
/**
 * Génère les boutons "Ajouter une groupe" et "Enregistrer les modifications".
 *
 * Sous forme de fonction pour permettre de répéter les boutons en haut et en
 * bas de page.
 */
function buttons() { ?>
    <p class="buttons" style="text-align: right">
        <button type="button" class="button add-group">
            <?= __('Ajouter un groupe', 'docalist-biblio') ?>
        </button>
        <button type="submit" class="button-primary ">
            <?= __('Enregistrer les modifications', 'docalist-biblio') ?>
        </button>
    </p><?php
}?>