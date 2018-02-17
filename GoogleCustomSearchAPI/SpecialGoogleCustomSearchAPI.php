<?php
class SpecialGoogleCustomSearchAPI extends SpecialPage {
	function __construct() {
        parent::__construct( 'GoogleCustomSearchAPI' );
        
	}

	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
        $output->addModules( 'ext.GoogleCustomSearchAPI' ); 
        $formDescriptor = array(
			'myfield1' => array(
				'label-message' => 'search',
				'type' => 'text',
				'default' => '',
			),
        );
        $htmlForm = new HTMLForm( $formDescriptor, $this->getContext(), 'testform' );
        $htmlForm->setSubmitText( 'Search' );
        $htmlForm->setMethod( 'get' );
        $htmlForm->prepareForm()->displayForm( false );
                
        $param = $request->getText( 'wpmyfield1' );
        $hasSave = $request->getText( 'save' );
        if($param){
            $this->showResults( $param );
        }
        if($hasSave){
            $this->saveSearch($request->getValues());
        }
        
    } 

    public function showResults( $param ) {
        global $wgGoogleSearchKey;
        global $wgGoogleSearchCX;
        global $wgGoogleSearchEndPoint;
		if ( !$wgGoogleSearchCX  || !$wgGoogleSearchKey || !$wgGoogleSearchEndPoint) {
            return true;
        }
        $output = $this->getOutput();            
        $url = $wgGoogleSearchEndPoint;
        $data = array (
            'cx'=>$wgGoogleSearchCX,
            'key'=>$wgGoogleSearchKey,
            'q' => $param,
            'num'=>10,
            'fields'=>'items(htmlTitle,link,htmlSnippet)'
        );
        $params = '';
        foreach($data as $key=>$value){
            $params .= $key.'='.$value.'&';
        }
        $params = trim($params, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url.'?'.$params ); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 7); 
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        $results = curl_exec($ch);
    
        if(curl_errno($ch)){
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);    
        $results = json_decode($results, true);
        $output->addHTML( "<form action=\"$_SERVER[REQUEST_URI]\" method=\"POST\" id=\"form1\"> ");
        $output->addHTML( '<input type="hidden" value="save" name="save" />' );
        $output->addHTML( '<div class="searchresults">' );
        $output->addHTML( '<ul>' );
        $output->addHTML( '<ul><li><input type="checkbox" id="select_all" /> Select All<br/></li>' );
        //$results['items'] = array(array("htmlTitle"=>"ss","link"=>"ss","htmlSnippet"=>"aaa"),array("htmlTitle"=>"ss","link"=>"ss","htmlSnippet"=>"aaa"),array("htmlTitle"=>"ss","link"=>"ss","htmlSnippet"=>"aaa"));
        if(isset($results['items'])){
            foreach($results['items'] as $key=>$result){
                $title = $result['htmlTitle'];
                $link = $result['link'];
                $description = $result['htmlSnippet'];
                $output->addHTML( "<li style='margin-bottom:15px'>" );
                $output->addHTML( "<span><input type=\"checkbox\" class=\"checkbox\" name=\"select_search[]\" value=\"$key\"></span>" );
                $output->addHTML( "<div style=\"display: inline-block;\">
                <a href=\"$link\" title=\"$title\">$title</a>
                </div>" );
                $output->addHTML( "<div >$description</div> " );
                $output->addHTML( "<div ><input type=\"text\" name=\"comment_$key\" /></div> " );
                $output->addHTML( "<input type=\"hidden\" value=\"$title\" name=\"title_$key\" />" );
                $output->addHTML( "<input type=\"hidden\" value=\"$link\" name=\"link_$key\" />" );
                $output->addHTML( "<input type=\"hidden\" value=\"$description\" name=\"description_$key\" />" );
                $output->addHTML( '</li>' );
            }
        }
        $output->addHTML( '</ul>' );
        $output->addHTML( '</div>' );
        $output->addHTML( '<input type="submit" value="Save" id="submitbtn" />' );
        $output->addHTML( '</form>' );
          
    }
    
    public function saveSearch( array $request_values ) {
        $output = $this->getOutput(); 
        $user_id = $this->getUser()->getId();
        if($user_id == 0){
			$loginPage = SpecialPage::getTitleFor( 'Userlogin' );
			$output->redirect( $loginPage->getLocalURL( 'returnto=Special:GoogleCustomSearchAPI' ) );
			return;
        }
        foreach($request_values['select_search'] as $value){
            $title = htmlentities($request_values['title_'.$value]);
            $link = $request_values['link_'.$value];
            $description = htmlentities($request_values['description_'.$value]);
            $comment = htmlspecialchars(strip_tags($request_values['comment_'.$value]));
            $this->insertToDb($title, $link, $description, $comment, $user_id);
        }
        $html = Html::openElement( 'p', [
            'class' => 'message'
        ] )
        . 'saved successfully'
        . Html::closeElement( 'p' );
        $output->addHtml( $html );
    }
    
    public function insertToDb($title, $link, $description, $comment, $user_id){
        $dbw = wfGetDB( DB_MASTER );
        $dbw->insert(
            'google_custome_search_api',
            array(
                'title' => $title,
                'link' => $link,
                'decription' => $description,
                'comment' => $comment,
                'user_id' => $user_id
            ),
            __METHOD__
        );
    }
    
}
$wgSpecialPages['GoogleCustomSearchAPI'] = 'SpecialGoogleCustomSearchAPI';