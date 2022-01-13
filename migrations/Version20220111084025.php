<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\CodePromo;
use App\Entity\Order;
use App\Entity\Trade;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\WalletAdjustment;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Configuration\EntityManager\ManagerRegistryEntityManager;
use Doctrine\Migrations\Exception\MigrationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220111084025 extends AbstractMigration implements ContainerAwareInterface
{

    private $container;

    public function getDescription(): string
    {
        return '';
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wallet_adjustment (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, admin_id INT DEFAULT NULL, created_at DATETIME NOT NULL, type SMALLINT NOT NULL, INDEX IDX_EA647D4A76ED395 (user_id), INDEX IDX_EA647D4642B8210 (admin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE wallet_adjustment ADD CONSTRAINT FK_EA647D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE wallet_adjustment ADD CONSTRAINT FK_EA647D4642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE wallet_adjustment ADD amount INT NOT NULL, ADD from_order_id INT DEFAULT NULL, ADD from_trade_id INT DEFAULT NULL,ADD notes VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE wallet_adjustment ADD CONSTRAINT FK_EA647D4CB708DA2 FOREIGN KEY (from_order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE wallet_adjustment ADD CONSTRAINT FK_EA647D44AC277FA FOREIGN KEY (from_trade_id) REFERENCES trade (id)');
        $this->addSql('CREATE INDEX IDX_EA647D4CB708DA2 ON wallet_adjustment (from_order_id)');
        $this->addSql('CREATE INDEX IDX_EA647D44AC277FA ON wallet_adjustment (from_trade_id)');

    }

    /**
     * @throws MigrationException|DBALException
     */
    public function postUp(Schema $schema): void
    {
        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine')->getManager();
        $paid_orders = $em->getRepository(Order::class)->findBy(['state'=>Order::STATE_PAID]);
        /** @var Order $paid_order */
        foreach ($paid_orders as $paid_order){
            $walletAdjustment = new WalletAdjustment();
            $walletAdjustment->setCreatedAt($paid_order->getCreatedAt());
            $walletAdjustment->setAmount($paid_order->getAmount());
            $walletAdjustment->setUser($paid_order->getUser());
            $walletAdjustment->setType(WalletAdjustment::TYPE_CREDIT);
            $walletAdjustment->setFromOrder($paid_order);
            $walletAdjustment->setNotes($paid_order->getTransactionsAsTxt());
            $em->persist($walletAdjustment);
        }

        $trades = $em->getRepository(Trade::class)->findAll();
        /** @var Trade $trade */
        foreach ($trades as $trade){
            $amount = intval($trade->getBalance());
            if ($amount < 0){
                $walletAdjustment = new WalletAdjustment();
                $walletAdjustment->setCreatedAt($trade->getCreatedAt());
                $walletAdjustment->setAmount(abs($amount));
                $walletAdjustment->setUser($trade->getUser());
                $walletAdjustment->setType(WalletAdjustment::TYPE_DEBIT);
                $walletAdjustment->setFromTrade($trade);
                $walletAdjustment->setNotes($trade->getItemsAsTxt());
                $em->persist($walletAdjustment);
            } if ($amount > 0){
                $walletAdjustment = new WalletAdjustment();
                $walletAdjustment->setCreatedAt($trade->getCreatedAt());
                $walletAdjustment->setAmount(abs($amount));
                $walletAdjustment->setUser($trade->getUser());
                $walletAdjustment->setType(WalletAdjustment::TYPE_CREDIT);
                $walletAdjustment->setFromTrade($trade);
                $walletAdjustment->setNotes($trade->getItemsAsTxt());
                $em->persist($walletAdjustment);
            }
        }
        $em->flush();

        // bug that do not credit first return (if stock creation).
        $users = $em->getRepository(User::class)->findAll();
        /** @var User $user */
        foreach ($users as $user){
            /** @var Trade $trade */
            $trade = $em->getRepository(Trade::class)->findOneBy(array('user'=>$user),array('created_at'=>'ASC'));
            if ($trade){
                $end_of_bug = new \DateTime('2022-01-06 15:30:00');
                if ($trade->getCreatedAt() < $end_of_bug && $trade->getBalance() > 0){
                    //$user->addToWallet($trade->getBalance()); //to fix
                    $walletAdjustement = new WalletAdjustment();
                    //$rollback_date = $trade->getCreatedAt()->add(new \DateInterval('PT1S'));
                    $walletAdjustement->setCreatedAt($trade->getCreatedAt());
                    $walletAdjustement->setAmount(abs(intval($trade->getBalance())));
                    $walletAdjustement->setUser($user);
                    $walletAdjustement->setType(WalletAdjustment::TYPE_DEBIT);
                    $walletAdjustement->setNotes('BUG "no credit on first return" (before 6/01/2022 15:30)');
                    $em->persist($walletAdjustement);
                }
            }
        }
        $em->flush();

        /** @var CodePromo[] $codes */
        $codes = $em->getRepository(CodePromo::class)->findUsed();
        foreach ($codes as $codePromo){
            $walletAdjustement = new WalletAdjustment();
            $walletAdjustement->setUser($codePromo->getUsedBy());
            $walletAdjustement->setType(WalletAdjustment::TYPE_CREDIT);
            $walletAdjustement->setAmount($codePromo->getAmount());
            $walletAdjustement->setCreatedAt($codePromo->getUsedAt());
            $walletAdjustement->setNotes('Using CODE : '.$codePromo->getCode());
            $em->persist($walletAdjustement);
        }
        $em->flush();

        //register manual change
        /** @var User[] $users */
        $users = $em->getRepository(User::class)->findAll();
        foreach ($users as $user){
            $computed_wallet = 0;
            /** @var WalletAdjustment $walletAdjustment */
            foreach ($user->getWalletAdjustments() as $walletAdjustment){
                $computed_wallet += $walletAdjustment->getBalance();
            }
            if ($computed_wallet != $user->getWallet()){
                $amount = $computed_wallet - intval($user->getWallet());
                $walletAdjustement = new WalletAdjustment();
                $walletAdjustement->setCreatedAt(new \DateTimeImmutable());
                $walletAdjustement->setAmount(abs($amount));
                $walletAdjustement->setUser($user);
                if ($amount<0){
                    $walletAdjustement->setType(WalletAdjustment::TYPE_CREDIT);
                }else{
                    $walletAdjustement->setType(WalletAdjustment::TYPE_DEBIT);
                }
                $walletAdjustement->setNotes('ADJUST ON SETUP');
                $em->persist($walletAdjustement);
            }
        }
        $em->flush();

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE wallet_adjustment');
    }
}
