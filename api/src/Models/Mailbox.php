<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2015-02-15
 * Time: 15:18
 */

namespace Models;

use flourish;

class Mailbox {

    private $mailbox;
    private $messages;
    public function __construct(){

    }
    //TODO przechwyc wyjatek i zwroc informacje o bledzie do angulara
    public function estabilishConnection($mailboxId,$app){
       // $this->mailbox = new flourish\fMailbox('imap', 'imap.gmail.com', 'mrappdevelopment@gmail.com', '47ax78d8', NULL, TRUE);


        $sql = "SELECT * FROM mailbox WHERE id = ?";
        $mbox = $app['db']->fetchAll($sql, array( $mailboxId))[0];
       try {
           $this->mailbox = new flourish\fMailbox('imap', $mbox['server'], $mbox['email'], $mbox['password'], NULL, TRUE);
       }catch (\Exception $e){
           $msg = $e->getMessage();
           $a=$msg;
       }


    }

    public function getMessages($mailboxId,$app){
        $this->estabilishConnection($mailboxId,$app);
        try {
            $this->messages = $this->mailbox->listMessages();
        }catch (Exception $e){
            $this->messages = $this->mailbox->listMessages();
        }
        // $messages = array_values($messages);
        //return displayMail($mailbox,array_values($messages)[5]['uid']);
        return $this->messages;
    }

   public function displayMail($uid,$mailboxId,$app)
    {
        $this->estabilishConnection($mailboxId,$app);
        $mail =$this->mailbox->fetchMessage($uid);
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

}
?>