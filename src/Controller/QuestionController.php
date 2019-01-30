<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Question;
use App\Form\MessageType;
use App\Form\QuestionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends AbstractController
{
    /**
     * @Route(
     *     "/questions/ajouter",
     *     name="question_create",
     *     methods={"GET", "POST"}
     * )
     */

    public function create(Request $request)
    {
        $question = new Question();

        $questionForm = $this->createForm(QuestionType::class, $question);

        $questionForm->handleRequest($request);

        if($questionForm->isSubmitted() && $questionForm->isValid()) {

            //récupére l'entity manager de Doctrine
            $em = $this->getDoctrine()->getManager();
            //on demande à Doctrine de sauvegarder notre instance
            $em->persist($question);
            //on exécute les requêtes
            $em->flush();

            //Crée un message flash à afficher sur la prochaine page
            $this->addFlash('success', 'Merci de votre participation !');

            //Redirige sur la page de détails de cette question
            return $this->redirectToRoute('question_detail', ['id' => $question->getId()]);

        }

        //Pour supprimer quelquechose de ma base de donnée
        //$em->remove($question);
        //$em->flush();

        return $this->render('question/create.html.twig',[
            "questionForm" => $questionForm->createView()
        ]);
    }

    /**
     * @Route(
     *     "/questions/{id}",
     *     name="question_detail",
     *     requirements={"id": "\d+"},
     *     methods={"GET","POST"}
     *     )
     */
    public function details($id, Request $request)
    {
        $questionRepository = $this->getDoctrine()->getRepository(Question::class);
        $messageRepository = $this->getDoctrine()->getRepository(Message::class);
        //Compte le nombre d'élément:
        //$question = $questionRepository->count();
        //$question = $questionRepository->findOneBy(["$id" => $id]);
        //$question = $questionRepository->findOneById($id);
        $question = $questionRepository->find($id);
        $messages = $messageRepository->findBy(
            ['isPublished' => true],
            ['creationDate' => 'DESC']
        );
        if(!$question){
            throw $this->createNotFoundException("Cette question n'existe pas!");

        }

        //Créée une instance de message à associer à formulaire
        $message = new Message();

        //Créé le formulaire
        $messageForm = $this->createForm(MessageType::class, $message);

        $messageForm->handleRequest($request);

        if($messageForm->isSubmitted() && $messageForm->isValid()) {

            //récupére l'entity manager de Doctrine
            $em = $this->getDoctrine()->getManager();
            //on demande à Doctrine de sauvegarder notre instance
            $em->persist($message);
            //on exécute les requêtes
            $em->flush();

            //Crée un message flash à afficher sur la prochaine page
            $this->addFlash('success', 'Merci de votre participation !');

            //Redirige sur la page de détails de cette question
            return $this->redirectToRoute('question_detail', [
                'id' => $question->getId()
                ]);
        }


        return $this->render('question/details.html.twig',[
            'question' => $question,
            'messageForm' => $messageForm->createView(),
            'messages' => $messages
            ]);
    }

    /**
     * @Route(
     *     "/questions",
     *      name="question_list",
     *     methods={"GET"}
     *     )
     */
    public function list()
    {
        //Ce repository nous permet de faire des SELECT
        $questionRepository = $this->getDoctrine()->getRepository(Question::class);

        //équivalent à SELECT * FROM questions WHERE status = 'debating'
        //ORDER BY supports DESC LIMIT 1000
        $questions = $questionRepository->findBy(
            ['status' => 'debating'], //WHERE
            ['supports' => 'DESC'],    //ORDER BY
            1000,                      //limit
            0                          //offset
        );

        //Fais un var dump puis un die
        //dd($questions);

        //Fais un var_dump et affiche le résultat dans le débugueur
        //dump($questions);

        return $this->render('question/list.html.twig', [
            'questions' => $questions
        ]);
    }
}
