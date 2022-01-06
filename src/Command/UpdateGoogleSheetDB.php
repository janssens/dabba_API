<?php

namespace App\Command;

use App\Entity\CodePromo;
use App\Entity\Container;
use App\Entity\Movement;
use App\Entity\Order;
use App\Entity\Restaurant;
use App\Entity\Stock;
use App\Entity\Trade;
use App\Entity\User;
use App\Entity\Zone;
use App\Repository\UserRepository;
use App\Service\GSheets;
use App\Service\Place;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\Sheets;
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
use function PHPUnit\Framework\isInstanceOf;

class UpdateGoogleSheetDB extends Command
{

    /**
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     *
     * @var Sheets
     */
    private $gsheets;

    private $parameter_bag;

    public function __construct(EntityManagerInterface $em,GSheets $gsheets,ParameterBagInterface $bag)
    {
        $this->em = $em;
        $this->gsheets = $gsheets;
        $this->parameter_bag = $bag;
        parent::__construct();
    }
    
    protected static $defaultName = 'app:google_sheets:update_db';

    protected function configure()
    {
        $this
            ->setDescription('Update google sheets data')
            ->setHelp(implode("\n", [
                'This command update a google sheet with data from DB',
                '<info>php %command.full_name%</info>'
            ]));
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->exportEntity(["id"=>"ID","name"=>"Name"],Zone::class,$io);
        $this->exportEntity(["id"=>"ID","name"=>"Name"],Container::class,$io);
        $this->exportEntity(["id"=>"ID","name"=>"Name","zone"=>"Zone"],Restaurant::class,$io);
        $this->exportEntity(["id"=>"ID","email"=>"Mail","zone"=>"Zone"],User::class,$io);
        $this->exportEntity(["id"=>"ID","created_at"=>"Date","user"=>"Utilisateur","restaurant"=>"Restaurant"],Trade::class,$io);
        $this->exportEntity(["id"=>"ID","created_at"=>"Date","amount"=>"montant","user"=>"Utilisateur","stateToString"=>"state"],Order::class,$io);
        $this->exportEntity(["id"=>"ID","created_at"=>"Date","reason_txt"=>"Raison","container"=>"Contenant","stock_from"=>"Depuis","stock_to"=>"Vers","quantity"=>"Quantité"],Movement::class,$io);
        $this->exportEntity(["id"=>"ID","type_to_string"=>"Type","link_id"=>"id associé"],Stock::class,$io);
        $this->exportEntity(["id"=>"ID","code"=>"Code","amount"=>"montant","enabled"=>"Activé","used_at"=>"utilisé le","used_by"=>"utilisé par","expired_at"=>"expire le"],CodePromo::class,$io);

        $this->writeMeta($io);

        return self::SUCCESS;
    }

    protected function writeMeta($io){
        $io->writeln('WRITE META DATA');
        $now = new \DateTime();
        $data = [
            ['App mode',$this->parameter_bag->get('app.env')],
            ['Last sync date',$now->format(DATE_W3C)],
            ['Author name','Gaëtan Janssens'],
            ['Author email','contact@plopcom.fr'],
        ];
        $this->gsheets->update("meta",$data);
    }

    protected function exportEntity($fields,$className,$io){
        $io->writeln('EXPORT '.$className);

        $progress = new ProgressBar($io);
        $items = $this->em->getRepository($className)->findAll();
        $progress->setMaxSteps(count($items));
        $data = array();
        $data[] = array_values($fields);
        foreach ($items as $item){
            $a = [];
            foreach ($fields as $key => $label){
                $getter = 'get'.str_replace(' ','',ucwords(str_replace("_"," ",strtolower($key))));
                $r = $item->$getter();
                if (is_object($r)){
                    if (get_class($r) == 'DateTime'){
                        $a[] = $r->format(DATE_W3C);
                    }else if(method_exists($r, 'getId')){
                        $a[] = $this->entityNameFromClassName(get_class($r))."#".$r->getId();
                    }else{
                        $a[] = $this->entityNameFromClassName(get_class($r));
                    }
                }else if ($r){
                    $a[] = $r;
                }else{
                    $a[] = '';
                }
            }
            $data[] = $a;
            $progress->advance();
        }
        $progress->finish();

        $this->gsheets->update($className,$data);
        $io->writeln('');
    }

    protected function entityNameFromClassName($className){
        $exploded = explode('\\',$className);
        if (is_array($exploded))
            return array_pop($exploded);
        return $exploded;
    }
}
