var base_url = document.getElementById('ajax_script').getAttribute('data-base_url');
function loadAjax()
{
    $('#restore').click(function()
        {
            $.ajax(
                {
                    url: base_url + 'index.php/ajax/restore',
                    type: 'POST',
                    data: {id: 1},
                    datatype: 'json',
                    error: function()
                    {
                        alert('something went wrong.');
                    },
                    success: function(j)
                    {
                        if(j == "200 OK")
                        {
                            console.log("Associations restored.");
                            checkShow();
                        }
                        else
                        {
                            console.log("There was an error.");
                            alert("Cannot contact the APIs.");
                        }
                    }
                }
            );
        });
        $("#transfer").click(function()
        {
               $.ajax(
                {
                    url: base_url + 'index.php/ajax/sync',
                    type: 'POST',
                    data: {id: 1},
                    datatype: 'json',
                    error: function()
                    {
                        alert('something went wrong.');
                    },
                    success: function(j)
                    {
                        console.log("Timesheets transfered.");
                        $( "#transfer" ).show();
                        checkShow();
                    }
                }
            )
        });
        $( "#loading" ).hide();
        $( "#check" ).hide();
        $( document ).ajaxStart(function() {
            $( "button" ).prop("disabled",true);
            $( "#loading" ).show();
            $( "#check" ).hide();});
            
        $( document ).ajaxStop(function() {
            $( "button" ).prop("disabled",false);
            $( "#loading" ).hide();
            });
        function checkShow(){
            $( "#check" ).show();
            setTimeout(function()
            {
                $( "#check" ).hide();
            }, 3000);
        }
    }
    loadAjax();