<?php require_once __DIR__.'/vendor/autoload.php';

//use flourish;
use Models\Mailbox;

use Silex\Provider;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Silex\Provider\FormServiceProvider;
use Symfony\Component\Validator\Constraints as Assert;

$app = new Silex\Application();
$app['debug'] = true;
//error_reporting(E_ALL & ~(E_WARNING|E_NOTICE));

//$mailbox = array();

$app->register(new Provider\DoctrineServiceProvider());
$app->register(new Provider\SecurityServiceProvider());
$app->register(new Provider\RememberMeServiceProvider());
$app->register(new Provider\SessionServiceProvider());
$app->register(new Provider\ServiceControllerServiceProvider());
$app->register(new Provider\UrlGeneratorServiceProvider());
$app->register(new Provider\TwigServiceProvider(), array('twig.path' => __DIR__.'/../views'));
$app->register(new Provider\SwiftmailerServiceProvider());
$app->register(new Provider\FormServiceProvider());
$app->register(new Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.domains' => array(),
));

$simpleUserProvider = new SimpleUser\UserServiceProvider();
$app->register($simpleUserProvider);
$mbox = new Mailbox();
/*
function mailConnection(){
   // global $mailbox;
    // Connect to a remote imap server (last param: timeout)
    global $app;
    $mailbox = new flourish\fMailbox('imap', 'imap.gmail.com', 'mrappdevelopment@gmail.com', '47ax78d8', NULL, TRUE,10);
    $app['session']->set('mailbox',$mailbox);
    // Retrieve an overview of all messages
    $messages = $mailbox->listMessages();
    $app['session']->set('messages',$messages);
    // $messages = array_values($messages);
    //return displayMail($mailbox,array_values($messages)[5]['uid']);
    return  $messages;
}

function displayMail($uid)
{
    global $app;
    if(!$app['session']->has('mailbox'))
        mailConnection();

    $mailbox = $app['session']->get('mailbox');

    $mail = $mailbox->fetchMessage($uid);
    preg_match_all('/src="cid:(.*)"/Uims', $mail['html'], $matches);

    if(count($matches)) {

        $search = array();
        $replace = array();

        foreach($matches[1] as $match) {
            //$uniqueFilename = "A UNIQUE_FILENAME.extension";

             $uniqueFilename =  (string)rand(0,1000).(string)rand(0,1000).".tmp";
            file_put_contents('D:\PHP\Silex Angular mail\api\temp\\'.$uniqueFilename,$mail['related']['cid:'.$match]['data']);

            $search[] = "src=\"cid:$match\"";
            $replace[] = 'src="\temp\\'.$uniqueFilename.'\"';
        }
        $mail['html'] = str_replace($search, $replace, $mail['html']);
       // $emailMessage->bodyHTML = str_replace($search, $replace, $emailMessage->bodyHTML);

    }


    return $mail;
}
*/

// Mount the user controller routes:
$app->mount('/user', $simpleUserProvider);

// route 1: '/'
$app->get('/', function(Silex\Application $app) {
	//return json_encode($mailbox);
   // $aa =  SimpleUser\User::getUsername();
    //return json_encode(mailConnection());
    return $app->redirect('/mailbox');
   // $mail = mailConnection();
   // return $mail['html'];

});

$app->get('/api/emaillist/{id}', function($id) use($mbox,$app) {
    //return json_encode($mailbox);

    //return json_encode(mailConnection());
    $mail = $mbox->getMessages($id,$app);
    //$mail = mailConnection();
    return json_encode($mail);

});

$app->get('/api/mailbox/{id}', function (Silex\Application $app, $id) use ($mbox) {



    $msgs = $mbox->getMessages();
    $mail = $mbox->displayMail($msgs[(int)$id]['uid']);

   // return json_encode($mail);
    return $mail['html'];
});

$app->get('/api/MailboxList', function() use($app){
    $userId = $app['user']->getId();
    $sql = "SELECT * FROM mailbox WHERE user_id = ?";
    $mailboxes = $app['db']->fetchAll($sql, array( $userId));
  /*  if($mailboxes!=null && count($mailboxes)>0)
        if(!$app['session']->has('activemailbox'))
            $app['session']->set('activemailbox',$mailboxes[0]['id']);*/
    return json_encode($mailboxes);
});

