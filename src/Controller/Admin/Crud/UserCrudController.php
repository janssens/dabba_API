<?php

namespace App\Controller\Admin\Crud;

use App\Entity\CodePromo;
use App\Entity\User;
use App\Entity\WalletAdjustment;
use App\Repository\AccessTokenRepository;
use App\Security\EmailVerifier;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\Address;

class UserCrudController extends AbstractCrudController
{
    private $adminUrlGenerator;
    /** @var AccessTokenRepository  */
    private $accessTokentRepo;
    /** @var EmailVerifier */
    private $emailVerifier;


    public function __construct(
        AdminUrlGenerator $adminUrlGenerator,
        AccessTokenRepository $accessTokenRepository,
        EmailVerifier $emailVerifier
    )
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->accessTokentRepo = $accessTokenRepository;
        $this->emailVerifier = $emailVerifier;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('email')
            ->add(DateTimeFilter::new('created_at','date de creation'))
            ->add('wallet')
            ->add('zone')
            ->add('roles')
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsAsDropdown()
            ;
    }


    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $make_admin_action = Action::new('makeAdmin', 'Make ADMIN','fas fa-user-shield')
            ->displayIf(static function ($entity) { /** @var User $entity */
                return !$entity->hasRoles('ROLE_ADMIN') && !$entity->hasRoles('ROLE_SUPER_ADMIN');
            })->linkToCrudAction('makeAdmin');
        $remove_admin_action = Action::new('removeAdmin', 'Remove role ADMIN','fas fa-user')
            ->displayIf(static function ($entity) { /** @var User $entity */
                return $entity->hasRoles('ROLE_ADMIN') && !$entity->hasRoles('ROLE_SUPER_ADMIN');
            })->linkToCrudAction('removeAdmin');
        $make_super_action = Action::new('makeSuperAdmin', 'Make SUPER ADMIN','fas fa-user-ninja')
            ->displayIf(static function ($entity) { /** @var User $entity */
                return $entity->hasRoles('ROLE_ADMIN') && !$entity->hasRoles('ROLE_SUPER_ADMIN');
            })->linkToCrudAction('makeSuperAdmin');
        $anonymize_action = Action::new('anonymize', 'Anonymiser','fas fa-user-secret')
            ->displayIf(static function ($entity) { /** @var User $entity */
                return !$entity->hasRoles('ROLE_ADMIN') && !$entity->hasRoles('ROLE_SUPER_ADMIN');
            })->linkToCrudAction('anonymize');
        $send_verify = Action::new('sendVerify', 'Send verify Email','fas fa-envelop')
            ->displayIf(static function ($entity) { /** @var User $entity */
                return !$entity->isVerified();
            })->linkToCrudAction('sendVerify');
        $actions->add(Crud::PAGE_INDEX, $make_admin_action);
        $actions->add(Crud::PAGE_INDEX, $remove_admin_action);
        $actions->add(Crud::PAGE_INDEX, $make_super_action);
        $actions->add(Crud::PAGE_INDEX, $send_verify);
        $actions->add(Crud::PAGE_INDEX, $anonymize_action);

        $actions->remove(Crud::PAGE_INDEX,Action::DELETE);
        $actions->remove(Crud::PAGE_DETAIL,Action::DELETE);

        $actions
            ->addBatchAction(Action::new('download', 'Exporter les utilisateurs')
                ->linkToCrudAction('csvDownload')
                ->addCssClass('btn btn-secondary')
                ->setIcon('fa fa-file-download'))
        ;

        $wallet = Action::new('wallet', 'Cagnotte', 'fa fa-wallet')
            ->linkToCrudAction('wallet');
        $actions->add(Crud::PAGE_INDEX, $wallet);

        //$actions->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
        $actions->setPermission('makeAdmin', 'ROLE_SUPER_ADMIN');
        $actions->setPermission('makeSuperAdmin', 'ROLE_SUPER_ADMIN');
        $actions->setPermission('removeAdmin', 'ROLE_SUPER_ADMIN');
        $actions->setPermission('download', 'ROLE_SUPER_ADMIN');
        return $actions;
    }

    public function wallet(AdminContext $context,Request $request)
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $em = $this->getDoctrine()->getManager();

        $formBuilder = $this->createFormBuilder()
            ->add('qty', IntegerType::class, ['label' => 'Nouveau montant'])
            ->add('notes', TextType::class, ['label' => 'Notes'])
            ->add('save', SubmitType::class, ['label' => 'Valider nouveau montant']);

        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $new_wallet = $form->get('qty')->getData();
            if ($new_wallet<0 ){
                throw new \Exception('negative wallet is not allowed');
            }

            $delta = $new_wallet - $user->getWallet();

            $wa = new WalletAdjustment();
            $wa->setCreatedAt(new \DateTimeImmutable());
            $wa->setAmount(abs($delta));
            $wa->setNotes($form->get('notes')->getData());
            $wa->setUser($user);
            if ($delta<0){
                $wa->setType(WalletAdjustment::TYPE_REFUND);
            }else{
                $wa->setType(WalletAdjustment::TYPE_CREDIT);
            }
            $wa->setAdmin($this->getUser());

            $user->setWallet($new_wallet);

            $em->persist($wa);
            $em->persist($user);
            $em->flush();

            $url = $this->adminUrlGenerator
                ->setController(UserCrudController::class)
                ->setAction('wallet')
                ->generateUrl();

            return $this->redirect($url);
        }

        return $this->render('admin/crud/wallet.html.twig',
            [
                'user'=>$user,
                'form' => $form->createView()
            ]);
    }

    public function sendVerify(AdminContext $context)
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address($this->getParameter('app.transactional_mail_sender'), 'Dabba consigne'))
                ->to($user->getEmail())
                ->subject('Confirme ton adresse e-mail pour créer ton compte dabba')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
        return $this->redirect($context->getReferrer());
    }

    public function makeAdmin(AdminContext $context)
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $user->addRole('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirect($context->getReferrer());
    }

    public function removeAdmin(AdminContext $context)
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $user->removeRole('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirect($context->getReferrer());
    }

    public function anonymize(AdminContext $context)
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $user->eraseCredentials();
        $user->setIsVerified(false);
        $user->setFirstname('');
        $user->setLastName('');
        $user->setEmail('anonymous_'.date('YmdHis').'@localhost');
        $user->setDob(null);
        $user->setRoles(['ROLE_ANONYMOUS']);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        foreach ($this->accessTokentRepo->findAllActiveForUser($user) as $token){
            $token->revoke();
            $em->persist($token);
        }
        $em->flush();
        return $this->redirect($context->getReferrer());
    }

    public function makeSuperAdmin(AdminContext $context)
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $user->removeRole('ROLE_ADMIN');
        $user->addRole('ROLE_SUPER_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return $this->redirect($context->getReferrer());
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('getFullname','nom complet')->hideOnForm(),
            TextField::new('firstname','prenom')->onlyOnForms(),
            TextField::new('lastname','nom')->onlyOnForms(),
            TextField::new('password','mot de passe')->onlyWhenCreating(),
            DateTimeField::new('created_at','date de creation')->onlyOnIndex(),
            EmailField::new('email'),
            IntegerField::new('wallet','cagnotte'),
            TextField::new('getRolesList','Roles'),
            AssociationField::new('zone'),
            BooleanField::new('isVerified','email validé'),
        ];
    }

    public function csvDownload(BatchActionDto $batchActionDto)
    {
        $entityManager = $this->getDoctrine()->getManagerForClass($batchActionDto->getEntityFqcn());
        $users = [];
        foreach ($batchActionDto->getEntityIds() as $id) {
            /** @var User $user */
            $user = $entityManager->find(User::class,$id);
            $users[] = [
                'id'=>$user->getId(),
                'name'=>$user->getFullname(),
                'email'=>$user->getEmail(),
                'created_at'=> $user->getCreatedAt()->format(DATE_W3C),
                'cagnotte'=> $user->getWallet(),
                'zone'=> ($user->getZone()) ? $user->getZone()->getName() : '',
            ];
        }
        $response = new StreamedResponse();
        $response->setCallback(function () use ($users) {
            $handle = fopen('php://output', 'w+');
            // Add header
            fputcsv($handle, array_keys($users[0]));
            foreach ($users as $user) {
                fputcsv($handle, array_values($user));
            }
            fclose($handle);
        });
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="users.csv"');
        return $response;
    }

}
