<?php

namespace App\Command;

use App\Entity\Restaurant;
use App\Entity\Zone;
use App\Repository\UserRepository;
use App\Service\Place;
use Doctrine\ORM\EntityManagerInterface;
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

class RestaurantImportCommand extends CsvCommand
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
        parent::__construct($em,$bag);
    }
    
    protected static $defaultName = 'app:restaurant:import';

    protected function configure()
    {
        $this
            ->setDescription('Import restaurants using csv file')
            ->setHelp(implode("\n", [
                'This command populate db with restaurants given in a csv',
                '<info>php %command.full_name% file.csv</info>'
            ]))
            ->addArgument('file',  InputArgument::REQUIRED, 'csv file')
            ->addOption('map','m',InputOption::VALUE_OPTIONAL,'map','')
            ->addOption('delimiter','d',InputOption::VALUE_OPTIONAL,'csv delimiter',';')
            ->addOption('erase','f',InputOption::VALUE_NONE,'erase existing data')
            ->addOption('limit','l',InputOption::VALUE_OPTIONAL,'limit')
            ->addOption('start','s',InputOption::VALUE_OPTIONAL,'start at',0)
            ->addOption('default_mapping',null,InputOption::VALUE_NONE,'');
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = array();

        if (!$input->getArgument('file') or !fileExists($input->getArgument('file'))) {
            $question = new Question('Please give the csv file path:');
            $question->setValidator(function ($file) {
                if (empty($file)) {
                    throw new \Exception('file path can not be empty');
                }
                return $file;
            });
            $questions['file'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $file = $input->getArgument('file');
        $delimiter = $input->getOption('delimiter');
        $map = $input->getOption('map');
        $limit = $input->getOption('limit');
        $erase = $input->getOption('erase');
        $start = $input->getOption('start');
        $default_mapping = $input->getOption('default_mapping');

        $output->writeln([
            '====================================',
            'Import registrations data from a csv',
            '====================================',
        ]);

        $zones = $this->em->getRepository(Zone::class)->findAll();
        $chooses = array();
        /** @var Zone $zone */
        foreach ($zones as $zone){
            $chooses[$zone->getId()] = $zone->getName();
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select your zone',
            $chooses,
            0
        );
        $question->setErrorMessage('Zone %s do not exist.');

        $zone = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected zone: '.$zone);

        $zone_id = array_search($zone,$chooses);
        $zone = $this->em->getRepository(Zone::class)->find($zone_id);

        $this->setNeededFields(array(
            'name' => array('label' => 'nom du restaurant','index'=>1),
            'address' => array('label' => 'adresse complète du restaurant','index'=>2),
            'website' => array('label' => 'site internet du restaurant','index'=>3)
        ));

        if (!$map && $this->loadSavedMap($file)){

        }else{
            $this->checkDelimiter($file,$delimiter,$input,$output);
            if (!$default_mapping&&!$map) {
                $this->mapField($file, $input, $output);
                $this->saveMap($file);
            }else{
                $this->setMap(explode(',',$map));
            }
            $confirm = new ConfirmationQuestion('Save this map as default ?', true);
            if ($this->getHelper('question')->ask($input, $output, $confirm)) {
                $this->saveMap($file);
            }
        }
        $output->writeln("<info>MAP : </info>");
        $this->displayMap($output);

        $lines = $this->getLines($file) - 1;

        $output->writeln("<info>File with <fg=cyan>$lines</> lines</info>");
        if ($start<0 or $start > $lines) {
            $start = 0;
        }
        if ($limit){
            $output->writeln("<info>Deal with <fg=cyan>$limit</> lines</info>");
        }
        if ($start != 0){
            $output->writeln("<info>Starting at <fg=cyan>$start</></info>");
        }

        $progress = new ProgressBar($output);
        $progress->setMaxSteps($lines);
        $progress->advance($start);

        if (($handle = fopen($file, "r")) !== FALSE) {

            $processed = 0;
            $goto = $start;
            while ((--$goto > 0) && (fgets($handle, 10000) !== FALSE)) {
            }

            $row = $start;

            while (($data = fgetcsv($handle, 10000, $this->getDelimiter())) !== FALSE) {
                if ($limit and $processed >= $limit) {
                    break;
                }
                if ($row > $lines) {
                    break;
                }

                $data = array_map("utf8_encode", $data); //utf8
                if ($row > 0) { //skip first line
                    $name = strtolower(utf8_decode($this->getField('name', $data)));
                    $address = utf8_decode($this->getField('address', $data));
                    $website = utf8_decode($this->getField('website', $data));
                    if ($name&$address){
                        $exist = $this->em->getRepository(Restaurant::class)->findOneBy(array('name'=>$name));
                        if (!$exist||$erase){
                            $data = $this->place->search($name,$address);
                            if (isset($data['error'])){
                                $io->error($data['error']);
                                $io->error('for "'.$name.'" and "'.$address.'"');
                            }else{
                                if (count($data['success'])>1){
                                    $io->info('Not only one result');
                                }else{
                                    $found = $data['success'][0];
                                    if ($exist)
                                        $restaurant = $exist;
                                    else
                                        $restaurant = new Restaurant();
                                    $restaurant->setName($name);
                                    $restaurant->setLat($found['geometry']['location']['lat']);
                                    $restaurant->setLng($found['geometry']['location']['lng']);
                                    $restaurant->setFormattedAddress($found['formatted_address']);
                                    $restaurant->setGooglePlaceId($found['place_id']);
                                    $details = $this->place->getDetails($found['place_id']);
                                    if (isset($details['success'])){
                                        if (isset($details['success']['opening_hours'])) {
                                            $restaurant->setOpeningHours($details['success']['opening_hours']['weekday_text']);
                                        }
                                        if (isset($details['success']['website'])){
                                            $restaurant->setWebsite($details['success']['website']);
                                        }else if($website){
                                            $restaurant->setWebsite($website);
                                        }
                                        if (isset($details['success']['formatted_phone_number'])) {
                                            $restaurant->setPhone($details['success']['formatted_phone_number']);
                                        }
                                    }
                                    $restaurant->setZone($zone);
                                    $this->em->persist($restaurant);
                                }
                            }
                        }else{
                            $io->info('a Restaurant with name "'.$name.'" already exist. skip this line.');
                        }
                    }else{
                        $io->error('Missing data for "'.$name.'" "'.$address.'".');
                    }
                }
                $row++;
                $processed++;
                $progress->advance();
            }
            fclose($handle);
            $this->em->flush();
            $io->writeln("", OutputInterface::VERBOSITY_VERBOSE);
            $io->writeln("flushing . . .", OutputInterface::VERBOSITY_VERBOSE);
            $progress->finish();
            $io->writeln('');
        }

        return self::SUCCESS;
    }
}
