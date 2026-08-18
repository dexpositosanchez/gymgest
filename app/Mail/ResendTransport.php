<?php

namespace App\Mail;

use Resend;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class ResendTransport extends AbstractTransport
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $client = Resend::client($this->apiKey);

        $to = [];
        foreach ($email->getTo() as $address) {
            $to[] = $address->getAddress();
        }

        $client->emails->send([
            'from' => $email->getFrom()[0]->getAddress(),
            'to' => $to,
            'subject' => $email->getSubject(),
            'html' => $email->getHtmlBody(),
            'text' => $email->getTextBody(),
        ]);
    }

    public function __toString(): string
    {
        return 'resend';
    }
}