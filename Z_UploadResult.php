<?php
/* $Id: Z_UploadResult.php 4213 2010-12-22 14:33:20Z tim_schofield $*/

//$PageSecurity=15;

include('includes/session.inc');

$title=_('File Upload Result');

include('includes/header.inc');


prnMsg( _('The file') . ' ' . $HTTP_POST_FILES['userfile']['name'] . ' ' . _('was uploaded to the server in the /tmp directory and has been renamed temp'),'info');

move_uploaded_file($HTTP_POST_FILES['userfile']['tmp_name'], '/tmp/temp');

include('includes/footer.inc');

?>