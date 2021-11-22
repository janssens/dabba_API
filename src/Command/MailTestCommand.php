<?php

namespace App\Command;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MailTestCommand extends Command
{


    protected static $defaultName = 'app:mail:test';

    private $mailer;
    /**
     * @var ParameterBagInterface
     */
    private $parameters;

    public function __construct(MailerInterface $mailer,ParameterBagInterface $bag)
    {
        $this->mailer = $mailer;
        $this->parameters = $bag;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Test email configuration.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email to send the mail to')
            ->setHelp(implode("\n", [
                'The <info>app:mail:test</info> command test the mail conf :',
                '<info>php %command.full_name% test@plopcom.fr</info>',
                'This will send a test email to test@plopcom.fr.',
            ]))
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = [];

        if (!$input->getArgument('email')) {
            $question = new Question('Please enter an email:');
            $question->setValidator(function ($email) {
                if (empty($email)) {
                    throw new \Exception('Email can not be empty');
                }
                return $email;
            });
            $questions['email'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $this->mailer->send(
            (new TemplatedEmail())
                ->from(new Address($this->parameters->get('app.transactional_mail_sender'), 'Dabba consigne'))
                ->to($email)
                ->subject('Dabba test mail')
                ->htmlTemplate('admin/test_email.html.twig')
        );

        $io->success(sprintf('Test mail sent to %s.', $email));

        return 0;
    }
}
