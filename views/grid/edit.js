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
/**
 * Editeur de grilles
 */
(function($) {
    /**
     * Traque la dernière boite cliquée ou sélectionnée
     */
    var last;
    
    /**
     * Permet à l'utilisateur de déplacer les boites
     */
    $('.meta-box-sortables').sortable({
        helper: function(event, element) {
            element.addClass('closed');
            return element;
        },
        placeholder: 'sortable-placeholder .postbox .closed',
        forcePlaceholderSize: true,
        'handle': 'h2',
        containment: "parent",
        tolerance: "pointer",
        axis: 'y',
        distance: 2
    });
    
    /**
     * Affiche ou masque le formulaire du champ quand on clique sur le titre
     */
    $(document).on('click', '.grid .postbox .postbox h2, .grid .postbox .postbox .handlediv', function() {
        $(this).parent('.postbox').toggleClass('closed');
    });

    /*
     * Remarque pour le code qui suit : pour détecter en temps réel les 
     * modifications faites dans un input, on utilise les événements "input" et "propertychange".
     * - input est le seul nécessaire en html5
     * - mais pour IE, on est obligé d'ajouter propertychange.
     * Il faudrait éventuellement ajouter keyup et paste :
     * - http://stackoverflow.com/a/9042710
     * - http://www.greywyvern.com/?post=282
     */
    
    /**
     * Met à jour le titre de la postbox lorsque le libellé d'un champ est modifié.
     */
    $(document).on('input propertychange', '.label,.labelspec', function() {
        var input = $(this); // le input.label qui a changé
        var postbox = input.parents('.postbox:first') // la postbox parent
        var title = $('h2 span:first span', postbox); // le titre de la box
        title.text(input.val() || input.attr('placeholder') || $('input.name', postbox).val());
    });
    
    /**
     * Met à jour l'icone "champ restreint" (une clé) lorsque le champ "capability" est modifié.
     */
    $(document).on('input propertychange', '.capability,.capabilityspec', function() {
        var input = $(this); // le input.label qui a changé
        var postbox = input.parents('.postbox:first') // la postbox parent
        if (input.val() || input.attr('placeholder')) {
            postbox.addClass('has-cap');
        } else {
            postbox.removeClass('has-cap');
        }
    });
    
    /**
     * Mémorise la dernière boite cliquée pour permettre à add-group de savoir où insérer le nouveau groupe.
     */
    $(document).on('focusin click', '.postbox.level2', function() {
        last = $(this);
    });
    
    /**
     * Ajoute un nouveau groupe.
     */
    $('.add-group').click(function() {
        var template = $('#group-template');
        var number = $('.group').length + 1;
        while ($('.group' + number).length) ++number;
        
        var html = template.html().replace(/\{group-number\}/g, number);
        var group = $(html);
        
        group.insertAfter(last || $('.postbox.level2:last'));
        group.hide().fadeIn();
        last = group;
        $('.label', group).focus();
    });
    
    /**
     * Supprime un groupe
     */
    $(document).on('click', '.delete-group', function() {
        $(this).parents('.postbox:first').remove();
        last = null;
        
        return false; // empêche que ça bubble et que ça réinitialise last (focusin click plus haut)
    });
}(jQuery));