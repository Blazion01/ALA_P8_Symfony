<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MedewerkerRepository;
use App\Repository\AfspraakRepository;
use App\Repository\BehandelingRepository;
use App\Form\RemoveFormType;
use App\Entity\Klant;
use App\Entity\Medewerker;

class LandingController extends AbstractController
{
    private $security;
    private $mailer;

    public function __construct(Security $security, MailerInterface $mailer)
    {
        $this->security = $security;
        $this->mailer = $mailer;
    }

    #[Route('/', name: 'app_landing')]
    public function index(): Response
    {
        if (($this->getUser() instanceof Klant) && !($this->getUser() instanceof Medewerker)) {
            return $this->redirectToRoute('app_customer_dashboard');
        }
        if (($this->getUser() instanceof Medewerker) && !($this->getUser() instanceof Klant)) {
            return $this->redirectToRoute('app_employee_dashboard');
        }

        return $this->redirectToRoute('app_customer_dashboard');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, $userType = null): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_landing');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $this->addFlash('info', 'Je kan hier inloggen als klant of medewerker');
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/mailtest', name: 'mail_test')]
    public function mailTest(): Response
    {   
        if(!$this->getUser()) {
            $this->addFlash('info', 'not logged in');
            return $this->redirectToRoute('app_landing');
        }
        $user = $this->getUser();

        $email = (new Email())
            ->from(new Address('no-reply@alaphilomena.com', 'No Reply'))
            ->to($user->getEmail())
            ->subject('Test Email')
            ->text('test');
        
        $this->mailer->send($email);
        return $this->redirectToRoute('app_landing');
    }

    #[Route('/delete/{entity}/{id}', name: 'app_confirm_delete')]
    /* 
     * @param $entity
     * @param $id
     */
    public function remove($entity = null, $id = null, EntityManagerInterface $entityManager, BehandelingRepository $BR, AfspraakRepository $AR, MedewerkerRepository $MR, Request $request) {
        if (!$entity) {
            return $this->redirectToRoute('app_landing');
        }

        $form = $this->createForm(RemoveFormType::class);
        $form->handleRequest($request);

        switch ($entity) {
            case 'employee':
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
                if (!$id) {
                    return $this->redirectToRoute('app_admin_dashboard');
                }
                $MRresult = $MR->find($id);
                if (!$MRresult) {
                    $this->addFlash('error', 'Entiteit of type \''.$entity.'\' met id \''.$id.'\' bestaat niet.');
                    return $this->redirectToRoute('app_admin_dashboard');
                }
                foreach ($MRresult->getRoles() as $role) {
                    if ("ROLE_ADMIN" == $role) {
                        if (!$this->security->isGranted('ROLE_OWNER')) {
                            $this->addFlash('error', 'U kan geen admins verwijderen.');
                            return $this->redirectToRoute('app_admin_dashboard');
                        }
                    }
                }
                if ($form->isSubmitted() && $form->isValid()) {
                    if (!$form->get('Remove')->getData()) {
                        return $this->redirectToRoute('app_admin_dashboard');
                    }
                    $MR->remove($MRresult);
                    $this->addFlash('success', 'medewerker succesvol verwijderd.');
                    return $this->redirectToRoute('app_admin_dashboard');
                }
                break;
            case 'behandeling':
                $this->denyAccessUnlessGranted('ROLE_ADMIN');
                if (!$id) {
                    return $this->redirectToRoute('app_admin_dashboard');
                }
                $BRresult = $BR->find($id);
                if (!$BRresult) {
                    $this->addFlash('error', 'Entiteit of type \''.$entity.'\' met id \''.$id.'\' bestaat niet.');
                    return $this->redirectToRoute('app_admin_dashboard');
                }
                if ($form->isSubmitted() && $form->isValid()) {
                    if (!$form->get('Remove')->getData()) {
                        return $this->redirectToRoute('app_admin_dashboard');
                    }
                    $BR->remove($BRresult);
                    $this->addFlash('success', 'behandeling succesvol verwijderd.');
                    return $this->redirectToRoute('app_admin_dashboard');
                }
                break;
            case "afspraak":
                if ($this->getUser() instanceof Klant) {
                    $entityType = "Klant";
                    $redirectRoute = 'app_customer_dashboard';
                }
                if ($this->getUser() instanceof Medewerker) {
                    $entityType = "Medewerker";
                    $redirectRoute = 'app_employee_dashboard';
                    if($this->security->isGranted("ROLE_ADMIN")) {
                        $redirectRoute = 'app_admin_dashboard';
                    }
                }
                $ARresult = $AR->find($id);
                if (!$ARresult) {
                    $this->addFlash('error', 'Entiteit of type \''.$entity.'\' met id \''.$id.'\' bestaat niet.');
                    return $this->redirectToRoute($redirectRoute);
                }
                switch ($entityType) {
                    case "Klant": 
                        if (!$ARresult->getKlant() == $this->getUser()) {
                            $this->addFlash('error', 'U mag deze afspraak niet verwijderen');
                            return $this->redirectToRoute($redirectRoute);
                        }
                        break;
                    case "Medewerker":
                        if (!$ARresult->getMedewerker() == $this->getUser()) {
                            $this->addFlash('error', 'U mag deze afspraak niet verwijderen');
                            return $this->redirectToRoute($redirectRoute);
                        }
                        break;
                }
                if ($form->isSubmitted() && $form->isValid()) {
                    if (!$form->get('Remove')->getData()) {
                        return $this->redirectToRoute($redirectRoute);
                    }
                    $AR->remove($ARresult);
                    $this->addFlash('success', `$entity succesvol verwijderd.`);
                    return $this->redirectToRoute($redirectRoute);
                }
                break;
            
            default:
                $this->addFlash('error', 'Entiteit of type: \''.$entity.'\' kan niet verwijderd worden.');
                return $this->redirectToRoute('app_landing');
                break;
        }

        return $this->render('common/remove.html.twig', ['removeForm' => $form->createView(), 'entity' => $entity]);
    }
}
