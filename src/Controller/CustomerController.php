<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Security;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Klant;
use App\Entity\Afspraak;
use App\Form\AfspraakFlowFormType;
use App\Repository\KlantRepository;
use App\Repository\AfspraakRepository;
use App\Repository\BehandelingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\TimeBundle\DateTimeFormatter;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class CustomerController extends AbstractController
{
    private $security;
    private $mailer;
    private $pagination;
    private $afspraakFlow;
    private $DTF;

    public function __construct(DateTimeFormatter $DTF, AfspraakFlowFormType $afspraakFlow, PaginatorInterface $pagination, Security $security, MailerInterface $mailer)
    {
        $this->security = $security;
        $this->mailer = $mailer;
        $this->pagination = $pagination;
        $this->afspraakFlow = $afspraakFlow;
        $this->DTF = $DTF;
    }

    #[Route('/customer', name: 'app_customer_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager, AfspraakRepository $AR): Response
    {
        if((!$this->security->isGranted('ROLE_CUSTOMER')) && ($this->security->isGranted('ROLE_EMPLOYEE'))) return $this->redirectToRoute('app_employee_dashboard');
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');
    
        $temp = $this->getUser()->getAfspraaks();
        $afspraken = [
            "pending" => [],
            "done" => [],
            "rejected" => [],
        ];
        $i = 0;
        foreach ($temp as $afspraak) {
            $i++;
            $row["id"] = $i;
            $row["redirectId"] = $afspraak->getId();
            $row["time"] = $afspraak->getDatum()->format("d-m-Y")." ".$afspraak->getTijd()->format("H:i");
            $row["timeFromNow"] = $this->DTF->formatDiff(new \DateTime($afspraak->getDatum()->format("d-m-Y")." ".$afspraak->getTijd()->format("H:i")), new \DateTime());
            switch ($afspraak->getStatus()) {
                case "done":
                    $row["status"] = "Gedaan";
                    break;
                case "pending":
                    $row["status"] = "Wordt bekeken";
                    break;
                case "acknowledged":
                    $row["status"] = "Ingeroosterd";
                    break;
                case "rejected":
                    $row["status"] = "Afgekeurd";
                    break;
                case "didNotShow":
                    $row["status"] = "Niet langsgekomen";
                    break;
                default:
                    $row["status"] = $afspraak->getStatus();
                    break;
            }
            switch ($afspraak->getBehandeling()->getGroep()) {
                case 'NN':
                    $row["type"] = "Nagels | Nieuwe Set";
                    break;
                case 'NA':
                    $row["type"] = "Nagels | Nabehandeling";
                    break;
                case 'NH':
                    $row["type"] = "Nagels | Handen";
                    break;
                case 'NV':
                    $row["type"] = "Nagels | Voeten";
                    break;
                case 'HD':
                    $row["type"] = "Haar | Dames";
                    break;
                case 'HH':
                    $row["type"] = "Haar | Heren";
                    break;
                case 'HK':
                    $row["type"] = "Haar | Kinderen t/m 11 jaar";
                    break;
                case 'HT':
                    $row["type"] = "Haar | Kinderen 12 t/m 15 jaar";
                    break;
                default:
                    $row["type"] = null;
                    break;
            }
            $row["naam"] = $afspraak->getBehandeling()->getNaam();
            $row["prijs"] = $afspraak->getBehandeling()->getPrijs();
            if (($row["timeFromNow"][0] != "i") && (!(($row["status"] == "Afgekeurd") || ($row["status"] == "Gedaan") || ($row["status"] == "Niet langsgekomen")))) {
                $afspraak->setStatus("didNotShow");
                $entityManager->persist($afspraak);
                $row["status"] = "Niet langsgekomen";
            }
            if (($row["status"] == "Afgekeurd") || ($row["status"] == "Niet langsgekomen")) {
                array_push($afspraken["rejected"], $row);
                continue;
            }
            if ($row["timeFromNow"][0] == "i") {
                array_push($afspraken["pending"], $row);
                continue;
            }
            array_push($afspraken["done"], $row);
        }
        $entityManager->flush();
        // dd($afspraken);

        return $this->render('customer/dashboard.html.twig', [
            'afspraken' => $afspraken,
        ]);
    }

    #[Route('/customer/afspraak/create', name: 'app_afspraak_create')]
    public function afspraakCreate(EntityManagerInterface $entityManager, KlantRepository $KR, BehandelingRepository $BR)
    {
        if(!$this->getUser() instanceof Klant) {
            $this->addFlash('info', 'Je kan alleen nieuwe afspraken maken met een klantenaccount.');
            return $this->redirectToRoute('app_customer_dashboard');
        }
        $formData = new Afspraak();
        $formData->setKlant($this->getUser());

        $flow = $this->afspraakFlow;
        $flow->bind($formData);
        // if ($flow->getFormStepKey() == "flow_afspraakFlowFormType_step") $flow->setFormStepKey(1);
        $form = $flow->createForm();

        $dataArray = [];
        $behandelingData = $formData->getBehandeling();
        if($behandelingData) {
            switch ($behandelingData->getGroep()) {
                case "NN";
                    $dataArray["groep"] = "Nagels | Nieuwe Set";
                    break;
                case "NA":
                    $dataArray["groep"] = "Nagels | Nabehandeling";
                    break;
                case "NH":
                    $dataArray["groep"] = "Nagels | Handen";
                    break;
                case "NV":
                    $dataArray["groep"] = "Nagels | Voeten";
                    break;
                case "HD":
                    $dataArray["groep"] = "Haar | Dames";
                    break;
                case "HH":
                    $dataArray["groep"] = "Haar | Heren";
                    break;
                case "HK":
                    $dataArray["groep"] = "Haar | Kinderen t/m 11 jaar";
                    break;
                case "HT":
                    $dataArray["groep"] = "Haar | Kinderen 12 t/m 15 jaar";
                    break;
                default:
                    $dataArray["groep"] = $behandelingData->getGroep();
                    break;
            }
        }

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);
    
            if ($flow->nextStep()) {
                // form for the next step
                $form = $flow->createForm();
            } else {
                // flow finished
                $formData->setStatus('pending');
                $entityManager->persist($formData);
                $entityManager->flush();

                $this->mailer->send(
                    (new TemplatedEmail())
                        ->from(new Address('no-reply@alaphilomena.com', 'No Reply'))
                        ->to($this->getUser()->getEmail())
                        ->subject('Afspraak aangevraagd')
                        ->htmlTemplate('emailTemplates/afspraak-aangevraagd.html.twig')
                        ->context([
                            'date' => $formData->getDatum()->format("d-m-Y").' '.$formData->getTijd()->format("H:i"),
                            // 'timeFromNow' => $this->DTF->formatDiff(new \DateTime($dataArray["tijd"]), new \DateTime()),
                            'type' => $dataArray["groep"],
                            'naam' => $behandelingData->getNaam(),
                            'prijs' => $behandelingData->getPrijs(),
                            'username' => $this->getUser()->getVoornaam().' '.$this->getUser()->getAchternaam(),
                        ])
                );
    
                $flow->reset(); // remove step data from the session
                $this->addFlash('success', 'Afspraak aangemaakt');
                return $this->redirectToRoute('app_customer_dashboard'); // redirect when done
            }
        }

        if($flow->getCurrentStepNumber() == 6) {
            $dataArray["naam"] = $behandelingData->getNaam();
            $dataArray["tijd"] = $formData->getDatum()->format("d-m-Y").' '.$formData->getTijd()->format("H:i");
            $dataArray["prijs"] = $behandelingData->getPrijs();
        }

        return $this->render('customer/afspraak_create.html.twig', [
            'form' => $form->createView(),
            'flow' => $flow,
            'formData' => $dataArray,
        ]);
    }
}
