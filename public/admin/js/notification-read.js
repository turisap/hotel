/**
 * Created by HP on 09-Jun-17.
 */
$(document).ready(function () {

    $('.set-read').click(function () {

        // get notification id from link id attribute
        var notification_id = $(this). attr('id');


        // set picture as the main one
        $.ajax({
            url: '/admin/notifications/set-as-read',
            data: {
                notification_id : notification_id
            },
            type: 'POST',
            success: function(data){

                if(!data.error){
                    alert(notification_id);
                    location.reload(true);
                }

            }
        });

    })

});