$app->get('/api/maildetails/{uid}', function (Silex\Application $app, $uid) use ($mbox) {

    $mail = $mbox->displayMail($uid);
    //$response = json_encode($mail);
    return $mail['html'];
});

$app->get('/mailbox', function (Silex\Application $app) {
    return $app['twig']->render('index.html');
});

$app->post('/api/addmailbox', function (Request $request) use($app) {
    $info = "Nothing important";

    $data = $request->request->all();
    $constraint = new Assert\Collection(array(
        'email' => array(new Assert\NotBlank(),new Assert\Email()),
        'password'  => new Assert\NotBlank(),
        'server' => new Assert\NotBlank(),
        'port' => array(new Assert\NotBlank(),new Assert\Type("int") ),
        'ssl' => new Assert\Type("bool")
    ));

    $violationList = $app['validator']->validateValue($request->request->all(), $constraint);

    $errors = array();
    foreach ($violationList as $violation){
        $field = preg_replace('/\[|\]/', "", $violation->getPropertyPath());
        $error = $violation->getMessage();
        $errors[$field] = $error;
    }

    $userId = $app['user']->getId();
  /*  $sql = "SELECT * FROM users WHERE id = ?";
    $post = $app['db']->fetchAll($sql, array((int) $userId));*/

    if(count($errors)==0){
        $sql = "SELECT * FROM mailbox WHERE email = ?";
        $post = $app['db']->fetchAll($sql, array( $data['email']));

        if(count($post)==0) {
          /*  $sql = "INSERT INTO mailbox VALUES (?,?,?,?,?,?,?)";
            $pm = array(
                0,(int)$userId,$data['email'],$data['password'],$data['server'],(int)$data['port'],(int)$data['ssl']
            );
            $post = $app['db']->insert('mailbox',$pm );*/

            $app['db']->insert('`mailbox`', array(
                '`id`' => 0,
                '`user_id`' => (int)$userId,
                '`email`' => $data['email'],
                '`password`' => $data['password'],
                '`server`' => $data['server'],
                '`port`' => (int)$data['port'],
                '`ssl`' => (int)$data['ssl']
            ));
            $info = "The mailbox has been added.";
        } else {
            $info = "A mailbox with that email address already exists in the database.";
        }
    }else{
        $info.='Correct the following errors:</br><ul style="list-style-type: circle;">';
        foreach($errors as $key => $value){
            $info.='<li><br>'.$key .'</br>: ' . $value .'</li>';
        }
        $info.='</ul>';
    };

    //TODO Komunikat o pomyslnym dodaniu skrzynki - ukryc formatki i wyswietlic koomunikat, bledy z walidacji po stronie serwera
    if(count($errors)==0&&count($post)==0)
        return new Response($info, 201); //Success response
    else
        return new Response($info,409);
})->before('jsonToRequest');


// route 2: '/{stockcode}'
/*
$app->get('/{stockcode}', function (Silex\Application $app, $stockcode) use ($mailbox){
	if(!isset($mailbox[$stockcode])) {
	$app->abort(404, "Stockcode {$stockcode} does not exist/");
	}

	return json_encode($mailbox[$stockcode]);
});*/


//db conf

$app['user.options'] = array();

$app['security.firewalls'] = array(
    // Ensure that the login page is accessible to all
    'login' => array(
        'pattern' => '^/user/login$',
    ),
    'register' => array(
        'pattern' => '^/user/register$',
    ),
    'secured_area' => array(
        'pattern' => '^/(user|mailbox|api)', //'/^.*$',
        'anonymous' => false,
        'remember_me' => array(),
        'form' => array(
            'login_path' => '/user/login',
            'check_path' => '/user/login_check',
        ),
        'logout' => array(
            'logout_path' => '/user/logout',
        ),
        'users' => $app->share(function($app) { return $app['user.manager']; }),
    ),
);

// Mailer config. See http://silex.sensiolabs.org/doc/providers/swiftmailer.html
$app['swiftmailer.options'] = array();

// Database config. See http://silex.sensiolabs.org/doc/providers/doctrine.html
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'host' => 'localhost',
    'dbname' => 'mydb',
    'user' => 'root',
    'password' => '',
);



$app->run();

function jsonToRequest(Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
};