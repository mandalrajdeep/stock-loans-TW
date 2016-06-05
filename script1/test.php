<?php/*
variable initialization
*/
if(!isset($_POST['submit']))
{
echo '<div id="cond"><p>This is a bulk subscription form, if you are looking for individual applications, <a href="http://www.kidsagepune.com/subscribe/"> click here </a></p>
<p>Please enter all details correctly so that we can contact you. Once you have filled your details, you will be contacted within 48 hours of filing the application.</p><p></p></div>'; 
}  
else
{
echo '<div id="cond"><p>Thank you for your interest in Kid\'s Age.</p>
<p>Our representative will touch base with you shortly in the next 48 hours, alternatively you can reach out to us at kidsagepune@gmail.com. Kindly mention the order number in that case.</p><p></p></div>'; 
}

//Payment Gateway
global $order_id, $price;
/*
global $key, $hash_key, $productinfo, $surl, $furl, $service_provider, $txnid;

  $key = "0pxXL8zf"; #"CiSBlXab";
  $hash_key = "4IAI1OSaKB"; #"8tjvviuBwD";
  $productinfo = "Children Newspaper";
  $surl = "http://www.kidsagepune.com/status/";
  $furl = "http://www.kidsagepune.com/status/";
  $service_provider = "payu_paisa";
*/

//Database
global $servername, $username, $password, $dbname;

  $servername = "localhost";
  $username = "radhika89";
  $password = "Trickster123";
  $dbname = "kidsagedb";

//Form Data
global  $first_name, $last_name, $institution, $type, $contact1, $contact2, $email, $address, $city, $state, $pincode, $junior_subs, $senior_subs, $year, $referral, $payment, $comment;

  /*
  Template Name: Contact Form
  */

  function formData()
  {
global $price;
global  $first_name, $last_name, $institution, $type, $contact1, $contact2, $email, $address, $city, $state, $pincode, $junior_subs, $senior_subs, $year, $referral, $payment, $comment;

    $first_name = strtolower(trim($_POST["first_name"]));
    $last_name = strtolower(trim($_POST["last_name"]));
    $institution = trim($_POST["institution"]);
    $type = trim($_POST["type"]);
    $contact1 = trim($_POST["contact1"]);
    $contact2 = trim($_POST["contact2"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);
    $city = trim($_POST["city"]);
    $state = trim($_POST["state"]);
    $pincode = trim($_POST["pincode"]);
    $year = trim($_POST["year"]);
    $junior_subs = trim($_POST["junior_subs"]);
    $senior_subs = trim($_POST["senior_subs"]);
    $referral = trim($_POST["referral"]);
    $payment = trim($_POST["payment"]);
    $comment = trim($_POST["comment"]);  
    $price = 0;

    if ($year == "One") {
      $price = 300;
      $sub_years = 1;
    }
    elseif ($year == "Two") {
      $price = 550;
      $sub_years = 2;
    }

    $price = $price * ($senior_subs + $junior_subs);   
  }

  function dbInsert()
  {
    global $servername, $username, $password, $dbname;
    global $order_id, $price;
global  $first_name, $last_name, $institution, $type, $contact1, $contact2, $email, $address, $city, $state, $pincode, $junior_subs, $senior_subs, $year, $referral, $payment, $comment;


    $form_sql = 'INSERT INTO bulk_data (first_name, last_name, institution, type, contact1, contact2, email, address, city, state, pincode, referral, payment_mode, comment)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn) {
      echo "Error: Unable to connect to MySQL." . PHP_EOL;
      echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
      exit;
    }


   /*
    echo "Success: A proper connection to MySQL was made! The my_db database is great." . PHP_EOL;
    echo "Host information: " . mysqli_get_host_info($conn) . PHP_EOL;
   */

    $form_sql_query = mysqli_prepare($conn, $form_sql);

    mysqli_stmt_bind_param($form_sql_query, 'ssssssssssssss', $first_name, $last_name, $institution, $type, $contact1, $contact2, $email, $address, $city, $state, $pincode,  $referral, $payment, $comment);

    if (!mysqli_stmt_execute($form_sql_query))
    {
      echo("Error description: " . mysqli_error($conn));
    }
    else{
      $order_id = "BOP" . mysqli_insert_id($conn);
      echo "<div id=\"cond\"><p>Your application has been received: <font style=\"color:red\">No. ". $order_id . ". </font>Your order details are as follows:</p>
<!--<ul style=\"list-style-type:none\">
    <li>No. of Jr Subscriptions: ". $junior_subs . "</li>
    <li>No. of Sr Subscriptions: ". $senior_subs . "</li>
    <li style=\"color:red\">Total Amount Due: &#8377;". $price . "</li>
</ul>-->
<br>";
    }
    mysqli_close($conn);

}
  function sendEmail()
  {
global  $first_name, $last_name, $institution, $type, $contact1, $contact2, $email, $address, $city, $state, $pincode, $junior_subs, $senior_subs, $year, $referral, $payment, $comment;

      $to = "kidsagepune@gmail.com"; // this is your Email address
      $from = $email; // this is the sender's Email address
      $subject = "Form Submission";
      $subject2 = "Copy of Your Form Submission";
      $message = $first_name . " " . $last_name . " wrote the following:" . "\n\nInstitution: " 
      . $institution . "\nType: " . $type . "\nContact: " . $contact1 . " / " . $contact2 . "\nEmail: "
      . $email . "\nAddress: " . $address . " " . $city . " " . $state . "" . $pincode . 
      . "\nReferred By: " . $referral .
      "\nPayment Mode: " . $payment . "\nComments: " . $comment . "\n\nThis email just lists your order details. It is not to be treated as a receipt.";


      $message2 = "Here is a copy of your message " . $message ;
      $paymsg ="";
      $headers = "From:" . $from;
      $headers2 = "From:" . $to;
      mail($to,$subject,$message,$headers);
      mail($from,$subject2,$message2,$headers2); // sends a copy of the message to the sender

  }

