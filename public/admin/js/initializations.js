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
        plugins: ["paste"],
        paste_as_text: true,
        browser_spellcheck: true,
        selector: 'textarea'
    });


});





