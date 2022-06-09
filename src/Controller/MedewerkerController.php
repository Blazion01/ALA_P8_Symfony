<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\MedewerkerRepository;
use App\Repository\BehandelingRepository;
use App\Repository\WerkurenRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Werkuren;
use App\Entity\Medewerker;
use App\Entity\Behandeling;
use App\Form\MedewerkerType;
use App\Form\BehandelingFormType;
use App\Form\WorkhoursFormType;
use App\Form\MedewerkerEditType;
use Knp\Component\Pager\PaginatorInterface;

class MedewerkerController extends AbstractController
{
    private $security;
    private $mailer;

    public function __construct(Security $security, MailerInterface $mailer)
    {
        $this->security = $security;
        $this->mailer = $mailer;
    }

    /* #[Route(path: '/employee/login', name: 'app_login_employee')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_employee_dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['userType' => 'employee', 'last_username' => $lastUsername, 'error' => $error]);
    } */

    /* #[Route(path: '/employee/logout', name: 'app_logout_employee')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    } */

    #[Route('/employee', name: 'app_employee_dashboard')]
    public function dashboard(): Response
    {
        if(!$this->security->isGranted('ROLE_EMPLOYEE')) return $this->redirectToRoute('app_customer_dashboard');
        return $this->render('employee/dashboard.html.twig');
    }

    #[Route('/employee/admin', name: 'app_admin_dashboard')]
    public function adminDashboard(MedewerkerRepository $MR, BehandelingRepository $BR, PaginatorInterface $paginator): Response
    {
        $temp = $MR->findAll();
        $employees = [];
        foreach($temp as $employee) {
            $employeeTemp["id"] = $employee->getId();
            $employeeTemp["roles"] = $employee->getRoles();
            $employeeTemp["voornaam"] = $employee->getVoornaam();
            $employeeTemp["achternaam"] = $employee->getAchternaam();
            $employeeTemp["email"] = $employee->getEmail();
            $employeeTemp["functie"] = $employee->getFunctie();
            $tempTelefoon = str_split(strval($employee->getTelefoonnummer()));
            $string = "0";
            for ($i=0; $i < count($tempTelefoon); $i++) {
                $string .= $tempTelefoon[$i];
                if (array_search($i, [0, 2, 4, 6]) || $i == 0) $string .= " ";
            }
            $employeeTemp["telefoonnummer"] = $string;
            $employeeTemp["edit"] = true;
            $employeeTemp["remove"] = true;
            if (in_array("ROLE_ADMIN", $employeeTemp["roles"]) || 
                in_array("ROLE_OWNER", $employeeTemp["roles"]) ||
                in_array("ROLE_DEV", $employeeTemp["roles"])) {
                if(!$this->security->isGranted("ROLE_OWNER")) {
                    $employeeTemp["remove"] = false;
                    if($employeeTemp["id"] != $this->getUser()->getId())
                        $employeeTemp["edit"] = false;
                }
            }

            array_push($employees, $employeeTemp);
        }

        $temp = $BR->findAll();
        // $BPager = $paginator->paginate(
        //     $temp,
        //     $request->query->getInt('BPage', 1),
        //     10
        // );
        $behandelingen = [];
        foreach ($temp as $behandeling) {
            $behandelingTemp["id"] = $behandeling->getId();
            $behandelingTemp["type"] = $behandeling->getType();
            $behandelingTemp["groep"] = $behandeling->getGroep();
            $behandelingTemp["naam"] = $behandeling->getNaam();
            $behandelingTemp["prijs"] = $behandeling->getPrijs();
            $behandelingTemp["edit"] = true;
            $behandelingTemp["remove"] = false;

            array_push($behandelingen, $behandelingTemp);
        }
        
        return $this->render('employee/adminDashboard.html.twig', [
            'employees' => $employees,
            'behandelingen' => $behandelingen,
        ]);
    }

    #[Route('/employee/hours', name: 'app_employee_hours')]
    public function hoursForm(EntityManagerInterface $entityManager, WerkurenRepository $WR, Request $request): Response
    {
        $hours = new Werkuren();
        if($this->getUser()->getWerkuren()) {
            $hours = $this->getUser()->getWerkuren();
        }
        $form = $this->createForm(WorkhoursFormType::class, $hours);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if(!$this->getUser()->getWerkuren())
            $this->getUser()->setWerkuren($hours);

            $entityManager->flush();
            $this->addFlash('success', 'werkuren bijgewerkt');
            return $this->redirectToRoute('app_employee_dashboard');
        }

