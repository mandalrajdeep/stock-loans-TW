<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <link rel="stylesheet" type="text/css" href="style.css" />
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
        <script type="text/javascript">
            function startTime() {
                var today = new Date();
                var h = today.getHours();
                var m = today.getMinutes();
                var s = today.getSeconds();
                m = checkTime(m);
                s = checkTime(s);
                document.getElementById('time').innerHTML =
                h + ":" + m + ":" + s;
                var t = setTimeout(startTime, 500);
                checkMorning(h,m,s);
                checkEvening(h,m,s);

            }
            function checkTime(i) {
                if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
                return i;
            }
            function checkReload() {
                if (i == 00 && j == 05 && k == 00) {
                    location.reload ();
                };
                if (i == 12 && j == 05 && k == 00) {
                    location.reload ();
                };
            }
            function checkMorning(i,j,k) {
                if (i == 07 && j == 00 && k == 00) {
                    //startScraping();
                    document.getElementById('run').click();
                    };  // add zero in front of numbers < 10
            }
            function checkEvening(i,j,k) {
                if (i == 18 && j == 31 && k == 00) {
                    document.getElementById('run').click();
                    };  // add zero in front of numbers < 10
            }
            $(function () {
                $('#run').click(function () {
                    startScraping();
                });
            });
            function startScraping() {
                $(this).fadeOut();
                $("#script_status").fadeIn();
                $("#script_finished").remove();

            }

        </script> 
    </head>
    <body onload="startTime()">			
        <div id="container" >
            <div id="wrapper">
                <div id="form">
                    <form  action="" autocomplete="on" method="post" > 
                        <div id="time">XX</div>
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

// write in database
    $dbhost = '127.0.0.1:8889';
    $dbuser = 'root';
    $dbpass = 'root';
    $dbname = "stockdata";
    $current_date = date("y-m-d h:i:s a");


    $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 
    //echo "Connected successfully";


    if ($html && is_object($html) && isset($html->nodes)) {

        $td0 = get_value($html, "td", 0, "text");
        $td1 = get_value($html, "td", 1, "text");
        $td2 = get_value($html, "td", 2, "text");
        $td3 = get_value($html, "td", 3, "text");

        if ($html->find("td", 2)) {

            $csv_line = "";
            $stock1 = "";
            $stock2 = "";
            $sbl1 = "";
            $sbl2= "";
            $db_insert = 'INSERT INTO sbl_info (ticker, sbl_qty, created_at) values (?,?,?)';


                if (strlen($td0) > 2) {
                    $csv_line .= quote_string($td0." TT");
                    $csv_line .= $delimiter . quote_string($td1);
                    $stock1 = $td0." TT";
                    $sbl1 = intval(str_replace(",", "", $td1));

                    if ( strlen($td1) < 15) {
                    $db_insert_query1 = mysqli_prepare($conn, $db_insert);
                    mysqli_stmt_bind_param($db_insert_query1, 'sis', $stock1,  $sbl1, $current_date);
                    if (!mysqli_stmt_execute($db_insert_query1)) {
                        echo("Error description: " . mysqli_error($conn));
                    }
                    }                    

                }
                if (strlen($td2) > 2 && strlen($td3) < 15 ){
                    $csv_line .= "\n" . quote_string($td2." TT");
                    $csv_line .= $delimiter . quote_string($td3);
                    $stock2 = $td2." TT";
                    $sbl2 = intval(str_replace(",", "", $td3));

                    
                    $db_insert_query2 = mysqli_prepare($conn, $db_insert);
                    mysqli_stmt_bind_param($db_insert_query2, 'sis', $stock2,  $sbl2, $current_date);
                    if (!mysqli_stmt_execute($db_insert_query2)) {
                        echo("Error description: " . mysqli_error($conn));
                    }
                    

                }

// write in file
            fwrite($handler, $csv_line . "\n");
        }

        $html->clear();
    }

    fclose($handler);
    mysqli_close($conn);

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

