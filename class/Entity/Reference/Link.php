<?php
/**
 * This file is part of a "Docalist Biblio" plugin.
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 *
 * @package Docalist
 * @subpackage Biblio
 * @author Daniel Ménard <daniel.menard@laposte.net>
 * @version SVN: $Id$
 */
namespace Docalist\Biblio\Entity\Reference;

use Docalist\Data\Entity\AbstractEntity;

/**
 * Lien internet.
 *
 * @property string $type
 * @property string $url
 * @property string $label
 * @property string $date
 * @property string $lastcheck
 * @property string $checkstatus
 */
class Link extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'type' => array(
                'label' => __('Type', 'docalist-biblio'),
                'description' => __('Type de lien', 'docalist-biblio'),
            ),
            'url' => array(
                'label' => __('Adresse', 'docalist-biblio'),
                'description' => __('Url complète du lien', 'docalist-biblio'),
            ),
            'label' => array(
                'label' => __('Libellé', 'docalist-biblio'),
                'description' => __('Texte à afficher', 'docalist-biblio'),
            ),
            'date' => array(
                'label' => __('Accédé le', 'docalist-biblio'),
                'description' => __('Date à laquelle le documentaliste a accédé à la ressource', 'docalist-biblio'),
            ),
            'lastcheck' => array(
                'label' => __('Lien vérifié le', 'docalist-biblio'),
                'description' => __('Date de dernière vérification du lien', 'docalist-biblio'),
            ),
            'status' => array(
                'label' => __('Statut', 'docalist-biblio'),
                'description' => __('Statut du lien lors de la dernière vérification.', 'docalist-biblio'),
            )
        );
        // @formatter:on
    }
}