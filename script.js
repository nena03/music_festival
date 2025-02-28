$(document).ready(function() {
    
    $('#loginForm').submit(function(event) {
        var username = $('#username').val();
        var password = $('#password').val();

        if (username === "" || password === "") {
            alert("Молимо вас да попуните сва поља.");
            event.preventDefault();
        }
    });
});
