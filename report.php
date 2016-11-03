<?php
include_once 'src/config.php';
require_once ROOTPATH.'/src/GenerateReport/ReportClass.php';
$report= new ReportClass();
$selectZone=$report->getAllZone();
$mesiDisponibili=$report->getAllMounth();
if (isset($_POST["submit"])) {
    $report->exportData();
}
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <title>Report</title>
        <meta name="generator" content="Bootply" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <link href="resource/css/styles.css" rel="stylesheet">
        <link href="resource/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.min.css" />
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker3.min.css" />
        <link href="resource/css/select2.min.css" rel="stylesheet" type="text/css"/>
        <!--[if lt IE 9]>
                <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <script src="resource/js/jquery.js" type="text/javascript"></script>
        <script src="resource/js/select2.full.js" type="text/javascript"></script>
        <script src="resource/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="resource/js/moment-with-locales.js" type="text/javascript"></script>
        <script src="resource/js/bootstrap-datetimepicker.js" type="text/javascript"></script>
        <script type="text/javascript">

            var zonaparameter = '1';
            function changeFunc() {
                var selectBox = document.getElementById("sel1");
                zonaparameter = selectBox.options[selectBox.selectedIndex].value;
            }
            $(document).ready(function () {
                $(".js-data-example-ajax").select2({
                    ajax: {
                        url: "autocomplete.php",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            var queryParameters = {
                                q: params.term,
                                p: zonaparameter
                            }

                            return queryParameters;
                        },
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    },
                    escapeMarkup: function (markup) {
                        return markup;
                    }, // let our custom formatter work
                    minimumInputLength: 1,
                });
            });
        </script><script type="text/javascript">$('#alerts').hide()</script>
        <script type="text/javascript">

            function ifischeck() {
                var chknome = document.getElementById("hnome");
                //var nome=document.getElementById("hotelname");
                if (chknome.checked) {
                    document.getElementById("selezionahotel").disabled = false;

                } else {
                    document.getElementById("selezionahotel").disabled = true;
                }
            }
            function ifischeckdal() {
                var chknome = document.getElementById("chekdal");
                //var nome=document.getElementById("hotelname");
                if (chekdal.checked) {
                    document.getElementById("dal").readOnly = false;
                    document.getElementById("al").readOnly = false;

                } else {
                    document.getElementById("dal").readOnly = true;
                    document.getElementById("dal").value = '';
                    document.getElementById("al").readOnly = true;
                    document.getElementById("al").value = '';
                }
            }
            function ifischeckRM() {
                var chknome = document.getElementById("selromemilano");

                //var nome=document.getElementById("hotelname");
                if (chknome.checked) {
                    zonaparameter = '9';
                    document.getElementById("sel2").disabled = false;
                    document.getElementById("sel1").disabled = true;

                } else {
                    //zonaparameter='1';
                    document.getElementById("sel2").disabled = true;
                    document.getElementById("sel1").disabled = false;
                    selexBox = document.getElementById("sel1");
                    zonaparameter = selexBox.options[selexBox.selectedIndex].value;
                }
            }
        </script>
    </head>
    <body style="overflow-y: scroll;">
        <div class="container">

            <h1 class="text-muted">Report RtCorp</h1>
            <hr/>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="reportForm">

                <div class="form-group" style="position: relative">
                    <label for="sel1">Seleziona mese </label>
                    <select class="form-control" id="selmese" name="selmese">
                        <?php echo $mesiDisponibili?>

                    </select>
                </div>


                <div class="form-group">
                    <label for="sel1">Seleziona zona </label>
                    <select class="form-control" id="sel1" name="selzone" onchange="changeFunc();">

                       <?php echo $selectZone?>

                    </select>
                </div>
				
                <div >
                    <label>

                        <input type="checkbox" id="selromemilano" onclick="ifischeckRM();"  /> <b>Seleziona zona personalizzata</b> 
                    </label>
                </div>
                <div class="form-group">
                    <select class="form-control" id="sel2" name="selzone" disabled="true">
                        <option value="10">Zona Rome-Milano</option>

                    </select>
				</div>
				  <div class="form-group">
                    <label>Seleziona Sito</label>
                    <div class="radio">
                        <label> <input type="radio" name="Sito" value="Booking" checked="checked">Booking</label>

                        <label><input type="radio" name="Sito" value="Expedia.it">Expedia</label>
						<label><input type="radio" name="Sito" value="">Both</label>
                    </div>
                 </div>
                <div >
                    <label>
                        <input type="checkbox" id="hnome" onclick="ifischeck();" /> <b>Nome Hotel</b> 
                    </label>
                </div>
                <div class="form-group">
                    <select class=" form-control js-data-example-ajax"  name="selhotel" id="selezionahotel" disabled="true" >
                        <option value="3620194" selected="selected">seleziona</option>
                    </select>
                     <!--   <input type="text" class="form-control" name="hotelname" id="hname"  required="" />-->
                </div>

                <div >
                    <label>
                        <input type="checkbox" id="chekdal" onclick="ifischeckdal();" /> <b>Dal-Al(si consiglia massimo 45 giorni / o filtrare per nome hotel per periodi piu estesi)</b> 
                    </label>
                </div>
                <div class="form-group" style="position: relative">
                    <label for="dal">Dal</label>
                    <input type="text" class="form-control" id="dal" name="dal"  required readonly="true">
                </div>

                <div class="form-group" style="position: relative">
                    <label for="al">Al</label>
                    <input type="text" class="form-control" id="al" name="al" required readonly="true">
                </div>
                <div class="form-group">
                    <label>Seleziona data filter</label>
                    <div class="radio">
                        <label> <input type="radio" name="Date" value="ReportDate" checked="checked">Data Creazione Report</label>

                        <label><input type="radio" name="Date" value="ArrivalDate">Data Soggiorno</label>
                    </div></div>
                <input type="submit" name="submit" class="btn btn-success" value="Invia"/>
            </form>

        </div>
        <script type="text/javascript">   $(function () {
                $('#dal').datetimepicker({format: "YYYY-MM-DD"});
                $('#al').datetimepicker({format: "YYYY-MM-DD"});
            });
        </script>
        <script>
//$("#reportForm").validate();
        </script>
    </body>
</html>

