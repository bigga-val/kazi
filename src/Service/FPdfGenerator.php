<?php

namespace App\Service;
use App\Entity\BonProduit;
use App\Repository\LigneBonProduitRepository;
use FPDF;
use App\Repository\FactureRepository;
use App\Repository\LigneFactureRepository;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


class FPdfGenerator
{

    private $fPDF;

    public function  __construct()
    {
        $this->fPDF = new FPDF();
        $this->fPDF->AddPage();
//        $pdfOptions = new Options();
//        $pdfOptions->set('defaultFont', 'Garamond');
//        $this->domPDF->setOptions($pdfOptions);

    }
    public function generatePdf($factureID, FactureRepository $factureRepository, LigneFactureRepository $ligneFactureRepository)
    {
        $facture = $factureRepository->find($factureID);
        $lignes = $ligneFactureRepository->findBy([
            'facture'=>$factureID
        ]);
        $pdf = $this->fPDF;
        $mail = new PHPMailer();
        //$pdf->AddPage();

        $pdf->SetDrawColor(183); // Couleur du fond RVB
        $pdf->SetFillColor(221); // Couleur des filets RVB
        $pdf->SetTextColor(0); // Couleur du texte noir

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetY(20);
        $pdf->SetX(15);
        $pdf->Cell(40, 10, 'Facture No '.$facture->getNumeroFacture());

        $pdf->Image('assets/images/qazi.png', 147, 0, 50);

        // informations sur qazi
        // ligne 1
        $pdf->SetY(40);
        $pdf->SetX(90);
        $pdf->Cell(60,8,'13 Tetrick Road, Cypress Gardens, Florida, 33884, US',0,1,'L',0);

        // ligne 2
        $pdf->SetY(48);
        $pdf->SetX(165);
        $pdf->Cell(60,8,'info@qazi.com',0,1,'L',0);

        //ligne 3
        $pdf->SetY(56);
        $pdf->SetX(162);
        $pdf->Cell(60,8,'+243995053623',0,1,'L',0);

        //tracer une ligne horizontale
        $pdf->Line(15, 70, 195, 70);

        //Inclure les infos du client
        $pdf->SetY(75);
        $pdf->SetX(15);
        $pdf->Cell(60,8,'Info du Client :',0,1,'L',0);
        //nom du client
        $pdf->SetY(80);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getNom(),0,1,'L',0);
        //son adresse physique
        $pdf->SetY(85);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getAdressephysique(),0,1,'L',0);
        //son email
        $pdf->SetY(90);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getEmail(),0,1,'L',0);
        //son telephone
        $pdf->SetY(95);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getTelephone(),0,1,'L',0);

        //Infos de la facture
        //label numero facture
        $pdf->SetY(75);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Facture No',0,1,'L',0);

        //label date debut
        $pdf->SetY(80);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Date d\'etablissement',0,1,'L',0);
        //label date d'expiration
        $pdf->SetY(85);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Date d\'expiration',0,1,'L',0);
        //valeur numero facture
        $pdf->SetY(75);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$facture->getNumeroFacture(),0,1,'L',0);

        //valeur date etablissement
        $pdf->SetY(80);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$facture->getDateDebut()->format('d-m-Y'),0,1,'L',0);

        //valeur date expiration
        $pdf->SetY(85);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$facture->getDateFin()->format('d-m-Y'),0,1,'L',0);

        //tableau des ligne de la facture
        $tabY = 105;
        $prodX = 15; $qtyX = 57; $puX = 99; $totX=143;
        // 1° ligne du tableau


        $pdf->SetY($tabY);

        $pdf->SetX($prodX);
        $pdf->Cell(50,8,ucfirst($facture->getTypeBien()) ,1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
        // position de la colonne 2 (70 = 10+60)
        $pdf->SetX($qtyX);
        $pdf->Cell(50,8,'Quantite',1,0,'C',1);
        // position de la colonne 3 (130 = 70+60)
        $pdf->SetX($puX);
        $pdf->Cell(50,8,'Prix Unitaire',1,0,'C',1);

        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Total',1,0,'C',1);
        $soustotal = 0;
        foreach($lignes as $ligne){
            $tabY = $tabY +8;
            $pdf->SetY($tabY);

            $pdf->SetX($prodX);
            if($facture->getTypeBien() =='produit'){
                $pdf->Cell(50,8,$ligne->getProduit()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne

            }else{
                $pdf->Cell(50,8,$ligne->getService()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
            }

            // position de la colonne 2 (70 = 10+60)
            $pdf->SetX($qtyX);
            $pdf->Cell(50,8,$ligne->getQuantite(),1,0,'C',1);
            // position de la colonne 3 (130 = 70+60)
            $pdf->SetX($puX);
            $pdf->Cell(50,8,$ligne->getPrix(),1,0,'C',1);
            $total = $ligne->getQuantite() * $ligne->getPrix();
            $soustotal = $soustotal+$total;
            $pdf->SetX($totX);
            $pdf->Cell(50,8,$total,1,0,'C',1);
        }
        //Sous-total
        $tabY = $tabY +15;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Sous-Total: '.$soustotal,0,1,'R',0);
        //TVA-Tax
        $tabY = $tabY +8;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'TVA(%): '.$facture->getTax(),0,1,'R',0);

        //Reduction - Discount
        $tabY = $tabY +8;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Reduction(%): '.$facture->getDiscount(),0,1,'R',0);

        //Grand-Total
        $taxable = $soustotal +($soustotal* $facture->getTax())/100;
        $reductible = ($soustotal * $facture->getDiscount())/100;
        $grandtotal = $taxable - $reductible;
        $tabY = $tabY +8;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Grand-Total: '.$grandtotal,0,1,'R',0);


        //Visualiser dans le navigateur
        // email stuff (change data below)


       return $pdf->Output('S');
        ///comment the below code to readjust the pdf file
        ///
        //
        //return $pdf->Output('attachment.pdf', 'S');
//        $mail->AddStringAttachment($attachment, 'attachment.pdf');
//
//        $mail->isSMTP();
//        $mail->SMTPAuth = true;
//        $mail->SMTPSecure = 'ssl';
//        $mail->Host = 'smtp.hostinger.com';
//        $mail->Port = '465';
//        $mail->isHTML(true);
//        $mail->Username = 'admin@insoftware.tech';
//        $mail->Password = 'Insoft@123';
//        $mail->SetFrom('admin@insoftware.tech');
//        //$mail->addAttachment($myFile);
//
//        $mail->Subject = ucfirst('Votre facture est prete');
//        $mail->Body = $mailContent;
//        $mail->AddAddress($facture->getClient()->getEmail());
//        return $mail->Send();


        //return $this->senderEmail('Test d\'envoi de facture',$mailContent,'gabykatonge@isicom.education');
        //return $pdf->Output('S');

//        if(mail($to, $subject, $body, $headers)){
//            $_SESSION['send_ok'] = true;
//            header("Location: gererorg.php");
//        }else{
//            header("Location: historique.php");
//        }

        // Télécharger le PDF
        /*
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="votre_fichier.pdf"',
        ]);
        */
    }


    public function sendInvoiceByEMail($factureID, FactureRepository $factureRepository, LigneFactureRepository $ligneFactureRepository)
    {
        $facture = $factureRepository->find($factureID);
        $lignes = $ligneFactureRepository->findBy([
            'facture'=>$factureID
        ]);
        $pdf = $this->fPDF;
        $mail = new PHPMailer();
        //$pdf->AddPage();

        $pdf->SetDrawColor(183); // Couleur du fond RVB
        $pdf->SetFillColor(221); // Couleur des filets RVB
        $pdf->SetTextColor(0); // Couleur du texte noir

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetY(20);
        $pdf->SetX(15);
        $pdf->Cell(40, 10, 'Facture No '.$facture->getNumeroFacture());

        $pdf->Image('assets/images/qazi.png', 147, 0, 50);

        // informations sur qazi
        // ligne 1
        $pdf->SetY(40);
        $pdf->SetX(90);
        $pdf->Cell(60,8,'13 Tetrick Road, Cypress Gardens, Florida, 33884, US',0,1,'L',0);

        // ligne 2
        $pdf->SetY(48);
        $pdf->SetX(165);
        $pdf->Cell(60,8,'info@qazi.com',0,1,'L',0);

        //ligne 3
        $pdf->SetY(56);
        $pdf->SetX(162);
        $pdf->Cell(60,8,'+243995053623',0,1,'L',0);

        //tracer une ligne horizontale
        $pdf->Line(15, 70, 195, 70);

        //Inclure les infos du client
        $pdf->SetY(75);
        $pdf->SetX(15);
        $pdf->Cell(60,8,'Info du Client :',0,1,'L',0);
        //nom du client
        $pdf->SetY(80);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getNom(),0,1,'L',0);
        //son adresse physique
        $pdf->SetY(85);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getAdressephysique(),0,1,'L',0);
        //son email
        $pdf->SetY(90);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getEmail(),0,1,'L',0);
        //son telephone
        $pdf->SetY(95);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getTelephone(),0,1,'L',0);

        //Infos de la facture
        //label numero facture
        $pdf->SetY(75);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Facture No',0,1,'L',0);

        //label date debut
        $pdf->SetY(80);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Date d\'etablissement',0,1,'L',0);
        //label date d'expiration
        $pdf->SetY(85);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Date d\'expiration',0,1,'L',0);
        //valeur numero facture
        $pdf->SetY(75);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$facture->getNumeroFacture(),0,1,'L',0);

        //valeur date etablissement
        $pdf->SetY(80);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$facture->getDateDebut()->format('d-m-Y'),0,1,'L',0);

        //valeur date expiration
        $pdf->SetY(85);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$facture->getDateFin()->format('d-m-Y'),0,1,'L',0);

        //tableau des ligne de la facture
        $tabY = 105;
        $prodX = 15; $qtyX = 57; $puX = 99; $totX=143;
        // 1° ligne du tableau


        $pdf->SetY($tabY);

        $pdf->SetX($prodX);
        $pdf->Cell(50,8,ucfirst($facture->getTypeBien()),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
        // position de la colonne 2 (70 = 10+60)
        $pdf->SetX($qtyX);
        $pdf->Cell(50,8,'Quantite',1,0,'C',1);
        // position de la colonne 3 (130 = 70+60)
        $pdf->SetX($puX);
        $pdf->Cell(50,8,'Prix Unitaire',1,0,'C',1);

        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Total',1,0,'C',1);
        $grandTotal = 0;
        foreach($lignes as $ligne){
            $tabY = $tabY +8;
            $pdf->SetY($tabY);

            $pdf->SetX($prodX);
            //$pdf->Cell(50,8,$ligne->getProduit()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
            if($facture->getTypeBien() =='produit'){
                $pdf->Cell(50,8,$ligne->getProduit()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne

            }else{
                $pdf->Cell(50,8,$ligne->getService()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
            }

            // position de la colonne 2 (70 = 10+60)
            $pdf->SetX($qtyX);
            $pdf->Cell(50,8,$ligne->getQuantite(),1,0,'C',1);
            // position de la colonne 3 (130 = 70+60)
            $pdf->SetX($puX);
            $pdf->Cell(50,8,$ligne->getPrix(),1,0,'C',1);
            $total = $ligne->getQuantite() * $ligne->getPrix();
            $grandTotal = $grandTotal+$total;
            $pdf->SetX($totX);
            $pdf->Cell(50,8,$total,1,0,'C',1);
        }

        $tabY = $tabY +15;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Grand Total: '.$grandTotal,0,1,'R',0);

        //Visualiser dans le navigateur
        $eol = PHP_EOL;
        // email stuff (change data below)
        $mailContent = 'Votre Facture\n';
        $mailContent .= 'Cher client, veuillez trouver en attache votre facture \n';
        $mailContent .= 'Merci de nous faire confiance ';

        //return $pdf->Output('S');
        ///comment the below code to readjust the pdf file

        $attachment = $pdf->Output('attachment.pdf', 'S');
        $mail->AddStringAttachment($attachment, 'attachment.pdf');

        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = 'smtp.hostinger.com';
        $mail->Port = '465';
        $mail->isHTML(true);
        $mail->Username = 'admin@insoftware.tech';
        $mail->Password = 'Insoft@123';
        $mail->SetFrom('admin@insoftware.tech');
        //$mail->addAttachment($myFile);

        $mail->Subject = ucfirst('Votre facture est prete');
        $mail->Body = $mailContent;
        $mail->AddAddress($facture->getClient()->getEmail());
        return $mail->Send();


        //return $this->senderEmail('Test d\'envoi de facture',$mailContent,'gabykatonge@isicom.education');
        //return $pdf->Output('S');

//        if(mail($to, $subject, $body, $headers)){
//            $_SESSION['send_ok'] = true;
//            header("Location: gererorg.php");
//        }else{
//            header("Location: historique.php");
//        }

        // Télécharger le PDF
        /*
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="votre_fichier.pdf"',
        ]);
        */
    }



    public function generateBonPdf(BonProduit $bonProduit,
                                    FactureRepository $factureRepository,
                                    LigneBonProduitRepository $ligneBonProduitRepository)
    {
        $facture = $factureRepository->find($bonProduit->getFacture()->getId());
        $lignesBon = $ligneBonProduitRepository->findBy(['bonProduit'=>$bonProduit->getId()]);
        $pdf = $this->fPDF;
        $mail = new PHPMailer();
        //$pdf->AddPage();

        $pdf->SetDrawColor(183); // Couleur du fond RVB
        $pdf->SetFillColor(221); // Couleur des filets RVB
        $pdf->SetTextColor(0); // Couleur du texte noir

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetY(20);
        $pdf->SetX(15);
        $pdf->Cell(40, 10, 'Facture No '.$facture->getNumeroFacture());

        $pdf->SetY(28);
        $pdf->SetX(15);
        $pdf->Cell(40, 10, 'Bon No '. $bonProduit->getNumero());

        $pdf->Image('assets/images/qazi.png', 147, 0, 50);

        // informations sur qazi
        // ligne 1
        $pdf->SetY(40);
        $pdf->SetX(90);
        $pdf->Cell(60,8,'13 Tetrick Road, Cypress Gardens, Florida, 33884, US',0,1,'L',0);

        // ligne 2
        $pdf->SetY(48);
        $pdf->SetX(165);
        $pdf->Cell(60,8,'info@qazi.com',0,1,'L',0);

        //ligne 3
        $pdf->SetY(56);
        $pdf->SetX(162);
        $pdf->Cell(60,8,'+243995053623',0,1,'L',0);

        //tracer une ligne horizontale
        $pdf->Line(15, 70, 195, 70);

        //Inclure les infos du client
        $pdf->SetY(75);
        $pdf->SetX(15);
        $pdf->Cell(60,8,'Info du Client :',0,1,'L',0);
        //nom du client
        $pdf->SetY(80);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getNom(),0,1,'L',0);
        //son adresse physique
        $pdf->SetY(85);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getAdressephysique(),0,1,'L',0);
        //son email
        $pdf->SetY(90);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getEmail(),0,1,'L',0);
        //son telephone
        $pdf->SetY(95);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getTelephone(),0,1,'L',0);

        //Infos du bon
        //label numero facture
        $pdf->SetY(75);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Bon No',0,1,'L',0);

        //label date debut
        $pdf->SetY(80);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Date d\'etablissement',0,1,'L',0);

        //valeur numero bon
        $pdf->SetY(75);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$bonProduit->getNumero(),0,1,'L',0);

        //valeur date etablissement
        $pdf->SetY(80);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$bonProduit->getCreatedAt()->format('d-m-Y'),0,1,'L',0);


        //tableau des ligne de la facture
        $tabY = 105;
        $prodX = 15; $qtyX = 57; $puX = 99; $totX=143;
        // 1° ligne du tableau


        $pdf->SetY($tabY);

        $pdf->SetX($prodX);
        $pdf->Cell(50,8,ucfirst($facture->getTypeBien()) ,1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
        // position de la colonne 2 (70 = 10+60)
        $pdf->SetX($qtyX);
        $pdf->Cell(50,8,'Quantite',1,0,'C',1);
        // position de la colonne 3 (130 = 70+60)
        $pdf->SetX($puX);
        $pdf->Cell(50,8,'Prix Unitaire',1,0,'C',1);

        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Total',1,0,'C',1);
        $grandTotal = 0;
        foreach($lignesBon as $ligne){
            $tabY = $tabY +8;
            $pdf->SetY($tabY);

            $pdf->SetX($prodX);
            if($facture->getTypeBien() =='produit'){
                $pdf->Cell(50,8,$ligne->getLigneFacture()->getProduit()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne

            }else{
                $pdf->Cell(50,8,$ligne->getLigneFacture()->getService()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
            }

            // position de la colonne 2 (70 = 10+60)
            $pdf->SetX($qtyX);
            $pdf->Cell(50,8,$ligne->getLigneFacture()->getQuantite(),1,0,'C',1);
            // position de la colonne 3 (130 = 70+60)
            $pdf->SetX($puX);
            $pdf->Cell(50,8,$ligne->getLigneFacture()->getPrix(),1,0,'C',1);
            $total = $ligne->getLigneFacture()->getQuantite() * $ligne->getLigneFacture()->getPrix();
            $grandTotal = $grandTotal+$total;
            $pdf->SetX($totX);
            $pdf->Cell(50,8,$total,1,0,'C',1);
        }
        //Sous-total
        $tabY = $tabY +15;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Sous-Total: '.$grandTotal,0,1,'R',0);

        //Sous-total
        $tabY = $tabY +8;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'TVA: '.$bonProduit->getFacture()->getTax().'%',0,1,'R',0);

//Sous-total
        $tabY = $tabY +8;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Reduction: '.$bonProduit->getFacture()->getDiscount().'%',0,1,'R',0);

        $taxable = $grandTotal + (($grandTotal * $bonProduit->getFacture()->getTax())/100);
        $reductible = ($grandTotal * $bonProduit->getFacture()->getDiscount())/100;
        $total = $taxable - $reductible;

        $tabY = $tabY +8;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Grand-Total: '.$total,0,1,'R',0);

        //Visualiser dans le navigateur


        return $pdf->Output('S');
    }


}