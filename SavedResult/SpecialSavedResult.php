<?php
class SpecialSavedResult extends SpecialPage {
	function __construct() {
        parent::__construct( 'SavedResult' );
        
	}

	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
        $output->addModules( 'ext.SavedResult' ); 
        $user_id = $this->getUser()->getId();
        if($user_id == 0){
			$loginPage = SpecialPage::getTitleFor( 'Userlogin' );
			$output->redirect( $loginPage->getLocalURL( 'returnto=Special:SavedResult' ) );
			return;
        }
        $action = $request->getText( 'action' );
        $hasSave = $request->getText( 'save' );
        $download_csv = $request->getText( 'download_csv' );
        
        if($action == "update"){
            $this->updateResult( $request->getValues(), $user_id );
        }elseif($action == "delete"){
            $this->deleteResult( $request->getValues(), $user_id );
        }
        $this->showResults($user_id);

        if($download_csv){
            $this->downloadCsv($user_id);
        }
    } 
    public function updateResult( array $request_values, $user_id ) {
        $id = $request_values['id'];
        $comment = $request_values['comment'];
        $dbw = wfGetDB( DB_MASTER );
        $result = $dbw->update(
            'google_custome_search_api',
            [ 'comment' => htmlspecialchars(strip_tags($comment)) ],
            [
                'id' => $id,
                'user_id' => $user_id 
            ],
            __METHOD__
        );
    }
    public function deleteResult( array $request_values, $user_id ) {
        $id = $request_values['id'];
        $dbw = wfGetDB( DB_MASTER );
        $result = $dbw->delete(
            'google_custome_search_api',
            [
                'id' => $id,
                'user_id' => $user_id 
            ],
            __METHOD__
        );
    }
    public function showResults( $user_id ) {
        $dbr = wfGetDB( DB_REPLICA );
        $output = $this->getOutput();
		$result = $dbr->select(
			'google_custome_search_api',
            [ 'id','title', 'link', 'decription','comment','user_id' ],
			[ 'user_id' => $user_id ],
			__METHOD__
        );
        if($dbr->numRows( $result )){
            $output->addHTML( '<div class="searchresults">' );
            $output->addHTML( '<ul> ' );
            foreach ( $result as $row ) {
                $id = $row->id;
                $title = html_entity_decode($row->title);
                $link = $row->link;
                $description = html_entity_decode($row->decription);
                $comment = $row->comment;
                $output->addHTML( "<li style='margin-bottom:15px'>" );
                $output->addHTML( "<div style=\"display: inline-block;\">
                <a href=\"$link\" title=\"$title\">$title</a>
                </div>" );
                $output->addHTML( "<div >$description</div> " );
                $output->addHTML( "<form action=\"$_SERVER[REQUEST_URI]\" method=\"POST\" id=\"form_$id\"> ");
                $output->addHTML( '<input type="hidden" value="update" name="action" id="action" />' );
                $output->addHTML( "<div ><input type=\"text\" name=\"comment\" value=\"$comment\" /></div> " );
                $output->addHTML( "<input type=\"hidden\" value=\"$id\" name=\"id[]\" />" );
                $output->addHTML( '<input type="submit" value="Update Comments" class="updatebtn" />' );
                $output->addHTML( '<input type="button" value="Delete" class="deletebtn" />' );
                $output->addHTML( '</form>' );
                $output->addHTML( '</li>' );
            }
            $output->addHTML( '</ul>' );
            $output->addHTML( '</div>' );
            $output->addHTML( "<form action=\"$_SERVER[REQUEST_URI]\" method=\"POST\" id=\"csv_form\"> ");
            $output->addHTML( '<input type="hidden" value="download_csv" name="download_csv" id="download_csv" />' );
            $output->addHTML( '<input type="submit" value="download all links as csv" id="csvbtn" />' );
            $output->addHTML( '</form>' );
        }else{
            $output->addWikiText( "no saved result");
        }
    }
     
    public function downloadCSV( $user_id ) {
        $dbr = wfGetDB( DB_REPLICA );
        $output = $this->getOutput();
		$result = $dbr->select(
			'google_custome_search_api',
            [  'link' ],
			[ 'user_id' => $user_id ],
			__METHOD__
        );
        $csv_array = array();
        if($dbr->numRows( $result )){
            foreach ( $result as $row ) {
                $link = '"'.$row->link.'"';
                $csv_array[] = array($link);
            }
        }else{
            $output->addWikiText( "no saved result");
        }
        $this->outputCSV($csv_array);
    }
    
    public function outputCSV($data,$file_name = 'saved_result.csv') {
         header("Content-Disposition: attachment; filename=$file_name");
         header("Cache-Control: no-cache, no-store, must-revalidate");
         header("Pragma: no-cache");
         header("Content-Type: application/csv");
         header("Expires: 0");
         header("Content-Transfer-Encoding: binary\n");
         $output = fopen("php://output", "w");
         foreach ($data as $row) {
             fputcsv($output, $row); 
         }
         fclose($output);
         exit;
     }


}
$wgSpecialPages['SavedResult'] = 'SpecialSavedResult';