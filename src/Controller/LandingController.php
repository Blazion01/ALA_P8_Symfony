<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MedewerkerRepository;

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
        return $this->redirectToRoute('app_login');
        return $this->render('landing/index.html.twig', [
            'controller_name' => 'LandingController',
        ]);
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

    #[Route('/delete/{entity}/{id}')]
    /* 
     * @param $entity
     * @param $id
     */
    public function remove($entity = null, $id = null, EntityManagerInterface $entityManager, MedewerkerRepository $MR) {
        if (!$entity) {
            return $this->redirectToRoute('app_landing');
        }

        $form = $this->createForm(RemoveFormType::class);

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
                if (in_array("ROLE_ADMIN", $MRresult->getRoles() && !$this->security->isGranted('ROLE_OWNER'))) {
                    $this->addFlash('error', 'U kan geen admins verwijderen.');
                    return $this->redirectToRoute('app_admin_dashboard');
                }
                if ($form->isSubmitted() && $form->isValid()) {
                    if (!$form->get('Remove')->getData()) {
                        return $this->redirectToRoute('app_admin_dashboard');
                    }
                    $MR->remove($MRresult);
                    $this->addFlash('success', 'succesvol verwijderd.');
                    return $this->redirectToRoute('app_admin_dashboard');
                }
                break;
            
            default:
                $this->addFlash('error', 'Entiteit of type: '.$entity.'kan niet verwijderd worden.');
                return $this->redirectToRoute('app_landing');
                break;
        }
    }
}
