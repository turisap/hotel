/**
 * Created by HP on 09-Jun-17.
 */
$(document).ready(function () {




    $('.set-read').click(function () {

        // get notification id from link id attribute
        var notification_id = $(this);
        var idValue = notification_id.attr('id');


        // set picture as the main one
        $.ajax({
            url: '/admin/notifications/set-as-read',
            data: {
                notification_id : idValue
            },
            type: 'POST',
            success: function(data){

                if(!data.error){

                    // get value of notification counter and set less by one
                    var counter = $('#notificationCount');
                    var value = parseInt(counter.text());
                    counter.text(Math.abs(value - 1));


                    // remove notification from the dropdown
                    var notification = notification_id.parents('.notification-container');
                    //alert(JSON.stringify(notification, null, 4));
                    notification.remove();

                   checkCounter();


                }

            }
        });



    });



  checkCounter();


});


function checkCounter(){
    // get counter element and its value in order to remove it if its equal 0
    var counter = $('#notificationCount');
    var counterValue = counter.text();
    // remove red counter if it's equal to 0 on document ready
    if (counterValue == 0){
        counter.remove();
    }

}