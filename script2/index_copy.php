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

// scrap url
                        $scrap_url = "http://www.tse.com.tw/ch/trading/SBL/TWT96U/TWT96U.php";

// field delimiter in output file
                        $delimiter = ",";
                        date_default_timezone_set('Asia/Taipei');
// output filename

                        $file = "outputSBL_" .date('m-d-Y_h-i-s a'). ".csv";

// open file for writing final results
                        $handler = @fopen($file, "w");
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

    $html = file_get_html($scrap_url);

    if ($html && is_object($html) && isset($html->nodes)) {

        $items = $html->find("table tr");

// loop through items on current page
        foreach ($items as $item) {
            scrap_single($item);
        }

        $html->clear();
    }
}

function scrap_single($html) {

    global $file;
    global $delimiter;
    $handler = fopen($file, "a");

    if ($html && is_object($html) && isset($html->nodes)) {

        $td0 = get_value($html, "td", 0, "text");
        $td1 = get_value($html, "td", 1, "text");
        $td2 = get_value($html, "td", 2, "text");
        $td3 = get_value($html, "td", 3, "text");

        if ($html->find("td", 2)) {

            $csv_line = "";

                if (strlen($td0) > 2) {
                    $csv_line .= quote_string($td0." TT");
                    $csv_line .= $delimiter . quote_string($td1);
                }
                if (strlen($td2) > 2) {
                    $csv_line .= "\n" . quote_string($td2." TT");
                    $csv_line .= $delimiter . quote_string($td3);
                }

// write in file
            fwrite($handler, $csv_line . "\n");
        }

        $html->clear();
    }

    fclose($handler);
}

function quote_string($string) {
    $string = str_replace('"', "'", $string);
    $string = str_replace('&amp;', '&', $string);
    $string = str_replace('&nbsp;', ' ', $string);
    $string = preg_replace('!\s+!', ' ', $string);
    return '"' . trim($string) . '"';
}

function get_value($element, $selector_string, $index, $type = "text") {
    $value = "";
    $cont = $element->find($selector_string, $index);
    if ($cont) {
        if ($type == "href") {
            $value = $cont->href;
        } elseif ($type == "src") {
            $value = $cont->src;
        } elseif ($type == "text") {
            $value = trim($cont->plaintext);
        } elseif ($type == "content") {
            $value = trim($cont->content);
        } else {
            $value = $cont->innertext;
        }
    }

    return trim($value);
}
?>

