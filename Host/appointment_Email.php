<?php
// session_start();
// require_once '../includes/db.php';
// require_once '../phpqrcode/qrlib.php';
// require_once '../includes/erp_layout.php';
// date_default_timezone_set('Asia/Kolkata');

 require '../vendor/autoload.php';  // Path to the Composer autoload file

 use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// $mail = new PHPMailer(true);

// try {
//     // Server settings
//     $mail->isSMTP(); // Set mailer to use SMTP
//     $mail->Host       = 'smtp.gmail.com'; // Set Gmail's SMTP server
//     $mail->SMTPAuth   = true; // Enable SMTP authentication
//     $mail->Username   = 'shirkeprajakta62@gmail.com'; // Your Gmail email address
//     $mail->Password   = 'pmvn jscl qwud ggyk'; // Use your Gmail App Password (not your Gmail password)
//     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
//     $mail->Port       = 587; // Use port 587 (or 465 for SSL)

//     // Recipients
//     $mail->setFrom('shirkeprajakta62@gmail.com', 'host'); // Sender's email and name
//     $mail->addAddress('sanikaparitkar@gmail.com', 'prajakta'); // Recipient's email and name
//     // $mail->addReplyTo('your_email@gmail.com', 'Mailer'); // Optionally, add a reply-to address

//     // Content
//     $mail->isHTML(true); // Set email format to HTML
//     $mail->Subject = 'Test Email from PHPMailer via Gmail SMTP'; // Email subject
//     $mail->Body    = 'This is a test email sent via PHPMailer using Gmail SMTP!'; // Email body
//     $mail->AltBody = 'This is the plain text version of the email content.'; // Plain text body (for non-HTML clients)

//     // Send the email
//     if ($mail->send()) {
//          echo json_encode(['status' => 'success', 'message' => 'Appointment booked successfully!']);
//          exit;
//     }
// } catch (Exception $e) {
//     echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
// }


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"] ?? "";
    $email = $_POST["email"] ?? "";
    $Mobile = $_POST["Mobile"] ?? "";
    $company= $_POST["company"] ?? "";
    $whom_to_meet=$_POST["whom_to_meet"]?? "";
    $Host_name= $_POST["Host_name"] ?? "";
    $Purpose= $_POST["Purpose"] ?? "";
    $Appointment_Date_Time= $_POST["Appointment_Date_Time"] ?? "";
    $Number_of_People= $_POST["Number_of_People"] ?? "";
    $Pass_Number= $_POST["Pass_Number"] ?? "";

    // echo $name.'<br>';
    // echo $email.'<br>';
    // echo $Mobile.'<br>';
    // echo $company.'<br>';
    // echo $whom_to_meet.'<br>';
    // echo $Host_name.'<br>';
    // echo $Purpose.'<br>';
    // echo $Appointment_Date_Time.'<br>';
    // echo $Pass_Number.'<br>';
    // echo $Number_of_People.'<br>';
    // exit();
    
    if (empty($name)) {
        echo "Missing name!";
        exit;
    }
    if (empty($email)) {
        echo "Missing email!";
        exit;
    }
    if (empty($Mobile)) {
        echo "Missing Mobile!";
        exit;
    }
    if (empty($company)) {
        echo "Missing company!";
        exit;
    }
    if (empty( $whom_to_meet)) {
        echo "Missing whom_to_meet!";
        exit;
    }
    if (empty($Host_name)) {
        echo "Missing Host_name!";
        exit;
    }
    if (empty($Purpose)) {
        echo "Missing Purpose!";
        exit;
    }
    if (empty($Appointment_Date_Time)) {
        echo "Missing Appointment_Date_Time!";
        exit;
    }
    if (empty($Pass_Number)) {
        echo "Missing Pass_Number!";
        exit;
    }
    if (empty($Number_of_People)) {
        echo "Missing Number_of_People!";
        exit;
    }


    // Example: pretend to send email
    // mail($email, $subject, $message);

    //-----------------------
    //Email Sending Logic
    //-----------------------
