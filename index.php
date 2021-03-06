<?php
	require "hw5.php";

?>
<main>
	<div class="wrapper-main">
		<section class="section-default">
			<?php
				if(isset($_SESSION['userID'])){
					echo '<p class="login-status">You are logged in!</p>';
				}
				else{
					echo '<p class="login-status">You are logged out!</p>';
				}
			?>
		</section>
	</div>
</main>


<?php 
	require_once 'login.php';

	$conn = new mysqli($hn, $un, $pw, $db);
    if($conn->connect_error) {
        die("<h4 style='color:red'>There was a problem connecting to mySQL: </h4>" . $conn->connect_error);
    }

    $createUserContentTable = "CREATE TABLE IF NOT EXISTS userContent (
               contentId int(10) AUTO_INCREMENT PRIMARY KEY NOT NULL ,
               contentName VARCHAR(64) NOT NULL,
               fileContent VARCHAR(256) NOT NULL,
               idUsers int NOT NULL,
         
               FOREIGN KEY(idUsers) REFERENCES userCred(idUsers)
            )";
    if($conn->query($createUserContentTable) === FALSE) {
        die("<h4 style='color:red'>There was a problem creating the user database</h4>");
    }

     function closeConnectionAndExit($message, &$conn) {
        $conn->close();
        exit("<h4 style='color:red'>$message</h4>");
    }


	echo <<<_END
    	<html>
    		<head>
    			<body>
    				<form enctype="multipart/form-data" action="index.php" method="POST">
    					<p>Upload your file</p>
    						<input type="file" name="file"></input><br />
    						<input type="submit" value="Upload"></input>
    				</form>
    			</body>
    		</head>
    	</html>
    _END;


    if($_FILES && is_uploaded_file($_FILES['file']['tmp_name']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $name = $_FILES['file']['name'];
        $name = strtolower(preg_replace("/[^A-Za-z0-9.]/", "", $name));
        
        if($_FILES['file']['type'] == "text/plain" ) {
            move_uploaded_file($_FILES['file']['tmp_name'], $name);
            
            $queryString = file_get_contents($name);
            if(trim($queryString) == "") {
                closeConnectionAndExit("The textfile is empty.", $conn);
            }
            else{
                 $open = fopen($name,'r');
                 while (!feof($open)) 
                 {
                   $getTextLine = fgets($open);
                      
                     $qry = "INSERT INTO userContent (contentName, fileContent, idUsers) VALUES ('".$name."', '".$getTextLine."', '".$_SESSION['userID']."')";
                     mysqli_query($conn,$qry);
                }

                fclose($open);
                }
                
                echo "<h4 style='color:green'>Text file successfully uploaded!!</h4>";
            }
            else{
                closeConnectionAndExit("Only plain text documents are allowed. $name is not an accepted file.", $conn);
            }
        }
      
    $sql = "SELECT * FROM userContent WHERE idUsers ='".$_SESSION['userID']."'";
     $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<br> File name: ". $row["contentName"]. " - Content: ". $row["fileContent"];
        }
    } else {
        echo "0 results";
    }
        
	$conn->close();

?>
