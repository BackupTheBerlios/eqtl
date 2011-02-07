<?php
if(isset($_GET['targetChromosoms'] , $_GET['targetSpecies'])) {
	echo 'Hallo!';
}else{
	echo 'An error has occurred! Please correct your selections!<br />';
	echo '<INPUT TYPE=BUTTON VALUE="correct" onClick="history.back()">';
}
?>