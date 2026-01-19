<?php
namespace Insi\Ssm;

use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Mime\Address;

final class SendMail{
    public function __invoke($address, $code): int {
        $email = (new MailtrapEmail())
            ->from(new Address('hello@demomailtrap.co', 'Mailtrap Test'))
            ->to(new Address('2148@htl.rennweg.at')) // TODO: change this to $address (only works if smtp server exists)
            ->subject('Ihr 2-Faktor Anmeldungscode')
            ->category('Login')
            ->text('Ihr 2-Faktor Code lautet: '. $code);

        $response = MailtrapClient::initSendingEmails(
            apiKey: '681d6f06a290d7c6f1bae7708cac31a5'
        )->send($email);

        return Command::SUCCESS;
    }
}