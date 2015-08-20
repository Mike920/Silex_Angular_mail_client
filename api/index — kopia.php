<?php
 require_once __DIR__.'/vendor/autoload.php';

  $app = new Silex\Application();
  // Please set to false in a production environment
  $app['debug'] = true;

  $toys = array(
         '00001'=> array(
             'name' => 'Racing Car',
         'quantity' => '53',
         'description' => '...',
         'image' => 'racing_car.jpg',
     ),
     '00002' => array(
             'name' => 'Raspberry Pi',
         'quantity' => '13',
         'description' => '...',
         'image' => 'raspberry_pi.jpg',
     ),
      'mail1' => array(
          'name' => 'Raspberry Pi',
          'quantity' => '13',
          'description' => '...',
          'image' => 'raspberry_pi.jpg',
      )
  
 );

function receiveMail()
{
    //  try {
    // Create a new IMAP transport with an SSL connection (default port is 993,
    // you can specify a different one using the second parameter of the constructor).
    $options = new ezcMailImapTransportOptions();
    $options->ssl = true;
    $imap = new ezcMailImapTransport("imap.gmail.com", null, $options);
    // Authenticate to the IMAP server
    $imap->authenticate("mrappdevelopment@gmail.com", "47ax78d8");

    // Select the Inbox mailbox
    $imap->selectMailbox('Inbox');
    // Get the number of messages on the server, combined size, number of recent
    // messages and number of unseen messages
    // in the variables $num, $size, $recent, $unseen
    $imap->status($num, $size, $recent, $unseen);
    // Get the list of message numbers on the server and their sizes
    // the returned array is something like: array( 1 => 1500, 2 => 45200 )
    // where the key is a message number and the value is the message size
    $messages = $imap->listMessages();
    echo "Messages list: " . json_encode($messages) . " \n\n";
    // Get the list of message unique ids on the server and their sizes
    // the returned array is something like: array( 1 => '15', 2 => '16' )
    // where the key is an message number and the value is the message unique id
    $messages = $imap->listUniqueIdentifiers();
    echo "Messages id's: " . json_encode($messages) . " \n\n";
    // Usually you will call one of these fetch functions:
    // Fetch all messages on the server

    // Fetch one message from the server (here: get the message no. 2)
    /* $set = $imap->fetchByMessageNr(2);
     // Fetch a range of messages from the server (here: get 4 messages starting from message no. 2)
     $set = $imap->fetchFromOffset(2, 4);
     // Fetch messages which have a certain flag
     // See the function description for a list of supported flags
     $set = $imap->fetchByFlag("DELETED");
     // Fetch a range of messages sorted by Date
     // Use this to page through a mailbox
     // See the function description for a list of criterias and for how to sort ascending or descending
     $set = $imap->sortFromOffset(1, 10, "Date");
     // Sort the specified messages by Date
     // See the function description for a list of criterias and for how to sort ascending or descending
     $set = $imap->sortMessages("1,2,3,4,5", "Date");
     // Fetch messages which match the specified criteria.
     // See the section 6.4.4. of RFC 1730 or 2060 for a list of criterias
     // (http://www.faqs.org/rfcs/rfc1730.html)
     // The following example returns the messages flagged as SEEN and with
     // 'release' in their Subject
     $set = $imap->searchMailbox('SEEN SUBJECT "release"');
 // Delete a message from the server (message is not physically deleted, but it's
 // list of flags get the "Deleted" flag.
     $imap->delete(1);
 // Use this to permanently delete the messages flagged with "Deleted"
     $imap->expunge();*/
// Use this to keep the connection alive

// Create a new mail parser object
    //       $parser = new ezcMailParser();
// Parse the set of messages retrieved from the server earlier
    //      $mail = $parser->parseMail($set);

    /*
        for ( $i = 0; $i < count( $mail ); $i++ )
        {
            // Process $mail[$i] such as use $mail[$i]->subject, $mail[$i]->body
            echo "From: {$mail[$i]->from}, Subject: {$mail[$i]->subject}\n";
            // Save the attachments to another folder
            $parts = $mail[$i]->fetchParts();
            echo "Attachments:\n";
            foreach ( $parts as $part )
            {
                if ( $part instanceof ezcMailFile )
                {
                    echo $part->fileName;
                    rename( $part->fileName, 'D:\PHP\Silex Angular mail\temp\\' . basename( $part->contentDisposition->displayFileName ) );
                }
            }
        }
    */
    viewEmail($imap);
    /*}
    catch ( Exception $e ) { echo "Failed: ", $e->getMessage(), "\n"; }*/
}


class collector
{
    function saveMailPart( $context, $mailPart )
    {
        // if it's a file, we copy the attachment to a new location, and
        // register its CID with the class - attaching it to the location in
        // which the *web server* can find the file.
        if ( $mailPart instanceof ezcMailFile )
        {
            // copy files to tmp with random name
            $newFile = 'D:\PHP\Silex Angular mail\temp\\' . basename( $mailPart->contentDisposition->displayFileName ) ;
            copy( $mailPart->fileName, $newFile );
            // save location and setup ID array
            $this->cids[$mailPart->contentId] =
                $this->webDir . '\\' . basename( $newFile );
        }
        // if we find a text part and if the sub-type is HTML (no plain text)
        // we store that in the classes' htmlText property.
        if ( $mailPart instanceof ezcMailText )
        {
            if ( $mailPart->subType == 'html' )
            {
                $this->htmlText = $mailPart->text;
            }
        }
    }
}
function viewEmail($imap)
{
    $set = $imap->fetchAll();
    $imap->noop();
    $parser = new ezcMailParser();
// Parse the set of messages retrieved from the server earlier
    $mail = $parser->parseMail($set);
// create the collector class and set the filesystem path, and the webserver's
// path to find the attached files (images) in.
    $collector = new collector();
    $collector->dir = 'D:\PHP\Silex Angular mail\temp';
    $collector->webDir = 'D:\PHP\Silex Angular mail\temp';
// We use the saveMailPart() method of the $collector object function as a
// callback in walkParts().
    $context = new ezcMailPartWalkContext( array( $collector, 'saveMailPart' ) );
// only call the callback for file and text parts.
    $context->filter = array( 'ezcMailFile', 'ezcMailText' );
// use walkParts() to iterate over all parts in the first parsed e-mail
// message.
    $mail[3]->walkParts( $context, $mail[3] );
// display the html text with the content IDs replaced with references to the
// file in the webroot.
    echo ezcMailTools::replaceContentIdRefs( $collector->htmlText, $collector->cids );
}




 $app->get('/', function() use ($toys) {

     return json_encode($toys);
 });

 $app->get('/{stockcode}', function (Silex\Application $app, $stockcode) use ($toys) {

     if (!isset($toys[$stockcode])) {
                 $app->abort(404, "Stockcode {$stockcode} does not exist.");
     }
     return json_encode($toys[$stockcode]);
 });

 $app->run();