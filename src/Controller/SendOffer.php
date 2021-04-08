<?php

namespace VC4SM\Bundle\Controller;

use VC4SM\Bundle\Entity\DidConnection;

class SendOffer
{
    //private $bookPublishingHandler;

    public function __construct(/*BookPublishingHandler $bookPublishingHandler*/)
    {
        //$this->bookPublishingHandler = $bookPublishingHandler;
    }

    public function __invoke(/*Book $data*/): DidConnection
    {
        //$this->bookPublishingHandler->handle($data);


        $didConnection = new DidConnection();
        $didConnection->setIdentifier('asdf');
        $didConnection->setName('Graz');
        $didConnection->setInvitation('offer!');

        return $didConnection;
    }
}
