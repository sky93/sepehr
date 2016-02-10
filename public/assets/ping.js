$(function(){
    function ping(){
        $.ajax({
            url: $('meta[property="root"]').attr("content") + '/ping',
            type: "POST",
            data: "ping=true"
        });
    }
    ping();
    window.setInterval(function(){ping();}, 30000);
});