/**
 * Created by HP on 15-May-17.
 */

$(document).ready(function () {
    // time out for fading flash messages
    setTimeout(fadeMessage, 3000);
});

// fading out of flash messages
function fadeMessage() {
    $('.flash_message').fadeOut(500);
}