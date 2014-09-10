<?php
/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012-2014 Daniel Ménard
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
    <h2><?= sprintf(__('%s - %s - %s', 'docalist-biblio'), $database->label(), $type->label(), $grid->label()) ?></h2>

    <p class="description">
        <?= __('L\'écran ci-dessous vous permet de personnaliser la grille.', 'docalist-biblio') ?>
        <?= __('Cliquez sur un champ pour afficher et modifier ses propriétés.', 'docalist-biblio') ?>
        <?= __('Utilisez le bouton "Ajouter un groupe" pour créer un nouveau groupe de champs.', 'docalist-biblio') ?>
        <?= __('Vous pouvez également modifier l\'ordre des champs et les déplacer d\'un groupe à un autre en faisant un glisser/déposer.', 'docalist-biblio') ?>
    </p>

    <form action ="" method="post">
        <?php buttons() ?>
        <ul id="fields" class="metabox-holder meta-box-sortables">
            <?php
                foreach($grid->fields as $field) makeBox($field, true, $gridname);
            ?>
        </ul>
        <?php buttons() ?>
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

            makeBox($field, false, $gridname)
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
function makeBox(Field $schema, $closed = true, $gridname) { ?>
    <?php
        $type = $schema->collection() ?: $schema->type();
    ?>
    <li id="<?= $schema->name() ?>" class="postbox <?= $closed ? 'closed' : '' ?> <?= $type === 'Docalist\Biblio\Type\Group' ? 'group' : $schema->name() ?>">
        <div class="handlediv"></div>
        <h3><span><?= $schema->label() ?: $schema->name() ?></span></h3>
        <div class="inside">
            <?php
            $field = new $type(null, $schema);
            $form = ($gridname === 'edit') ? $field->settingsForm() : $field->formatSettings();

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
            $form->bind($schema->toArray())->render('wordpress', array('indent' => true));
            ?>
        </div>
    </li><?php
}

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