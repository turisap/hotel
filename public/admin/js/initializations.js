/**
 * Created by HP on 04-Jun-17.
 */
$(document).ready(function () {


    // bootstrap confirmation buttons initializing
    $('[data-toggle=confirmation]').confirmation({
        rootSelector: '[data-toggle=confirmation]'
    });



    // TINYMCE TEXTAREA PLUGIN
    tinymce.init({
        forced_root_block : "",
        browser_spellcheck: true,
        plugins: 'powerpaste',
        menu: {
            edit: {title: 'edit', items: 'pastetext'}
        }
    });


});



