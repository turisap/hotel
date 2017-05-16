/**
 * Created by HP on 16-May-17.
 */
// custom method to validate Password using regex in all forms which need that
$.validator.addMethod('validPassword',
    function (value, element, param) {
        if(value != ''){
            if(value.match(/.*[a-z]+.*/i) == null){
                return false;
            }
            if(value.match(/.*\d+.*/) == null){
                return false;
            }
        }
        return true;
    },
    'Must contain at least one letter and one number'
);