$email_sent = false;
// if ($appointment) {
    $mail = new PHPMailer(true);
    // try {
    //     //Server settings
    //     $mail->isSMTP();
    //     $mail->Host = 'smtp.yourmailserver.com'; // Change this to your organization's SMTP server
    //     $mail->SMTPAuth = true;
    //     $mail->Username = 'shirkeprajakta62@gmail.com'; // Your email address
    //     $mail->Password = 'pmvn jscl qwud ggyk'; // Your email password or app password
    //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    //     $mail->Port = 587;

    //     //Recipients
    //     $mail->setFrom('shirkeprajakta62@gmail.com', 'host');
    //     $mail->addAddress($email,$name); // Recipient email

    //     // Content
    //     $mail->isHTML(true);
    //     $mail->Subject = 'Appointment Confirmation'; 
    //     $mail->Body = "Hello";
    //      $mail->AltBody="Hii";
        // $mail->Body = "
        //     <h3>Hello {$name},</h3>
        //     <p>Your appointment has been successfully booked.</p>
        //     <p><strong>Appointment Details:</strong></p>
        //     <ul>
        //         <li><strong>email</strong> {$email}</li>
        //         <li><strong>Mobile</strong> {$Mobile}</li>
        //         <li><strong>company</strong> {$company}</li>
        //         <li><strong>whom_to_meet</strong> { $whom_to_meet}</li>
        //         <li><strong>whom_to_meet</strong> { $Host_name}</li>
        //         <li><strong>Appointment_Date_Time</strong> {$Appointment_Date_Time}</li>
        //         <li><strong> Number_of_People</strong> { $Number_of_People}</li>
        //         <li><strong>Pass_Number</strong> {$Pass_Number}</li>
        //         <li><strong>Purpose</strong> {$Purpose}</li>
        //     </ul>
        //     <p>Please reach on time. We look forward to seeing you!</p>
        //     <p>Best regards,<br>Your Organization</p>
        // ";
        // $mail->AltBody = "Hello {$name}, your appointment details are as follows:\n"
            // . "email: {$email}\n"
            // . "Mobile {$Mobile}\n"
            // . "whom_to_meet: {$whom_to_meet}\n"
            // . "Company: {$company}\n"
            // . "Host_name: {$Host_name}\n"
            // . "Number_of_People: {$Number_of_People}\n"
            // . "Pass_Number: {$Pass_Number}\n"
            // . "Purpose: {$Purpose}\n"
            // . "Appointment_Date_Time: {$Appointment_Date_Time}\n\n"
            // . "Please reach on time. We look forward to seeing you!\n\nBest regards, Your Organization";

    //     $mail->send();
    //     $email_sent = true;
    // } catch (Exception $e) {
    //     error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    // }
// }
$email_sent = false;
try {
    // Server settings
    $mail->isSMTP(); // Set mailer to use SMTP
    $mail->Host       = 'smtp.gmail.com'; // Set Gmail's SMTP server
    $mail->SMTPAuth   = true; // Enable SMTP authentication
    $mail->Username   = 'shirkeprajakta62@gmail.com'; // Your Gmail email address
    $mail->Password   = 'pmvn jscl qwud ggyk'; // Use your Gmail App Password (not your Gmail password)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port       = 587; // Use port 587 (or 465 for SSL)

    // Recipients
    $mail->setFrom('shirkeprajakta62@gmail.com', 'host'); // Sender's email and name
    $mail->addAddress($email, $name); // Recipient's email and name
    // $mail->addReplyTo('your_email@gmail.com', 'Mailer'); // Optionally, add a reply-to address

    // Content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = 'Appointment Confirmation'; // Email subject
     $mail->Body = "
            <h3>Hello {$name},</h3>
            <p>Your appointment has been successfully booked.</p>
            <p><strong>Appointment Details:</strong></p>
            <ul>
                <li><strong>email</strong> {$email}</li>
                <li><strong>Mobile</strong> {$Mobile}</li>
                <li><strong>company</strong> {$company}</li>
                <li><strong>whom_to_meet</strong> {$whom_to_meet}</li>
                <li><strong>whom_to_meet</strong> {$Host_name}</li>
                <li><strong>Appointment_Date_Time</strong> {$Appointment_Date_Time}</li>
                <li><strong> Number_of_People</strong> {$Number_of_People}</li>
                <li><strong>Pass_Number</strong> {$Pass_Number}</li>
                <li><strong>Purpose</strong> {$Purpose}</li>
            </ul>
            <p>Please reach on time. We look forward to seeing you!</p>
            <p>Best regards,<br>PDVS</p>
        ";
        $mail->AltBody = "Hello {$name}, your appointment details are as follows:\n"
            . "email: {$email}\n"
            . "Mobile {$Mobile}\n"
            . "whom_to_meet: {$whom_to_meet}\n"
            . "Company: {$company}\n"
            . "Host_name: {$Host_name}\n"
            . "Number_of_People: {$Number_of_People}\n"
            . "Pass_Number: {$Pass_Number}\n"
            . "Purpose: {$Purpose}\n"
            . "Appointment_Date_Time: {$Appointment_Date_Time}\n\n"
            . "Please reach on time. We look forward to seeing you!\n\nBest regards, PDVS";

    // Send the email
    if ($mail->send()) {
         $email_sent = true;
    }
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
if( $email_sent){
echo "Email sent successfully to $email!";
} else {
    echo "Invalid request!";
}
}
?>