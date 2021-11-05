<?php

namespace App\Command;

use App\Entity\Restaurant;
use App\Entity\Zone;
use App\Repository\UserRepository;
use App\Service\Place;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use function PHPUnit\Framework\fileExists;

class UpdateOpeningHoursCommand extends Command
{

    /**
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     *
     * @var Place
     */
    private $place;

    public function __construct(EntityManagerInterface $em,Place $place,ParameterBagInterface $bag)
    {
        $this->em = $em;
        $this->place = $place;
        parent::__construct();
    }
    
    protected static $defaultName = 'app:restaurant:update_opening_hours';

    protected function configure()
    {
        $this
            ->setDescription('Update opening hours data')
            ->setHelp(implode("\n", [
                'This command update restaurants opening hours data from google place API',
                '<info>php %command.full_name%</info>'
            ]));
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $restaurants = $this->em->getRepository(Restaurant::class)->findAll();

        $progress = new ProgressBar($io);
        $progress->setMaxSteps(count($restaurants));
        /** @var Restaurant $restaurant */
        foreach ($restaurants as $restaurant){
            if ($restaurant->getGooglePlaceId()){
                $new_data = $this->place->getOpenningHours($restaurant->getGooglePlaceId());
                if (isset($details['success'])){
                    $restaurant->setOpeningHours($new_data['success']['opening_hours']['weekday_text']);
                    $this->em->persist($restaurant);
                }else if (isset($details['error'])){
                    $io->error($details['error']);
                }
            }
            $progress->advance();
        }
        $this->em->flush();
        $progress->finish();

        $io->writeln('');

        return self::SUCCESS;
    }
}
