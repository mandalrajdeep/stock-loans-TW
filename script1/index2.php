<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <link rel="stylesheet" type="text/css" href="style.css" />
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
        <script type="text/javascript">
            $(function () {
                $('#run').click(function () {
                    $(this).fadeOut();
                    $("#script_status").fadeIn();
                    $("#script_finished").remove();
                });
            });
        </script> 
    </head>
    <body>			
        <div id="container" >
            <div id="wrapper">
                <div id="form">
                    <form  action="" autocomplete="on" method="post" > 
                        <div id="input_submit">
                            <span class="button"> 
                                <input type="submit" name="run" id="run" value="Run script" /> 
                            </span>
                            <div id="script_status" >
                                <div id="progress_bar" >
                                    <p>Scraping is in progress...</p>
                                    <img src="progress_bar.gif" />
                                </div>
                            </div>
                        </div> 

                        <input name="submited" value="submited" type="hidden" />
                    </form>
                    <?php

                    if (isset($_POST["run"]) && ($_POST["submited"] == "submited")) {

// set display errors status
                        ini_set('display_errors', 1); // 1-turn on all error reporings 0-turn off all error reporings
                        error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

// change max execution time to unlimitied
                        ini_set('max_execution_time', 0);

// include simple html dom parser
                        require "simple_html_dom.php";

// base url
                        $base_url = "";

// scrap url
                        $scrap_url = "http://mis.twse.com.tw/stock/api/getStockSblsCap.jsp";

// field delimiter in output file
                        $delimiter = ",";
// Set Time Zone
                        
                        date_default_timezone_set('Asia/Taipei');
// output filename

                        $file = "output_" .date('m-d-Y_h-i-s a'). ".csv";

// open file for writing final results
                        $handler = @fopen($file, "w");

                        $header = "Stock Code" . $delimiter .
                                "Real Time Available Volume for SBL Short Sales" . $delimiter .
                                "Last Modify" . $delimiter .
                                "\n";

                        fwrite($handler, $header);
                        fclose($handler);

                        scrap_page($scrap_url);

                        echo "<div id='script_finished'><br /><hr /><b>Scraping is finished!</b>";
                        echo "<hr />
                             <p>You can download output here <a href='" . $file . "' >Output file</a></p>
                             <hr /></div>";
                    }
                    ?> </div>
            </div>
        </div>
    </body>
</html>

<?php

// define functions
function scrap_page($scrap_url) {

    global $base_url;
    $next_page = "";

    $html = file_get_html($scrap_url);

    if ($html && is_object($html) && isset($html->nodes)) {


        $json_decoded = json_decode($html, TRUE);
        $items = $json_decoded["msgArray"];

// loop through items on current page
        foreach ($items as $item) {
            scrap_single($item);
        }

        $html->clear();
    }

    return $next_page;
}

function scrap_single($item) {

    global $file;
    global $delimiter;
    $handler = fopen($file, "a");

    $csv_line = "";

    $csv_line .= quote_string($item["stkno"]) . " TT";
    $csv_line .= $delimiter . quote_string($item["slblimit"]);
    $csv_line .= $delimiter . quote_string($item["txtime"]);

// write in file
    fwrite($handler, $csv_line . "\n");

    fclose($handler);
}

function quote_string($string) {
    $string = str_replace('"', "'", $string);
    $string = str_replace('&amp;', '&', $string);
    $string = str_replace('&nbsp;', ' ', $string);
    $string = preg_replace('!\s+!', ' ', $string);
    return '"' . trim($string) . '"';
}
?>

