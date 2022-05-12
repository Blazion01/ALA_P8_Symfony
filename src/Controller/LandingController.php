<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class LandingController extends AbstractController
{
    #[Route('/', name: 'app_landing')]
    public function index(): Response
    {
        return $this->render('landing/index.html.twig', [
            'controller_name' => 'LandingController',
        ]);
    }

    #[Route('/mailtest', name: 'mail_test')]
    public function mailTest(MailerInterface $mailer): Response
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
        if ($mailer->send($email)) {
            $this->addFlash('info', 'mail send');
            return $this->render('landing/index.html.twig', [
                'controller_name' => 'LandingController',
            ]);
        } else {
            $this->addFlash('error', `Mailer Error`);
            return $this->render('landing/index.html.twig', [
                'controller_name' => 'LandingController',
            ]);
        }



        //$mail = new PHPMailer();
        //$mail->isSMTP();
        //$mail->Host = 'smtp.mailtrap.io';
        //$mail->SMTPAuth = true;
        //$mail->Username = '52e353527148b6';
        //$mail->Password = '2e93ab2a86f410';
        //$mail->SMTPSecure = 'tls';
        //$mail->Port = 2525;
        //$mail->setFrom('no-reply@alaphilomena', 'No Reply');
        //$mail->addReplyTo($user->getEmail());
        //$mail->Subject = 'Test Email';
        //$mail->Body = 'Test';
        //$mail->AltBody = 'Test';
        //if ($mail->send()) {
        //    $this->addFlash('info', 'mail send');
        //    return $this->redirectToRoute('app_landing');
        //} else {
        //    $this->addFlash('error', `Mailer Error: $mail->ErrorInfo`);
        //    return $this->redirectToRoute('app_landing');
        //}
    }
}
