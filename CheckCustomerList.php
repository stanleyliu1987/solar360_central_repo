<?php
include('includes/session.inc');
include('includes/DefineInvCartClass.php');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


if(isset($_GET['custname']) or $_GET['branchname'] or $_GET['billingemail']){
    
$CustomerMatchName = strtoupper(preg_replace("/\&(.*?)(amp);/", '', trim($_GET['custname'])));
$BranchMatchName = strtoupper(preg_replace("/\&(.*?)(amp);/", '', trim($_GET['branchname'])));
$EmailMatchName = strtoupper(preg_replace("/\&(.*?)(amp);/", '', trim($_GET['billingemail'])));
    
//insert wildcard characters in spaces
$SearchCustString = '%' . str_replace(' ', '%', str_replace("  "," ",$CustomerMatchName)) . '%';
$SearchBranString = '%' . str_replace(' ', '%', str_replace("  "," ",$BranchMatchName)) . '%';
$SearchEmailString = '%' . str_replace(' ', '%', str_replace("  "," ",$EmailMatchName)) . '%';

$CustomerSQL = "SELECT custbranch.brname,
							custbranch.contactname,
							custbranch.phoneno,
							custbranch.faxno,
							custbranch.branchcode,
							custbranch.debtorno,
							debtorsmaster.name
						FROM custbranch
						LEFT JOIN debtorsmaster
						ON custbranch.debtorno=debtorsmaster.debtorno
						WHERE debtorsmaster.name " . LIKE . " '" . $SearchCustString . "' or 
                                                      custbranch.brname ".LIKE."'".$SearchBranString."' or 
                                                      custbranch.email ".LIKE."'".$SearchEmailString."'"; 

		$ErrMsg = _('The Customer cannot be retrieved because');
                $addnewCustLink=$rootpath . '/Customers.php?';
		$CustomerResult = db_query($CustomerSQL,$db,$ErrMsg);
                $newCust = '<div align="right"><a href="'.$addnewCustLink.'">Add a New Customer</a></div>';    
                echo $newCust;
          if (isset($CustomerResult) and DB_num_rows($CustomerResult)>0) {
                echo '<table><tr>';
		echo '<div class="page_help_text">' . _('Check Customer Result') . '</div><br />';
		echo '<br /><tr>
                                <th>' . _('Customer Code') . '</th>
				<th>' . _('Customer') . '</th>
				<th>' . _('Branch') . '</th>
				<th>' . _('Contact') . '</th>
				<th>' . _('Phone') . '</th>
				<th>' . _('Fax') . '</th>
				</tr>';

            $TempgroupId=0;     
            
            while ($myrow=DB_fetch_array($CustomerResult)) {
            
			echo '<tr class="OddTableRows">
                              <td>' .  $myrow['debtorno'].' '.$myrow['branchcode']. '</td>
                              <td>'.$myrow['name'].'</td>
                              <td>'.$myrow['brname'].'</td>
			      <td>'.$myrow['contactname'].'</td>
                              <td>'.$myrow['phoneno'].'</td>  
                              <td>'.$myrow['faxno'].'</td> </tr>';
             
		} /* end of loop around items */    
               echo '</table>'; 
        }
        
        else{

         echo  _('This Customer does not exist in ERP, Please click abovre link to create one'.'<p>');
        
        }
}

 
              
?>
