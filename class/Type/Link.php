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
 */
namespace Docalist\Biblio\Type;

use Docalist\Type\MultiField;
use InvalidArgumentException;

/**
 * Lien internet.
 *
 * @property Docalist\Type\TableEntry $type
 * @property Docalist\Type\Url $url
 * @property Docalist\Type\Text $label
 * @property Docalist\Type\DateTime $date
 */
class Link extends MultiField {
    static public function loadSchema() {
        return [
            'editor' => 'table',
            'fields' => [
                'type' => [
                    'type' => 'Docalist\Type\TableEntry',
                    'table' => 'table:links',
                    'label' => __('Type', 'docalist-biblio'),
                    'description' => __('Type de lien', 'docalist-biblio'),
                ],
                'url' => [
                    'type' => 'Docalist\Type\Url',
                    'label' => __('Adresse', 'docalist-biblio'),
                    'description' => __('Url complète du lien', 'docalist-biblio'),
                ],
                'label' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Libellé', 'docalist-biblio'),
                    'description' => __('Texte à afficher', 'docalist-biblio'),
                ],
                'date' => [
                    'type' => 'Docalist\Type\DateTime',
                    'label' => __('Accédé le', 'docalist-biblio'),
                    'description' => __('Date', 'docalist-biblio'),
                ],
/*
                'lastcheck' => [
                    'type' => 'Docalist\Type\DateTime',
                    'label' => __('Lien vérifié le', 'docalist-biblio'),
                    'description' => __('Date de dernière vérification du lien', 'docalist-biblio'),
                ],
                'status' => [
                    'type' => 'Docalist\Type\Text',
                    'label' => __('Statut', 'docalist-biblio'),
                    'description' => __('Statut du lien lors de la dernière vérification.', 'docalist-biblio'),
                ]
*/
            ]
        ];
    }

    public function assign($value)
    {
        if (is_array($value)) {
            unset($value['lastcheck']);
            unset($value['status']);
        }

        return parent::assign($value);
    }

    public function getDefaultFormat()
    {
        return 'link';
    }

    public function getAvailableFormats()
    {
        return [
            'url'       => 'Url',
            'label'     => 'Libellé',
            'link'      => 'Libellé cliquable',
            'urllink'   => 'Url cliquable',
            'labellink' => 'Type et libellé cliquable',
            'embed'     => 'Incorporé si possible, libellé cliquable sinon',
        ];
    }

    public function getFormattedValue($options = null)
    {
        $format = $this->getOption('format', $options, $this->getDefaultFormat());
        switch ($format) {
            case 'url':         return $this->formatUrl($options);
            case 'label':       return $this->formatLabel($options);
            case 'link':        return $this->formatLink($options);
            case 'urllink':     return $this->formatUrlLink($options);
            case 'labellink':   return $this->formatLabelLink($options);
            case 'embed':       return $this->formatEmbed($options);
        }

        throw new InvalidArgumentException("Invalid link format '$format'");
    }

    /**
     * Format 'url'.
     *
     * Retourne l'url formattée ou une chaine vide si on n'a pas d'url.
     *
     * @param mixed $options
     *
     * @eturn string
     */
    private function formatUrl($options = null)
    {
        return isset($this->url) ? $this->formatField('url', $options) : '';
    }

    /**
     * Format 'label'.
     *
     * Retourne le libellé formatté si on a un libellé, le type formatté sinon.
     *
     * @param mixed $options
     *
     * @eturn string
     */
    private function formatLabel($options = null)
    {
        return $this->formatField(isset($this->label) ? 'label' : 'type', $options);
    }

    /**
     * Format 'link'.
     *
     * Retourne un lien cliquable (tag <a>) en utilisant le libellé (ou le type) comme libellé.
     * La date du lien (si dispo) est indiquée sous forme de bulle d'aide.
     *
     * @param mixed $options
     *
     * @return string
     */
    private function formatLink($options = null)
    {
        if (isset($this->date)) {
            $title = sprintf(__('Lien consulté le %s', 'docalist-biblio'), $this->formatField('date', $options));
            $format = '<a href="%1$s" title="%3$s">%2$s</a>';
        } else {
            $title = '';
            $format = '<a href="%1$s">%2$s</a>';
        }

        return sprintf($format,
            esc_attr($this->formatUrl($options)),
            esc_html($this->formatLabel($options)),
            esc_attr($title)
        );
    }

    /**
     * Format 'urllink'.
     *
     * Retourne un lien cliquable (tag <a>) en utilisant l'url comme libellé.
     * Le libellé (ou le type si on n'a aucun libellé) et la date du lien (si disponibles) sont
     * indiqués sous forme de bulle d'aide.
     *
     * @param mixed $options
     *
     * @return string
     */
    private function formatUrlLink($options = null)
    {
        $url = $this->formatUrl($options);
        $title = $this->formatLabel($options);
        if (isset($this->date)) {
            $title .= ' ';
            $title .= sprintf(__('(lien consulté le %s)', 'docalist-biblio'), $this->formatField('date', $options));
        }

        return sprintf('<a href="%1$s" title="%3$s">%2$s</a>',
            esc_attr($url),
            esc_html($url),
            esc_attr($title)
        );
    }

    /**
     * Format 'labellink'.
     *
     * @param mixed $options
     *
     * @return string
     */
    private function formatLabelLink($options = null)
    {
        return $this->formatUrl($options) . ' : ' . $this->formatLabel($options); // insécable avant ':'
    }

    /**
     * Format 'embed'.
     *
     * @param mixed $options
     *
     * @return string
     */
    private function formatEmbed($options = null)
    {
        global $wp_embed; /* @var $wp_embed WP_Embed */

        $url = $this->formatField('url', $options);

        $sav = $wp_embed->return_false_on_fail;
        $wp_embed->return_false_on_fail = true;
        $result = $wp_embed->shortcode(['width' => '480', 'height' => '270'], $url);
        $wp_embed->return_false_on_fail = $sav;

        return $result ?: $this->formatLink($options);

        // Remarque : ce serait plus simple d'utiliser wp_oembed_get($url) mais ça ne gère que les
        // sites qui gèrent oEmbed, pas les providers enregistrés avec wp_embed_register_handler().
    }

    public function filterEmpty($strict = true) {
        // Supprime les éléments vides
        $empty = parent::filterEmpty();

        // Si tout est vide ou si on est en mode strict, terminé
        if ($empty || $strict) {
            return $empty;
        }

        // Retourne true si on n'a pas d'url
        return $this->filterEmptyProperty('url');
    }
}