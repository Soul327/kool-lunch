<!DOCTYPE html>
<html>
  <head>
    <title>Sign Up | KoolLunches</title>

    <?php
      require realpath($_SERVER["DOCUMENT_ROOT"])."/res/head.php";
      require_once realpath($_SERVER["DOCUMENT_ROOT"])."/res/lib.php";
    ?>

    <script>
      function showMiddleSec() {
        $("#topSec").hide();
        $("#middleSec").show();
        $("#bottomSec").hide();
      }

      function showBottomSec() {
        if(checkAdults() == false) return;

        $("#topSec").hide();
        $("#middleSec").hide();
        $("#bottomSec").show();
      }

      /**
       * Checks the amount of adults currently shown, if there is only one adult
       * then don't let the user delete them by disabling the delete button.
       */
      function preventLastAdultDeletion() {
        console.log( pickerUppers.length );
        if(pickerUppers.length <= 1) {
          pickerUppers[0].removeButton.attr('disabled', 'disabled');
        } else {
          for(let pickerUpperLinks of pickerUppers) {
            pickerUpperLinks.removeButton.removeAttr('disabled');
          }
        }
      }


      /**
       * Added a pickup individual to the form. Creates a pickupObject and
       * appends it to the pickup object array.
       *
       * TODO: Add a way for individuals to signup using other contact methods,
       *   but we need to check with Brandy first + make sure that an automatic
       *   email system works.
       */
      function addPickerUpper() {
        let div = $("<div>", {class: "content"});

        // Create an object of all the inputs and append it to the array to use
        // later for the submit button
        let pickerUpperLinks = {
          nameInput: $("<input>", {type: "text", placeholder: "John Doe"}),
          phoneInput: $("<input>", {type: "tel", placeholder: "(123) 456-7890", pattern: "[0-9]{11}"}),
          remindSelection:  $("<input>", {type: "checkbox"}),
          removeButton: $("<button>").text("Remove this individual").click(()=>{
            // Remove the div
            div.remove();

            // Remove the element from the array
            pickerUppers = pickerUppers.filter(item => item !== pickerUpperLinks);

            preventLastAdultDeletion();
          }),
        };
        pickerUppers.push(pickerUpperLinks);

        div.append(
          $("<div>", {class: "section"}).append(
            $("<label>").text("Name of individual"), pickerUpperLinks.nameInput,
          ),
          $("<div>", {class: "section"}).append(
            $("<label>").text("Phone Number"), pickerUpperLinks.phoneInput,
          ),
          $("<div>", {class: "section"}).append(
            pickerUpperLinks.remindSelection, $("<label>", {style: "display: inline"}).text("I would like updates via remind"),
          ),
          $("<center>").append( pickerUpperLinks.removeButton ),
        );
        $("#pickerUppersDiv").append(div);
        preventLastAdultDeletion();
      }


      /**
       * Checks wether or not the adults are all fill out correctly, if they are
       * not then we indicate that the input is required
       *
       * @returns bool true if good, false if bad
       */
      function checkAdults() {
        if(pickerUppers.length < 1) {
          displayAlert({title: "Error", "text": "At least one adult has to signup."});
          return false;
        }

        for(let links of pickerUppers) {
          // Check name input
          if(links.nameInput.val().length <= 0) {
            displayAlert({title: "Error", "text": "Adults need names."});
            return false;
          }

          // Check phone number
          if(links.phoneInput.val().length <= 0) {
            displayAlert({title: "Error", "text": "Phone numbers are required."});
            return false;
          }

          // Removed due to the phone number of "(903) 039-1039" being denied
          // Also this did not stop the form from continuing
          // if (links.phoneInput.val().length > 11) {
          //   displayAlert({title: "Error", "text": "Please enter a ten or eleven digit phone number."});
          // }
        }
      }

      /**
       * Check if the misc section is filled out correctly
       *
       * $returns bool true if good, false if bad
       */
      function checkMisc() {
        // Check if there is a pickup day
        let hasPickupDay =
          $("#pickupMon").is(':checked') ||
          $("#pickupTue").is(':checked') ||
          $("#pickupWed").is(':checked') ||
          $("#pickupThu").is(':checked')
        ;
        if(hasPickupDay == false) {
          displayAlert({title: "Error", "text": "At least one pickup day must be selected."});
          return false;
        }

        // Check if they have a location selected
        if( $('input[name="location"]:checked').val() === undefined ) {
          displayAlert({title: "Error", "text": "A pickup location must be given."});
          return false;
        }

        // if they have checked allergies make them list the alergies
        if( $("#hasAllergies").is(":checked") && $("#allergies").val().length <= 0 ) {
          displayAlert({title: "Error", "text": "Please list all allergies"});
          return false;
        }

        // All looks good
        return true;
      }

      async function submit() {
        if(checkMisc() == false) return;

        $("#submit-button")
          .prop("disabled", true)
          .text("Submitting...")
        ;

        // This is the json obj that will be submitted to the server
        let submitObj = {};

        // Grab the adults
        submitObj.adults = [];
        for(let links of pickerUppers) {
          let adult = {};

          adult.name = links.nameInput.val();
          adult.phoneNumber = links.phoneInput.val();
          adult.wantsRemind = (links.remindSelection.is(":checked"))? 1: 0;

          submitObj.adults.push(adult);
        }

        // Grab the misc section
        submitObj.allowPhotos = $("#pickupMon").is(":checked")
        submitObj.lunchesNeeded = $("#lunchesNeeded").val();
        submitObj.allergies = $("#allergies").val();
        submitObj.pickupLocation = $("input[name='location']:checked").val();
        submitObj.pickupDays = {
          Mon: ($("#pickupMon").is(":checked")),
          Tue: ($("#pickupTue").is(":checked")),
          Wed: ($("#pickupWed").is(":checked")),
          Thu: ($("#pickupThu").is(":checked")),
        };

        // Post the data to the server
        console.log("Submitting:", submitObj);
        let msg = await post("/ajax/su.php", submitObj);
        console.log("Response:", msg);

        // Display job done
        if(msg === 0) {
          displayAlert({
            title: "Thank You",
            text: "<center>Your form has been submitted.<br><br>Your name has been added to our list and you start picking up on the next pick up day.</center>", // TODO: Write message here
            onClose: ()=>{ window.location.href = '/'; }
          });
        } else {
          $("#submit-button")
            .prop("disabled", false)
            .text("Sign Up")
          ;

          displayAlert({
            title: "Unknown Error",
            text: "Something went wrong, please try again later",
            onClose: ()=>{}
          });
        }
      }

      var pickerUppers = [];
      $(document).ready(function() {
        // Later on we will fetch this info from the database
        let locationRadioDiv = $("#location-radio");

        let locations = ["Pizza Hut", "Simpson Park", "Powder Creek Park", "T.E.A.M. Center Housing Authority"];
        for(let location of locations) {
          locationRadioDiv.append(
            $("<input>", {type: "radio", value: location, name: "location"}),
            $("<p>", {style: "display: inline"}).text(location),
            $("<br>")
          );
        }

        // Add a trigger for allergies checkbox
        let checkbox = $("#hasAllergies").change(function() {
          if ($(this).is(':checked')) {
            $('#allergy-div').show();
          } else {
            $('#allergy-div').hide();
          }
        });


        addPickerUpper();
      });
    </script>

    <style>
      .subdiv {
        margin-left: 2em;
      }

      .error {
        color: red;
        margin: 0px;
        padding: 0px;
      }
    </style>
  </head>

  <header>
    <?php
      include realpath($_SERVER["DOCUMENT_ROOT"])."/res/header.php";
    ?>
  </header>

  <body>
    <!-- Top section of the signup form -->
    <div class="topSec" id="topSec">
      <div class="content">
        <center><h1>Sign Up</h1></center>

        <center>
          <p><b><u>Dates served are June 1st - July 30th Monday-Thursdays</u></b></p>
        </center>
        <p>Sack lunches will be prepared for pick up at local parks and areas in Bonham. Lunches will be four days a week, Monday through Thursday, with the exception of July 4th in order to observe Independence Day.</p>
        <p>If you are interested in your child or children participating please fill out the questionnaire and submit.</p>
        <p>If you have any questions, please contact The Kool Lunches Program at thekoollunchesprogram@gmail.com or message us on Facebook @ Kool Lunches.</p>
        <br>
        <p>I understand that I do not let the Kool Lunches Program know before 10:55 that I will not be picking up lunches that day, my name will removed until I contact the Kool Lunches Program to begin receiving lunches again.</p>
      </div>
      <center>
        <button onclick="showMiddleSec()" class="large-button">I understand</button>
      </center>
    </div>

    <div id="middleSec" class="form" style="display: none;">
      <center>
        <div style="max-width: 75%">
          <h2>Picker Uppers</h2>
          <p>People listed here will be able to pickup the meals</p>
        </div>
      </center>
      <div id="pickerUppersDiv"></div>
      <center>
        <button type="submit" class="large-button" onclick="showBottomSec();">Continue</button>
        <button type="submit" class="large-button" onclick="addPickerUpper();">Add Adult</button>
      </center>
    </div>

    <div id="bottomSec" class="form" style="margin-top: 1em; display: none;">
      <div class="content">
        <div class="section">
          <label># Of Lunches Needed</label>
          <input type="number" id="lunchesNeeded" min=1 value=1 required>
        </div>

        <div class="section">
          <input type="checkbox" id="photoConsent"> <label style="display: inline">Allow photos?</label>
        </div>

        <!-- Somehow t -->
        <div class="section">
          <input type="checkbox" id="hasAllergies"> <label style="display: inline">One or more of my kids have allergies</label> <br>
          <div id="allergy-div" style="margin-top: .5em; display: none">
            <label>Please list all allergies</label>
            <input type="text" id="allergies" placeholder="Blakely is allergic to books, Taylor can not have bananas." style="width: 100%">
          </div>
        </div>


        <div class="section">
          <label>Pickup Days</label>
          <input type="checkbox" id="pickupMon"> <p style="display: inline">Mon</p> <br>
          <input type="checkbox" id="pickupTue"> <p style="display: inline">Tue</p> <br>
          <input type="checkbox" id="pickupWed"> <p style="display: inline">Wed</p> <br>
          <input type="checkbox" id="pickupThu"> <p style="display: inline">Thu</p> <br>
        </div>

        <div class="section">
          <label>Pickup Location</label>
          <div id="location-radio"></div>
        </div>
      </div>

      <center>
        <button type="submit" class="large-button" onclick="showMiddleSec();">Back</button>
        <button type="submit" class="large-button" onclick="submit();" id="submit-button">Sign Up</button>
      </center>
    </div> <!-- Form -->

    <div class="content" id="submission" style="display: none;">
      Thank you for your submission.
    </div>
  </body>

  <?php require realpath($_SERVER["DOCUMENT_ROOT"])."/res/footer.php"; ?>
</html>