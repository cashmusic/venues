<?php
// process.php

session_start();
/**************************************************************
*
*       Database Connection
*
**************************************************************/
require_once('config.php');

try {
  $db = new PDO($CONNECTION, $USERNAME, $PASSWORD);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $e) {
  echo $e->getMessage();
  exit;
}


$errors         = array();      // array to hold validation errors
$data           = array();      // array to pass back data

// validate the variables ======================================================
    // if any of these variables don't exist, add an error to our $errors array

    if (empty($_POST['venuename']))
        $errors['venuename'] = 'Name is required.';

    if (empty($_POST['address1']))
        $errors['address1'] = 'Address is required.';

// return a response ===========================================================

    // if there are any errors in our errors array, return a success boolean of false
    if ( ! empty($errors)) {

        // if there are items in our errors array, return those errors
        $data['success'] = false;
        $data['errors']  = $errors;
    } else {

        // if there are no errors process our form, then return a message

        // DO ALL YOUR FORM PROCESSING HERE
        // THIS CAN BE WHATEVER YOU WANT TO DO (LOGIN, SAVE, UPDATE, WHATEVER)

        //Katy Proces to updated DB
        $venuename = $_POST['venuename'];
        $address1 = $_POST['address1'];
        $address2 = $_POST['address2'];
        $city = $_POST['city'];
        $region = $_POST['region'];
        $country = $_POST['country'];
        $postalcode = $_POST['postalcode'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $url = $_POST['url'];
        $phone = $_POST['phone'];
        $type = $_POST['type'];
        $UUID = $_POST['UUID'];
      
         try {
          
           $sql = "UPDATE venues SET name = ?, address1 = ?,address2 = ?,city = ?,region = ?,country = ?,postalcode = ?,latitude = ?, longitude = ?, url = ?,phone = ?,type = ? WHERE UUID = ?";
                      
           $q = $db->prepare($sql);
           $q->execute(array($venuename,$address1,$address2,$city,$region,$country,$postalcode,$latitude, $longitude, $url,$phone,$type,$UUID));
                  
          

           } catch(Exception $e) {
              echo $e->getMessage();
              exit;
           }

        // show a message of success and provide a true success variable
        $data['success'] = true;
        $data['message'] = 'Success!';
    }

    // return all our data to an AJAX call
    echo json_encode($data);

?>