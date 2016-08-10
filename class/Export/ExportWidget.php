<?php
/**
 * This file is part of the "Docalist Biblio Export" plugin.
 *
 * Copyright (C) 2015-2015 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package     Docalist\Biblio\Export
 * @author      Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Biblio\Export;

use WP_Widget;
use Docalist\Search\SearchRequest;
use Docalist\Search\SearchResults;
use Docalist\Forms\Container;

class ExportWidget extends WP_Widget
{
    public function __construct()
    {
        $id = 'docalist-biblio-export';
        parent::__construct(
            // Base ID. Inutile de préfixer avec "widget", WordPress le fait
            $id,

            // Titre (nom) du widget affiché en back office
            __('Export de notices', 'docalist-biblio-export'),

            // Args
            [
                'description' => __('Export de notices', 'docalist-biblio-export'),
                'classname' => $id, // par défaut, WordPress met 'widget_'.$id
            ]
        );
    }

    /**
     * Affiche le widget.
     *
     * @param array $context Les paramètres d'affichage du widget. Il s'agit
     * des paramètres définis par le thème lors de l'appel à la fonction
     * WordPress.
     *
     * Le tableau passé en paramètre inclut notamment les clés :
     * - before_widget : code html à afficher avant le widget.
     * - after_widget : texte html à affiche après le widget.
     * - before_title : code html à générer avant le titre (exemple : '<h2>')
     * - after_title  : code html à générer après le titre (exemple : '</h2>')
     *
     * @param array $settings Les paramètres du widget que l'administrateur
     * a saisi dans le formulaire de paramétrage (cf. {createSettingsForm()}).
     *
     * @see http://codex.wordpress.org/Function_Reference/register_sidebar
     */
    public function widget($context, $settings)
    {
        // Seuls les utilisateurs loggués peuvent exporter
        if (! is_user_logged_in()) {
            return;
        }

        // Si on n'a pas de recherche en cours, terminé
        $request = docalist('docalist-search-engine')->getSearchRequest(); /* @var $request SearchRequest */
        if (is_null($request) || ! $request->isSearch()) {
            return;
        }

        // Si on n'a pas de hits, terminé
        $results = docalist('docalist-search-engine')->getSearchResults(); /* @var $results SearchResults */
        if (is_null($results) || $results->getHitsCount() === 0) {
            return;
        }

        // TODO: à étudier, avec le widget customizer, on peut être appellé avec
        // des settings vides. Se produit quand on ajoute un nouveau widget dans
        // une sidebar, tant qu'on ne modifie aucun paramètre. Dès qu'on modifie
        // l'un des paramètres du widget, celui-ci est correctement enregistré
        // et dès lors on a les settings.
        $settings += $this->defaultSettings();

        // Début du widget
        echo $context['before_widget'];

        // Titre du widget
        $title = apply_filters('widget_title', $settings['title'], $settings, $this->id_base);
        if ($title) {
            echo $context['before_title'], $title, $context['after_title'];
        }

        // Début des liens
        $link = '<li class="%s" style="%s" title="%s"><a href="%s">%s</a></li>';
        echo '<ul>';

        // Détermine l'url de la page "export"
        $exportPage = get_permalink(docalist('docalist-biblio-export')->exportpage());

        // Lien "Exporter"
        $label = $settings['file'];
        $label && printf($link,
            'export-file',
            '',
            __("Génére un fichier d'export", 'docalist-biblio-export'),
            $exportPage,
            $label
        );

        // Lien "Biblio"
        $label = $settings['print'];
        $label && printf($link,
            'export-print',
            '',
            __('Génére une bibliographie', 'docalist-biblio-export'),
            $exportPage,
            $label
        );

        // Lien "Mail"
        $label = $settings['mail'];
        $label && printf($link,
            'export-mail',
            '',
            __("Génère un fichier d'export et l'envoie par messagerie", 'docalist-biblio-export'),
            $exportPage,
            $label
        );

        // Fin des liens
        echo '</ul>';

        // Fin du widget
        echo $context['after_widget'];
    }

    /**
     * Crée le formulaire permettant de paramètrer le widget.
     *
     * @return Fragment
     */
    protected function settingsForm()
    {
        $form = new Container();

        $form->input('title')
            ->setAttribute('id', $this->get_field_id('title')) // pour que le widget affiche le bon titre en backoffice. cf widgets.dev.js, fonction appendTitle(), L250
            ->setLabel(__('<b>Titre du widget</b>', 'docalist-biblio-export'))
            ->addClass('widefat');

        $form->input('file')
            ->setLabel(__('<b>Exporter</b>', 'docalist-biblio-export'))
            ->addClass('widefat');

        $form->input('print')
            ->setLabel(__('<b>Créer une bibliographie</b>', 'docalist-biblio-export'))
            ->addClass('widefat');

        $form->input('mail')
            ->setLabel(__('<b>Envoyer par messagerie</b>', 'docalist-biblio-export'))
            ->addClass('widefat');

        return $form;
    }

    /**
     * Retourne les paramètres par défaut du widget.
     *
     * @return array
     */
    protected function defaultSettings()
    {
        return [
            'title' => __('Export', 'docalist-biblio-export'),
            'file' => __('Générer un fichier', 'docalist-biblio-export'),
            'print' => __('Créer une bibliographie', 'docalist-biblio-export'),
            'mail' => __('Envoyer par messagerie', 'docalist-biblio-export'),
        ];
    }

    /**
     * Affiche le formulaire qui permet de paramètrer le widget.
     *
     * @see WP_Widget::form()
     */
    public function form($instance)
    {
        // Récupère le formulaire à afficher
        $form = $this->settingsForm();

        // Lie le formulaire aux paramètres du widget
        $form->bind($instance ?: $this->defaultSettings());

        // Dans WordPress, les widget ont un ID et sont multi-instances. Le
        // formulaire doit donc avoir le même nom que le widget.
        // Par ailleurs, l'API Widgets de WordPress attend des noms
        // de champ de la forme "widget-id_base-[number][champ]". Pour générer
        // cela facilement, on donne directement le bon nom au formulaire.
        // Pour que les facettes soient orrectement clonées, le champ facets
        // définit explicitement repeatLevel=2 (cf. settingsForm)
        $name = 'widget-' . $this->id_base . '[' . $this->number . ']';
        $form->setName($name);

        // Affiche le formulaire
        $form->display();
    }

    /**
     * Enregistre les paramètres du widget.
     *
     * La méthode vérifie que les nouveaux paramètres sont valides et retourne
     * la version corrigée.
     *
     * @param array $new les nouveaux paramètres du widget.
     * @param array $old les anciens paramètres du widget
     *
     * @return array La version corrigée des paramètres.
     */
    public function update($new, $old)
    {
        $settings = $this->settingsForm()->bind($new)->getData();

        // TODO validation

        return $settings;
    }
}
