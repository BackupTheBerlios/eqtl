<?php

/*

=head1 CLASS

Trait

=head1 DESCRIPTION

The class shall harbor the core routines implemented for traits.

=head1 ARGUMENTS

=over 4

=item traitid

=item connection

=back

=head1 AUTHOR

Steffen ME<ouml>ller <moeller@inb.uni-luebeck.de>

=head1 COPYRIGHT

Universities of Rostock and LE<uuml>beck, 2010

=cut

*/

class Trait
{
    public $trait_id;
    public $genename;
    public $chr;
    public $start;

    function retrieveFromDBviaTraitID($traitid,$connection) {

   	#print "retrieveFromDBviaTraitID($traitid,..)<br>\n";
   	$this->trait_id=$traitid; # identification of the entry

	$q="SELECT trait_id,gene_name,chromosome,start FROM trait WHERE trait_id='$traitid'";
	$resultTraitDetails = mysqli_query($connection,$q);
	if (!$resultTraitDetails){
		echo "<p>Problem with query '$q': "
			.mysqli_error($connection)."</p>";
		mysqli_close($connection);
		exit; // learn about alternative
	}
	if ($resultTraitDetailsLine = mysqli_fetch_array($resultTraitDetails,MYSQL_ASSOC)) {
		#print_r($resultTraitDetailsLine);
		$this->genename=$resultTraitDetailsLine["gene_name"];
		$this->chr=$resultTraitDetailsLine["chromosome"];
		$this->start=$resultTraitDetailsLine["start"];
		if (empty($this->chr)) $this->chr="NA";
		if (empty($this->start)) $this->start=0;
		if (empty($this->genename)) {
			#echo "empty genename: $this->genename<br>";
			$this->genename="$traitid";
		}
		else if (strlen($this->genename)>25) {
			#echo "long genename: $this->genename<br>";
			$s=preg_split("/ [^A-Za-z0-9,'-][^A-Za-z0-9,'-] /",$this->genename);
			#print_r($s);
			$this->genename="* ".$s[1];
		}
		else {
			#echo "Normal genename: $this->genename<br>";
		}
		$r = "<small>$this->trait_id</small><br><a href=\"trait.php?direct=1&traits="
			.$this->trait_id."\">".$this->genename."</a>";
		$r .="<br>";
		$r .= "$this->chr"."@".round($this->start/1000/1000,1);
		#echo("Ausgabe: $r");
		return($r);
	}
	else {
		return(FALSE);
	}
    }
}

?>
