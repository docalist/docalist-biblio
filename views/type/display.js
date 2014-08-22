/**
 * This file is part of the 'Docalist Biblio' plugin.
 *
 * Copyright (C) 2012, 2013 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */
/*
 * Gère la liste des champs pour un type de notice.
 */
(function($) {
    // Traque la dernière boite cliquée ou sélectionnée
    var last;
    var sortableSettings = {
        // helper: 'clone',
        connectWith: '.children',
        placeholder: 'sortable-placeholder',
        forcePlaceholderSize: true,
        opacity: 0.65
    };
    
    function createGroup(format, parent) {
        var group = $($('#group-template').html()).appendTo(parent);
        $('h3 span', group).text('Groupe');

        $('[name=label]', group).val(format.label);
        $('[name=content]', group).val(format.content);
        $('[name=default]', group).val(format.default);
        $('[name=before]', group).val(format.before);
        $('[name=after]', group).val(format.after);
        $('[name=row]', group).val(format.row);
        $('[name=between]', group).val(format.between);
        
        parent = $('.children', group);
        for (var i in format.children) {
            var child = format.children[i];
            
            if (typeof child === 'string') child = {field: child};
            
            if ('undefined' === typeof child.field) {
                createGroup(child, parent);
            } else {
                createField(child, parent);
            }
        }
        
        $(parent).sortable(sortableSettings);

    }
    
    function createField(format, parent) {
        if (typeof format == 'string') {
            format = {
                field: format
            }
        }
        var field = $($('#field-template').html()).appendTo(parent);
        $('h3 span', field).text(format.field);
        
        $('[name=for]', field).val(format.for);
        $('[name=label]', field).val(format.label);
        $('[name=content]', field).val(format.content);
        $('[name=default]', field).val(format.default);
        $('[name=before]', field).val(format.before);
        $('[name=after]', field).val(format.after);
    }
    
    // Permet à l'utilisateur d'ajouter des champs dans l'éditeur
    $('.add-field').click(function() {
        var field = $(this).data('field');
        console.log('Ajouter', field);
    });
    
    // Permet à l'utilisateur de déplacer les boites
    //$('.children').sortable(sortableSettings);
    
    // Affiche ou masque le formulaire du champ quand on clique sur le titre
    $(document).on('click', '.postbox h3, .postbox .handlediv', function() {
        console.log(this);
        $(this).parent('.postbox').toggleClass('closed');
    });
    

    // Initialisation
    console.log(format);
    createGroup(format, $('#post-body-content'));
}(jQuery));
