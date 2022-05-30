<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\MedewerkerRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Medewerker;
use App\Form\MedewerkerType;
// use App\Form\AdminType;

class MedewerkerController extends AbstractController
{
    private $security;
    private $mailer;

    public function __construct(Security $security, MailerInterface $mailer)
    {
        $this->security = $security;
        $this->mailer = $mailer;
    }

    #[Route(path: '/login/employee', name: 'app_login_employee')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_employee_dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout/eployee', name: 'app_logout_employee')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/employee', name: 'app_employee_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('employee/dashboard.html.twig');
    }

    #[Route('/admin', name: 'app_admin_dashboard')]
    public function adminDashboard(MedewerkerRepository $MR): Response
    {
        if (!$this->security->isGranted('ROLE_CUSTOMER')) {
            $temp = $MR->findAllExcept($this->getUser()->getId());
        } else {
            $temp = $MR->findAll();
        }
        $employees = [];
        foreach($temp as $employee) {
            $employeeTemp["id"] = $employee->getId();
            $employeeTemp["roles"] = $employee->getRoles();
            $employeeTemp["voornaam"] = $employee->getVoornaam();
            $employeeTemp["achternaam"] = $employee->getAchternaam();
            $employeeTemp["email"] = $employee->getEmail();
            $employeeTemp["functie"] = $employee->getFunctie();
            $tempTelefoon = str_split(strval($employee->getTelefoonnummer()));
            $string = "";
            for ($i=0; $i < count($tempTelefoon); $i++) { 
                $string .= $tempTelefoon[$i];
                if (array_search($i, [0, 2, 4, 6])) {
                    $string .= " ";
                }
            }
            $employeeTemp["telefoonnummer"] = $string;
            array_push($employees, $employeeTemp);
        }

        return $this->render('employee/adminDashboard.html.twig', [
            'employees' => $employees,
        ]);
    }

    #[Route('/create/employee', name: 'app_create_employee')]
    #[Route('/edit/employee/{id}', name: 'app_edit_employee')]
    /* 
     * @param $id
     */
    public function register($id = 0, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MedewerkerRepository $MR): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        if ($id != 0 && $MR->find($id)) {
            $user = $MR->find($id);
        } else {
            $user = new Medewerker();
        }
        $form = $this->createForm(MedewerkerType::class, $user);
        $form->handleRequest($request);
        
        if (!($form->isSubmitted() && $form->isValid()) && $this->security->isGranted('ROLE_OWNER')) {
            $form->get('roles')->setData(false);
        }
    
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            if ($form->get('password')->getData() == $form->get('passwordCheck')->getData()) {
                $user->setPassword(
                $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );

                $roles = [];
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
    
                $entityManager->persist($user);
                $entityManager->flush();
    
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
                return $this->redirectToRoute("app_admin_dashboard");
            }
    
            $this->addFlash('error', 'wachtwoorden kwamen niet overeen');
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
            ]);
        }
    
        return $this->render('employee/admin_edit_employee.html.twig', [
            'medewerkerForm' => $form->createView(),
        ]);
    }
}
