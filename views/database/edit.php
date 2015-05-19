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

        $homePageDescription = sprintf(
            __("
                Choisissez la page d'accueil de votre base.
                Les références auront un permalien de la forme <code>%s/votre/page/12345/</code>.",
                'docalist-biblio'
            ),
            home_url()
        );

        $form = new Form('', 'post');
        $form->input('name')->attribute('class', 'regular-text');
        $form->select('homepage')->options(pagesList())->firstOption(false)->description($homePageDescription);
        $form->input('label')->attribute('class', 'regular-text');
        $form->textarea('description')->attribute('rows', 2)->attribute('class', 'large-text');
        $form->checkbox('thumbnail');
        $form->checkbox('revisions');
        $form->checkbox('comments');
        $form
            ->select('stemming')
            ->attribute('class', 'regular-text')
            ->firstOption(__('(Pas de stemming)', 'docalist-biblio'))
            ->options($analyzers);
        $form->input('icon')->attribute('class', 'medium-text');
        $form->textarea('notes')->attribute('rows', 10)->attribute('class', 'large-text');
        $form->input('creation')->attribute('disabled', true);
        $form->input('lastupdate')->attribute('disabled', true);
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