/*
Start of display() functiom
*/
  function display()
  {
    formData();
    dbInsert();
    if(isset($_POST['submit']))
    {
       sendEmail();
    }
  }

/*
End of display() functiom
*/

  if(isset($_POST['submit']))
  {
     display();
  } 
  
    /*
    If the email was sent, show a thank you message
    Otherwise show form
    */
  if($emailSent) 
  {
    echo '
    <div class="thanks">
    <h1>Thanks, '.$name. '.$emailSent  </h1>
    <p>Your email was successfully sent. I will be in touch soon.</p>
    </div> ';
  } 
 
  if(!isset($_POST['submit']))
  {
    echo '
    <form action="" id="contactForm" method="post" onsubmit="return validateForm()">
    <label>First name*: </label>
    <input type="text" name="first_name" id="first_name" autocorrect="off" required><br>
    <label>Last name*: </label>
    <input type="text" name="last_name" id="last_name" autocorrect="off" required><br>
    <label>Institution Name: </label>
    <input type="text" name="institution" autocorrect="off"><br>
    <label>Institution Type: </label>
    <select name="type">
    <option disabled selected value> -- Select -- </option>
    <option value="school">School</option>
    <option value="coaching">Coaching Class</option>
    <option value="group">Parent Groups</option>
    <option value="camp">Summer Camp</option>
    <option value="other">Other</option>
    </select><br>
    <label>Contact Number*: </label>
    <input type="text" name="contact1" id="contact1" required><br>
    <label>Alternate Contact Number:</label>
    <input type="text" name="contact2"><br>
    <label>Email*: </label>
    <input type="text" name="email" id="email" autocorrect="off"><br>
    <label>Address: </label>
    <textarea form= "contactForm" name="address" autocorrect="off"></textarea><br>
    <label>City*: </label>
    <input type="text" name="city" autocorrect="off" required><br>
    <label>State: </label>
    <input type="text" name="state" autocorrect="off"><br>
    <label>Pincode: </label>
    <input type="text" name="pincode"><br>
    <!--<label>Number of Jr Subscriptions: </label><br>
    <input type="number" name="junior_subs" value="1">
    <label>Number of Sr Subscriptions: </label><br>
    <input type="number" name="senior_subs" value="1">
    <label>Subscription Plan*:</label><br>
    <input type="radio" name="year" id="one" value="One" checked>&emsp;<label for="one" style="display:inline;">1 year (&#8377; 300.00)</label>
    <input type="radio" name="year" id="two" value="Two">&emsp;<label for="two" style="display:inline;">2 years (&#8377; 550.00)</label>
    <br>-->
    <label>The Person who referred you to Kids Age (if any): </label><br>
    <input type="text" name="referral" ><br>
    <label>Preferred Payment Mode: </label>
    <select name="payment">
    <option disabled selected value> -- Select -- </option>
    <option value="online">Online</option>
    <option value="bank">Bank Transfer</option>
    <option value="cheque">Cheque/DD (+ &#8377; 25)</option>
    <option value="cash">Cash</option>
    <option value="notsure">Not Sure</option>
    </select><br>
    <label>Message/Comment: </label>
    <textarea form="contactForm" name="comment" autocorrect="off"></textarea><br>
    <div id="cond">*Fields marked are compulsory<br>
    </div>
    <input type="submit" value="submit" name="submit" >
    </form> ';
  }

  ?>