<?php

/**
STARTOFDOCUMENTATION

=pod

=head1 NAME

test.php - 

=head1 SYNOPSIS

=head1 DESCRIPTION

=head1 AUTHOR

Michael Brehler <brehler@informatik.uni-luebeck.de>,
Georg Zeplin <zeplin@informatik.uni-luebeck.de>,

=head1 COPYRIGHT

University of LE<uuml>beck, Germany, 2011

=cut

ENDOFDOCUMENTATION
*/

include 'fill_related_projects.php';
global $compara_array;
fill_compara_array();

var_export($compara_array);
?>
