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
 * Résumé du document.
 *
 * Remarque : cette classe devrait s'appeller "Abstract" mais c'est un mot clé
 * réservé en PHP. Abstract désigne le fait que c'est un résumé, pas une classe
 * abstraite.
 *
 * @property string $language
 * @property string $content
 */
class AbstractField extends AbstractEntity {

    protected function loadSchema() {
        // @formatter:off
        return array(
            'language' => array(
                'label' => __('Langue', 'docalist-biblio'),
                'description' => __('Langue du résumé', 'docalist-biblio'),
            ),
            'content' => array(
                'label' => __('Résumé', 'docalist-biblio'),
                'description' => __("Résumé du document.", 'docalist-biblio'),
            ),
        );
        // @formatter:on
    }
}