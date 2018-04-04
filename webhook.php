<?php

$data = file_get_contents('php://input');
$data = json_decode($data);

require_once('aweber_api/aweber_api.php');

$consumerKey = '';
$consumerSecret = '';

$accessKey = '';
$accessSecret = '';
$list_id = ''; // list name:
if (!$consumerKey || !$consumerSecret){
    //print "You need to assign \$consumerKey and \$consumerSecret at the top of this script and reload.<br><br>" .
    //    "These are listed on <a href='https://labs.aweber.com/apps' target=_blank>https://labs.aweber.com/apps</a><br>\n";
    exit;
}
$aweber = new AWeberAPI($consumerKey, $consumerSecret);
if (!$accessKey || !$accessSecret){
    display_access_tokens($aweber);
}
try { 
    $account = $aweber->getAccount($accessKey, $accessSecret);
    $account_id = $account->id;
    if (!$list_id){
        display_available_lists($account);
        exit;
    }
    //print "You script is configured properly! " . 
     //   "You can now start to develop your API calls, see the example in this script.<br><br>";
        //"Be sure to set \$test_email if you are going to use the example<p>";
    
    if ($data->event_type === 'chat_started')
    {
        $email = $data->visitor->email;
        $name = $data->visitor->name;
        $page = $data->visitor->page_current;

        $tag = getTag($page);
        $list_id = getListID($page);

        $subscriber = array(
            'email' => $email,
            'name'  => $name
        );

        $listURL = "/accounts/{$account_id}/lists/{$list_id}"; 
        $list = $account->loadFromUrl($listURL);
        $params = array( 
            'email' => $email,
            'ip_address' => '',
            'ad_tracking' => '', 
            'misc_notes' => '', 
            'name' => $name,
            'tags' => array($tag)
        ); 
        $subscribers = $list->subscribers; 
        $new_subscriber = $subscribers->create($params);
    }
    
} catch(AWeberAPIException $exc) { 
    print "<h3>AWeberAPIException:</h3>"; 
    print " <li> Type: $exc->type <br>"; 
    print " <li> Msg : $exc->message <br>"; 
    print " <li> Docs: $exc->documentation_url <br>"; 
    print "<hr>"; 
    exit(1); 
}
function get_self(){
    return $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
function display_available_lists($account){
    print "Please add one for the lines of PHP Code below to the top of your script for the proper list<br>" .
            "then click <a href='" . get_self() . "'>here</a> to continue<p>";
    $listURL ="/accounts/{$account->id}/lists/"; 
    $lists = $account->loadFromUrl($listURL);
    foreach($lists->data['entries'] as $list ){
        print "<pre>\$list_id = '{$list['id']}'; // list name:{$list['name']}\n</pre>";
    }
}
function display_access_tokens($aweber){
    if (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])){
        $aweber->user->requestToken = $_GET['oauth_token'];
        $aweber->user->verifier = $_GET['oauth_verifier'];
        $aweber->user->tokenSecret = $_COOKIE['secret'];
        list($accessTokenKey, $accessTokenSecret) = $aweber->getAccessToken();
        print "Please add these lines of code to the top of your script:<br>" .
                "<pre>" .
                "\$accessKey = '{$accessTokenKey}';\n" . 
                "\$accessSecret = '{$accessTokenSecret}';\n" .
                "</pre>" . "<br><br>" .
                "Then click <a href='" . get_self() . "'>here</a> to continue";
        exit;
    }
    if(!isset($_SERVER['HTTP_USER_AGENT'])){
        print "This request must be made from a web browser\n";
        exit;
    }
    $callbackURL = get_self();
    list($key, $secret) = $aweber->getRequestToken($callbackURL);
    $authorizationURL = $aweber->getAuthorizeUrl();
    setcookie('secret', $secret);
    header("Location: $authorizationURL");
    exit();
}

function getTag($siteUrl){
	if(strpos($siteUrl, '1') !== false){
		return "1";
	}
	else if(strpos($siteUrl, '2') !== false){
		return "2";
	}
	else if(strpos($siteUrl, '3') !== false){
		return "3";
	}
	else if(strpos($siteUrl, '4') !== false){
		return "4";
	}
	else if(strpos($siteUrl, '5') !== false){
		return "5";
	}
	else if(strpos($siteUrl, '6') !== false){
		return "6";
	}
	else{
		return 'error';
	}
}

function getListID($siteUrl){
	if(strpos($siteUrl, '1') !== false){
		return '1';
	}
	if(strpos($siteUrl, '2') !== false){
		return '2';
	}
	else if(strpos($siteUrl, '3') !== false){
		return '3';
	}
	else if(strpos($siteUrl, '4') !== false){
		return '4';
	}
	else if(strpos($siteUrl, '5') !== false){
		return '5';
	}
	else if(strpos($siteUrl, '6') !== false){
		return '6';
	}
	else{
		return 'error';
	}
}

?>