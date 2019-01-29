<?php

namespace App\Controller;

use App\Entity\Question;
use http\Env\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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


    public function create()
    {
        $question = new Question();
        $question->setTitle('blabla');
        $question -> setDescription('lorem etc');
        $question -> setStatus('debating');
        $question -> setSupports(666);
        $question -> setCreationDate(new \DateTime());

        //récupére l'entity manager de Doctrine
        $em = $this->getDoctrine()->getManager();
        //on demande à Doctrine de sauvegarder notre instance
        $em->persist($question);
        //on exécute les requêtes
        $em->flush();

        //Pour supprimer quelquechose de ma base de donnée
        //$em->remove($question);
        //$em->flush();

        return $this->render('question/create.html.twig',
            );
    }

    /**
     * @Route(
     *     "/questions/{id}",
     *     name="question_detail",
     *     requirements={"id": "\d+"},
     *     methods={"GET","POST"}
     *     )
     */
    public function details($id)
    {
        $questionRepository = $this->getDoctrine()->getRepository(Question::class);

        //Compte le nombre d'élément:
        //$question = $questionRepository->count();

        //$question = $questionRepository->findOneBy(["$id" => $id]);
        //$question = $questionRepository->findOneById($id);
        $question = $questionRepository->find($id);

        if(!$question){
            throw $this->createNotFoundException("Cette question n'existe pas!");
        }

        return $this->render('question/details.html.twig',[
            'question' => $question
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
