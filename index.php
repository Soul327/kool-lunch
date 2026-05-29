<!DOCTYPE html>
<html>
  <head>
    <title>Kool Lunches</title>
    <link rel="stylesheet" href="/res/rf-gallery.css"/>
    <?php
      require realpath($_SERVER["DOCUMENT_ROOT"])."/res/head.php";
      include realpath($_SERVER["DOCUMENT_ROOT"])."/res/secret.php";
      require_once realpath($_SERVER["DOCUMENT_ROOT"])."/res/lib.php";
      require_once realpath($_SERVER["DOCUMENT_ROOT"])."/res/Parsedown.php";


      // Grabs and lists all monitary donations to the screen, if there is any
      // error skip
      function drawMonetaryDonations() {

        $drawString = "";
        $result = [];
        // $result = Database::getCollections();
        if ($result["code"] != 110) {return;}
        $drawString .= '<div style="margin-bottom: 1em"> <h2 class="center-text" style="color: black">Monetary Donations</h2>';
        $drawStringColData = "<center><h3>Unable to retrieve donation information at this time.</h3></center>";

        $data = $result["data"];
        foreach ($data as $col) {
          if ($col["coll"] != NULL) {
            $drawStringColData = "<center><h3>".$col["coll"]."</h3></center>";
          } else {
            $drawStringColData = "<center><h3>Others</h3></center>";
          }

          $drawString .= $drawStringColData;
          $drawString .=  "<table style='margin: 0em auto;'>";

          $result = Database::getCollectionDonations($col["coll"]);

          $data = $result["data"];

          foreach($data as $row) {
            $name = $row["donatorName"];
            $amount = $row["amount"];
            $drawString.=  "
            <tr>
              <td>$$amount</td>
              <td>$name</td>
            </tr>
            ";
          }
          $drawString.=  "</table>";
        }

        $drawString.=  '</div>';

        echo $drawString;
      }

      /**
       * Loads and converts the FAQ data in to a more useable array of answer &
       * questions
       */
      function loadFAQ() {
        $data = [];
        $setting = Database::getSetting("faqText");
        $markdown = $setting["value"];

        // Split the string into an array of lines using the newline character (\n) as the delimiter
        $lines = explode("\n", $markdown);

        // Iterate over each line
        $num = 0; // If zero then nothing, if one then question, if two then answer
        $question = "";
        $answer = "";
        foreach ($lines as $line) {
          if(strpos($line, "<Question>") === 0) {
            if($num == 2) {
              // This is the start of another question append to data
              $data[] = ["question"=>$question, "answer"=>$answer];
              $question = "";
              $answer = "";
            }
            $num = 1;
            continue;
          }

          if(strpos($line, "<Answer>") === 0) {
            $num = 2;
            continue;
          }

          if($num == 1) $question .= $line."\n\n";
          if($num == 2) $answer   .= $line."\n\n";
        }

        if(strlen($question) > 0 && strlen($answer)) {
          // This is the start of another question append to data
          $data[] = ["question"=>$question, "answer"=>$answer];
          $question = ""; $answer = "";
        }

        return $data;
      }

      $Parsedown = new Parsedown();
    ?>

    <script>
      function faqClick(element) {
        let ele = $(element);
        let faqDiv = ele.parent();
        let answerEle = faqDiv.children().eq(1);
        answerEle.toggle(); // Toggles show/hide on the element
      }

      function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
      }
    </script>
  </head>

  <header>
    <?php require realpath($_SERVER["DOCUMENT_ROOT"])."/res/header.php"; ?>
  </header>

  <body>

    <div class="content">
      <!-- <iframe src="https://docs.google.com/presentation/d/e/2PACX-1vSxBW-X_1GKybMlrA-B4kD5QTEGf0UYux56FmcT3Ei7NEAMfisy6M9lkadUfErssLJBVUKpsElRjCFx/embed?start=true&loop=true&delayms=3000&amp;rm=minimal" frameborder="0" onload="resizeIframe(this)"></iframe> -->
        <div class="img-gallery"><center>
            <img id="current-img" src="" alt="Gallery loading"></center>
        </div>
        <script src="/res/rf-gallery.js"></script>
        <script>
          let galleryImages = {
            "/res/images/kl-1.jpg" : "Kool Lunches Volunteers",
            "/res/images/kl-2.jpg" : "Kool Lunches Volunteers",
            "/res/images/kl-3.jpg" : "Kool Lunches Volunteers",
            "/res/images/kl-4.jpg" : "Kool Lunches Volunteers",
            "/res/images/kl-5.jpg" : "Kool Lunches Volunteers"
          };
          let gallery = new Gallery(galleryImages, "auto", 2000);
        </script>
      <center>
        <?php
          $value = Database::getSetting("showSignUp")["value"];
          $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
          if($value) {
            echo "<a href='/sign-up' class='button'>SIGNUP</a>";
          }

          $value = Database::getSetting("showVolunteer")["value"];
          $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
          if($value) {
            echo "<a href='/volunteer-sign-up' class='button'>VOLUNTEER</a>";
          }
        ?>
      </center>

      <?php
        $setting = Database::getSetting("homePageText");
        $markdown = $setting["value"];

        echo $Parsedown->text($markdown);
      ?>
    </div>

    <div class="content">
      <h2 class="center-text" style="color: black">FAQ'S</h2>
      <div id="faq" class="faq">
        <?php
          $qnaData = loadFAQ();
          foreach($qnaData as $qna) {
            $question = $Parsedown->text( $qna["question"] );
            $answer   = $Parsedown->text( $qna["answer"] );

            echo "<div class='faqElement'>
              <div class='question' style='display: flex; justify-content: space-between;' onclick='faqClick(this)'>
                $question
                <p style='text-align: right;'>+</p>
              </div>
              <div class='answer' style='display: none'>
                $answer
              </div>
            </div>";
          }
        ?>
      </div>

      <p class="center-text" style="margin-bottom: 0em;">
        All other questions can be sent through
      </p>
      <p class="link-row">
        <a href="https://www.facebook.com/koollunches/">Facebook</a>
        <a href="https://www.remind.com/join/koollunch5">Remind</a>
        <a href="">Email</a>
      </p>
    </div>

    <div class="content">
      <!-- <iframe src="https://docs.google.com/presentation/d/e/2PACX-1vQ15Qlu6CeWJkAIDFFkFgO2MIPIco7-KkOZWg3DJfRJSrrIpordmYhTj-ZnqBoKsDhYiC8ptKGL65NG/embed?start=true&loop=true&delayms=3000&amp;rm=minimal" frameborder="0" class="section"></iframe> -->

      <?php @drawMonetaryDonations();?>
      <div class="center-text thank-you-grid">
        <div>
          <h2>Board Members</h2>
          <p>Jodi Hunt</p>
          <p>Brandy Stockton</p>
          <p>Kristy Agerlid</p>
          <p>Wendi Lindsey</p>
          <p>Steve Mohundro</p>
          <p>Phyllis Kinnaird</p>
          <p>Mary Karl</p>
          <p>Tillman Boyd</p>
        </div>

        <div>
          <h2>Volunteers</h2>
          <p>St. Elizabeth Catholic Church</p>
          <p>First Congregational Church of Bonham</p>

          <p>First Presbyterian Church</p>
          <p>Northside Church of Christ</p>
          <p>First Baptist Church</p>
          <p>Boyd Baptist Church</p>
          <p>Bethlehem Baptist Church</p>
          <p>First United Methodist Church</p>
          <p>7th and Main Baptist Church</p>

          <p>First United Methodist Church</p>
        </div>

        <div>
          <h2>Website</h2>
          <p>Walter Ozmore</p>
          <p>Rayna Fetters</p>
        </div>

        <div>
          <h2>Building</h2>
          <p>First Presbyterian Church-Bonham</p>
        </div>
      </div>
    </div>
  </body>

  <?php require realpath($_SERVER["DOCUMENT_ROOT"])."/res/footer.php"; ?>
</html>
