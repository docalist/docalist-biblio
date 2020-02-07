<?php
/**
 * This file is part of Docalist Biblio.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Biblio\Aggregation;

use Docalist\Search\Aggregation\Bucket\TermsAggregation;
use stdClass;

/**
 * Une agrégation standard de type "terms" sur le champ "language".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TermsLanguage extends TermsAggregation
{
    /**
     * Constructeur
     *
     * @param array $parameters     Autres paramètres de l'agrégation.
     * @param array $options        Options d'affichage.
     */
    public function __construct(array $parameters = [], array $options = [])
    {
        !isset($parameters['size']) && $parameters['size'] = 1000;
        !isset($options['title']) && $options['title'] = __('Langue', 'docalist-search');
        parent::__construct('filter.language.label', $parameters, $options);
    }

    /**
     * {@inheritDoc}
     */
    final public function getBucketLabel(stdClass $bucket): string
    {
        // dans la table, les libellés sont en tout minu, met la première lettre en maju
        return ucfirst(parent::getBucketLabel($bucket));
    }

}
