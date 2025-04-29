<?php

namespace App\Mail\Transports;

use App\Services\ApiMailService;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class ApiTransport extends AbstractTransport
{
    protected $apiMailService;

    public function __construct(ApiMailService $apiMailService)
    {
        parent::__construct();
        $this->apiMailService = $apiMailService;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $content = $email->getHtmlBody() ?? $email->getTextBody() ?? '';
        $this->apiMailService->sendEmail(
            $email->getTo()[0]->getAddress(),
            $email->getSubject(),
            $email->getHtmlBody(),
            $content, // Now guaranteed to have content
            [
                'from_name' => $email->getFrom()[0]->getName(),
            ]
        );
    }

    public function __toString(): string
    {
        return 'api';
    }
}