        return $this->render('employee/workhours_form.html.twig', [
            'werkurenForm' => $form->createView(),
        ]);
    }

    #[Route('/employee/create', name: 'app_create_employee')]
    #[Route('/employee/edit/{id}', name: 'app_edit_employee', requirements: ['id' => '\d+'])]
    public function register(int $id = null, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MedewerkerRepository $MR): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        if ($id != 0 && $MR->find($id)) {
            $user = $MR->find($id);
            $form = $this->createForm(MedewerkerEditType::class, $user);
            $ownAccount = false;
            if ($user->getId() == $this->getUser()->getId()) $ownAccount = true;
        } else {
            $user = new Medewerker();
            $form = $this->createForm(MedewerkerType::class, $user);
        }
        $form->handleRequest($request);
        
        if (!($form->isSubmitted() && $form->isValid())) {
            if ($this->security->isGranted("ROLE_OWNER")) {
                $form->get('roles')->setData(false);
                if (in_array("ROLE_ADMIN", $user->getRoles())) $form->get('roles')->setData("ROLE_ADMIN");
            }
        }
    
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $roles = [];
            if($this->security->isGranted('ROLE_OWNER')) {
                if($form->get('roles')->getData() != false) {
                    if(is_array($form->get('roles')->getData())) {
                        foreach($form->get('roles')->getData() as $role) {
                            array_push($roles, $role);
                        }
                    } else {
                        array_push($roles, $form->get('roles')->getData());
                    }
                }
                $user->setRoles($roles);
            }

            if ($id == 0) {
                if ($form->get('password')->getData() == $form->get('passwordCheck')->getData()) {
                    $user->setPassword(
                    $userPasswordHasher->hashPassword(
                            $user,
                            $form->get('password')->getData()
                        )
                    );
                } else {
                    $form->get('passwordCheck')->setData('');
                    $this->addFlash('error', 'wachtwoorden kwamen niet overeen');
                    if ($this->security->isGranted('ROLE_OWNER')) {
                        return $this->render('employee/owner_create_employee.html.twig', [
                            'medewerkerForm' => $form->createView(),
                        ]);
                    }
                
                    return $this->render('employee/admin_create_employee.html.twig', [
                        'medewerkerForm' => $form->createView(),
                    ]);
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();

            if ($id == 0) {
                // generate a signed url and email it to the user
                $this->mailer->send((new TemplatedEmail())
                    ->from(new Address('no-reply@ala-philomena.com', 'Mail Verification'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('emailTemplates/create_medewerker_email.html.twig')
                    ->context([
                        'naam' => $user->getVoornaam().' '.$user->getAchternaam(),
                        'function' => $user->getFunctie(),
                        'username' => $user->getEmail(),
                        'password' => $form->get('password')->getData(),
                    ])
                );
                // do anything else you need here, like send an email
                $this->addFlash('success', 'gebruiker correct aangemaakt');
            }
            if ($id != 0) $this->addFlash('info', 'medewerker succesvol bijgewerkt.');
            return $this->redirectToRoute("app_admin_dashboard");
        }

        if ($id == 0) {
            if ($this->security->isGranted('ROLE_OWNER')) {
                return $this->render('employee/owner_create_employee.html.twig', [
                    'medewerkerForm' => $form->createView(),
                ]);
            }
        
            return $this->render('employee/admin_create_employee.html.twig', [
                'medewerkerForm' => $form->createView(),
            ]);
        }

        if ($this->security->isGranted('ROLE_OWNER')) {
            return $this->render('employee/owner_edit_employee.html.twig', [
                'medewerkerForm' => $form->createView(),
                'ownAccount' => $ownAccount,
            ]);
        }
    
        return $this->render('employee/admin_edit_employee.html.twig', [
            'medewerkerForm' => $form->createView(),
            'ownAccount' => $ownAccount,
        ]);
    }

    #[Route('/employee/behandeling/create', name: 'app_create_behandeling')]
    #[Route('/employee/behandeling/edit/{id}', name: 'app_edit_behandeling', requirements: ['id' => '\d+'])]
    public function addBehandeling(int $id = null, Request $request, EntityManagerInterface $entityManager, BehandelingRepository $BR): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        $edit = false;
        if ($id != 0 && $BR->find($id)) {
            $behandeling = $BR->find($id);
            $form = $this->createForm(BehandelingFormType::class, $behandeling);
            $edit = true;
        } else {
            $behandeling = new Behandeling();
            $form = $this->createForm(BehandelingFormType::class, $behandeling);
            $form->get('type')->setData($BR->countRows() + 1);
        }
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($behandeling);
            $entityManager->flush();

            if ($id != 0) $this->addFlash('info', 'behandeling succesvol bijgewerkt.');
            if ($id == 0) $this->addFlash('info', 'behandeling succesvol aangemaakt.');
            return $this->redirectToRoute("app_admin_dashboard");
        }
    
        return $this->render('employee/behandeling_create.html.twig', [
            'behandelingForm' => $form->createView(),
            'edit' => $edit
        ]);
    }
}
