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
use Docalist\Forms\Form;
use Docalist\Forms\Themes;
use Docalist\Utils;

/**
 * Edite les paramètres d'un base de données.
 *
 * @param DatabaseSettings $database La base à éditer.
 * @param int $dbindex L'index de la base.
 * @param string $error Erreur éventuelle à afficher.
 */
/* @var $database DatabaseSettings */

?>
<div class="wrap">
    <?= screen_icon() ?>
    <h2><?= __('Paramètres de la base', 'docalist-biblio') ?></h2>

    <p class="description">
        <?= __('Utilisez le formulaire ci-dessous pour modifier les paramètres de votre base de données :', 'docalist-biblio') ?>
    </p>

    <?php if ($error) :?>
        <div class="error">
            <p><?= $error ?></p>
        </div>
    <?php endif ?>

    <?php
        // Charge la liste des analyseurs disponibles
        $settings = apply_filters('docalist_search_get_index_settings', []);

        // Ne conserve que les analyseurs "texte"
        $analyzers = [];
        foreach(array_keys($settings['settings']['analysis']['analyzer']) as $analyzer) {
            if (strpos($analyzer, 'text') !== false) {
                $analyzers[] = $analyzer;
            }
        }

        $form = new Form('', 'post');

        $form->tag('h3.title', __('Paramètres généraux', 'docalist-biblio'))
             ->description(__('Options de publication de votre base de données.', 'docalist-biblio'));
        $form->input('name')
             ->attribute('class', 'regular-text')
             ->description(__('Nom de code interne de la base de données, de 1 à 14 caractères, lettres minuscules, chiffres et tiret autorisés.', 'docalist-biblio'));
        $form->select('homepage')
             ->options(pagesList())
             ->firstOption(false)
             ->description(__("Choisissez la page d'accueil de votre base. Les références auront un permalien de la forme <code>/votre/page/12345/</code>.", 'docalist-biblio'));

        $form->tag('h3.title', __('Fonctionnalités', 'docalist-biblio'))
             ->description(__('Options et fonctionnalités disponibles pour cette base.', 'docalist-biblio'));
        $form->checkbox('thumbnail');
        $form->checkbox('revisions');
        $form->checkbox('comments');

        $form->tag('h3.title', __('Indexation docalist-search', 'docalist-biblio'))
             ->description(__("Options d'indexation dans le moteur de recherche.", 'docalist-biblio'));
        $form->select('stemming')
             ->attribute('class', 'regular-text')
             ->firstOption(__('(Pas de stemming)', 'docalist-biblio'))
             ->options($analyzers);

        $form->tag('h3.title', __('Intégration dans WordPress', 'docalist-biblio'))
             ->description(__("Apparence de cette base dans le back-office de WordPress.", 'docalist-biblio'));
        $form->input('icon')
             ->attribute('class', 'medium-text')
             ->description(sprintf(
                __('Icône à utiliser dans le menu de WordPress. Par exemple %s pour obtenir l\'icône %s.<br />
                    Pour choisir une icône, allez sur le site %s, faites votre voix et recopiez le nom de l\'icône.<br />
                    Remarque : vous pouvez également indiquer l\'url complète d\'une image, mais dans ce cas celle-ci ne s\'adaptera pas automatiquement au back-office de WordPress.',
                    'docalist-biblio'),
                '<code>dashicons-book</code>',
                '<span class="dashicons dashicons-book"></span>',
                '<a href="https://developer.wordpress.org/resource/dashicons/#book" target="_blank">WordPress dashicons</a>'
            ));
        $form->input('label')
             ->attribute('class', 'regular-text');
        $form->textarea('description')
             ->attribute('rows', 2)
             ->attribute('class', 'large-text');

        $form->tag('h3.title', __('Autres informations', 'docalist-biblio'))
             ->description(__('Informations pour vous.', 'docalist-biblio'));
        $form->input('creation')
             ->attribute('disabled', true);
        $form->input('lastupdate')
             ->attribute('disabled', true);
        $form->textarea('notes')
             ->attribute('rows', 10)
             ->attribute('class', 'large-text')
             ->description(__("Vous pouvez utiliser cette zone pour stocker toute information qui vous est utile : historique, modifications apportées, etc.", 'docalist-biblio'));

        $form->submit(__('Enregistrer les modifications', 'docalist-biblio'));

        $assets=$form->assets();
        $assets->add(Themes::assets('wordpress'));
        Utils::enqueueAssets($assets); // @todo : faire plutôt $assets->enqueue()

        !isset($database->creation) && $database->creation = date_i18n('Y/m/d H:i:s');
        !isset($database->lastupdate) && $database->lastupdate = date_i18n('Y/m/d H:i:s');
        !isset($database->stemming) && $database->stemming = 'fr-text';
        !isset($database->icon) && $database->icon = 'dashicons-list-view';
        !isset($database->thumbnail) && $database->thumbnail = true;
        !isset($database->revisions) && $database->revisions = true;
        !isset($database->comments) && $database->comments = false;

        $form->bind($database)->render('wordpress');
    ?>
</div>
<script type="text/javascript">
(function($) {
    /**
     * Si la base n'a pas de slug, change le slug quand on tape le nom
     */
    $(document).ready(function () {
        $(document).on('input propertychange', '#icon', function() {
            $('#icon-preview').remove();
            $('#icon').after('<span id="icon-preview" class="dashicons ' + $('#icon').val() + '" style="padding-left: 10px;font-size: 30px;"></span>');
        });
        $('#icon').trigger('input');

        $('#name').focus();
    });
}(jQuery));
</script>
<?php
/**
 * Retourne la liste hiérarchique des pages sous la forme d'un tableau
 * utilisable dans un select.
 *
 * @return array Un tableau de la forme PageID => PageTitle
 */
function pagesList() {
    $pages = ['…'];
    foreach(get_pages() as $page) { /* @var $page \WP_Post */
        $pages[$page->ID] = str_repeat('   ', count($page->ancestors)) . $page->post_title;
    }

    return $pages;
}